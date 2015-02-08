<?php

namespace Wechat\Web\Service;

use Wechat\Web\Service\BaseWebService;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Wechat\Api\WcSession;
use Wechat\Db\Entity\WcMsg;
use Wechat\Db\Entity\WcUser;
use Wechat\Api\Msg\ResponseTextMsg;
use Wechat\Api\Msg\ResponseMsg;
use Wechat\Db\Entity\WcRmsg;
use Zend\Db\Sql\Where;
use Cly\Common\ClyLib;

class WcTalkUserWebService extends BaseWebService {
	protected $options = array (
			'item_count' => '20' 
	);
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$wcApp = $this->getWcApp ();
		$baseUri = $controller->getRequest ()->getUri ();
		$page = $controller->params ()->fromQuery ( 'page' );
		$openId = $controller->params ()->fromQuery ( 'openId' );
		$preUrl = $controller->params ()->fromQuery ( 'baseUrl' );
		if (! $openId)
			exit ();
		$sqlStr = "create temporary table temp(select createTime,'come' as target,openId,content,msgType from wc_msg,wc_user,wc_word where wc_msg.fromUserName=wc_user.openId and wc_msg.wid=wc_word.wid and wc_msg.fromUserName='$openId')
union
(select createTime,'to' as target,toUserName as openId,content,msgType from wc_rmsg,wc_user where wc_rmsg.toUserName=wc_user.openId and wc_rmsg.msgType='text' and wc_rmsg.toUserName='$openId')
union
(select createTime,'to' as target,toUserName as openId,news as content,msgType from wc_rmsg,wc_user where wc_rmsg.toUserName=wc_user.openId and wc_rmsg.msgType='news' and wc_rmsg.toUserName='$openId')";
		$this->getDb ()->queryExecute ( $sqlStr );
		$action = $controller->params ()->fromQuery ( 'action' );
		$where = new Where ();
		switch ($action) {
			case 'send' :
				$message = $controller->params ()->fromPost ( 'message' );
				if ($message) {
					$responseTextMsg = new ResponseTextMsg ();
					$responseTextMsg [ResponseMsg::TO_USER_NAME] = $openId;
					$responseTextMsg [ResponseMsg::FORM_USER_NAME] = $wcApp->getAppUser ();
					$responseTextMsg [ResponseMsg::CONTENT] = $message;
					if ($this->getService ( 'Wechat\Api\Msg\MsgService' )->sendMsg ( $responseTextMsg->toSendStr () )) {
						$wcRmsg = new WcRmsg ( $responseTextMsg->getPopulateArray () );
						$wcRmsg = $this->getTable ( WcRmsg::TABLE )->insertRow ( $wcRmsg );
					}
				}
				break;
			case 'search' :
				$searchText = $controller->params ()->fromPost ( 'search' );
				if ($searchText) {
					$where->like ( 'content', "%$searchText%" );
				}
				break;
			case 'groupSend' :
				break;
		}
		$select = new Select ( 'temp' );
		$where->equalTo ( WcUser::OPEN_ID, $openId );
		$select->where ( $where );
		$wcUser = $this->getTable ( WcUser::TABLE )->selectOne ( array (
				'openId' => $openId 
		) );
		$select->order ( WcMsg::CREATE_TIME . ' DESC' );
		$paginator = new Paginator ( new DbSelect ( $select, $this->getAdapter () ) );
		$paginator->setCurrentPageNumber ( $page );
		$paginator->setItemCountPerPage ( $this->getOptions ( 'item_count' ) );
		$viewModel = new ViewModel ( array (
				'preUrl' => $preUrl,
				'baseUri' => $baseUri,
				'sendUrl' => ClyLib::addQuery ( $baseUri, array (
						'action' => 'send' 
				) ),
				'searchUrl' => ClyLib::addQuery ( $baseUri, array (
						'action' => 'search' 
				) ),
				'paginator' => $paginator,
				'wcUser' => $wcUser,
				'wcApp' => WcSession::get ( 'WcApp' ) 
		) );
		$viewModel->setTemplate ( 'wechat/service/wc_talk_user' );
		return $viewModel;
	}
}