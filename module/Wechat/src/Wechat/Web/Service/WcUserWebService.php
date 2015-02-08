<?php

namespace Wechat\Web\Service;

use Wechat\Web\Service\BaseWebService;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Wechat\Db\Entity\WcUser;
use Wechat\Db\Entity\WcApp;
use Wechat\Api\WcSession;
use Cly\Common\ClyLib;
use Zend\Db\Sql\Where;
use Wechat\Api\Msg\ResponseTextMsg;
use Wechat\Db\Entity\WcRmsg;
use Wechat\Api\Msg\ResponseNewsMsg;

class WcUserWebService extends BaseWebService {
	protected $options = array (
			'item_count' => 20,
			'send_time' => 48,
			'effect_time' => 48,
			'expire_time' => 4 
	);
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$wcApp = $this->getWcApp ();
		$effectTime = $this->getOptions ( 'effect_time' );
		$groupList = $this->getService ( 'Wechat\Api\User\GroupService' )->getGroupList ();
		$page = $controller->params ()->fromQuery ( 'page' );
		$action = $controller->params ()->fromQuery ( 'action' );
		$uri = $controller->getRequest ()->getUri ();
		$select = new Select ( WcUser::TABLE );
		$select->columns ( array (
				'*' 
		) );
		$where = new Where ();
		$where->addPredicates ( array (
				WcApp::AID => $wcApp->getAid () 
		) );
		$select->where ( $where );
		$select->order ( WcUser::SUBSCRIBE_TIME . ' desc' );
		switch ($action) {
			case 'group' :
				$data = json_decode ( $controller->params ()->fromPost ( 'data' ), true );
				$openId = $data ['openId'];
				$groupName = $data ['groupName'];
				foreach ( $groupList as $group ) {
					if ($group ['name'] == $groupName) {
						$groupId = $group ['id'];
						break;
					}
				}
				if (is_string ( $openId ) && is_numeric ( $groupId )) {
					if ($this->getService ( 'Wechat\Api\User\GroupService' )->moveGroupForUser ( $groupId, $openId )) {
						$wcUser = $this->getTable ( WcUser::TABLE )->selectOne ( array (
								WcUser::OPEN_ID => $openId 
						) );
						$wcUser->setGroupId ( $groupId );
						$this->getTable ( WcUser::TABLE )->updateRow ( $wcUser );
						exit ( 'success' );
					}
				}
				exit ( 'error' );
				break;
			case 'sync' :
				$queryArray = $uri->getQueryAsArray ();
				unset ( $queryArray ['action'] );
				$uri->setQuery ( $queryArray );
				$this->syncUserList ();
				break;
			case 'refreshLast' :
				$queryArray = $uri->getQueryAsArray ();
				unset ( $queryArray ['action'] );
				$uri->setQuery ( $queryArray );
				$aid = $wcApp = $wcApp->getAid ();
				$result = $this->getTable ( WcUser::TABLE )->select ( array (
						WcUser::AID => $aid 
				) );
				foreach ( $result as $u ) {
					$openId = $u->getOpenId ();
					$sql = "select * from wc_msg_word_event where fromUserName='$openId' ORDER BY createTime desc limit 1";
					$r = $this->getDb ()->queryExecute ( $sql )->current ();
					$lastMsgTime = $r ['createTime'] ?  : 0;
					$sql = "select content from wc_msg_word_event where fromUserName='$openId' and content is not null ORDER BY createTime desc limit 1";
					$r = $this->getDb ()->queryExecute ( $sql )->current ();
					$lastMsgContent = $r ['content'];
					$u->setLastMsgTime ( $lastMsgTime );
					$u->setLastMsgContent ( $lastMsgContent );
					$this->getTable ( WcUser::TABLE )->updateRow ( $u );
				}
				break;
			case 'groupSend' :
				$sendType = $controller->params ()->fromPost ( 'sendType' );
				$sendData = $controller->params ()->fromPost ( 'sendData' );
				$where->expression ( 'lastMsgTime>UNIX_TIMESTAMP()-?*3600', array (
						$this->getOptions ( 'send_time' ) 
				) );
				$select->where ( $where );
				$str = $select->getSqlString ( $this->getAdapter ()->getPlatform () );
				$resultSet = $this->getDb ()->queryExecute ( $str );
				switch ($sendType) {
					case 'text' :
						$responseMsg = new ResponseTextMsg ();
						$responseMsg->setContent ( $sendData );
						break;
					case 'news' :
						$responseMsg = new ResponseNewsMsg ();
						$news = json_decode ( $sendData, true );
						$news = $news ['data'] ['value'];
						$responseMsg->setArticleCount ( count ( $news ) );
						$responseMsg ['news'] = $news;
						break;
				}
				$responseMsg->setFromUserName ( $wcApp->getAppUser () );
				foreach ( $resultSet as $value ) {
					$responseMsg->setToUserName ( $value ['openId'] );
					$responseMsg->setCreateTime ( time () );
					$r = $this->getService ( 'Wechat\Api\Msg\MsgService' )->sendMsg ( $responseMsg->toSendStr () );
					if ($r) {
						$wcRmsg = new WcRmsg ( $responseMsg->getPopulateArray () );
						$wcRmsg = $this->getTable ( WcRmsg::TABLE )->insertRow ( $wcRmsg );
					}
				}
				exit ( 'finish' );
				break;
			case 'search' :
				$searchText = $controller->params ()->fromQuery ( 'searchText' );
				$searchType = $controller->params ()->fromQuery ( 'searchType' );
				switch ($searchType) {
					case '用户名' :
						$where->like ( WcUser::NICK_NAME, '%' . $searchText . '%' );
						$select->where ( $where );
						break;
					case '互动时间' :
						$time = intval ( $searchText );
						$time = $time == 0 ? $this->getOptions ( 'effect_time' ) : $time;
						$where->expression ( 'lastMsgTime>UNIX_TIMESTAMP()-?*3600', array (
								$time 
						) );
						$select->where ( $where );
						break;
					case '即将过期' :
						$time = intval ( $searchText );
						$time = $time == 0 ? $this->getOptions ( 'expire_time' ) : $time;
						$where->expression ( 'lastMsgTime+?*3600<?*3600+UNIX_TIMESTAMP() and lastMsgTime>UNIX_TIMESTAMP()-?*3600', array (
								$effectTime,
								$time,
								$effectTime 
						) );
						$select->where ( $where );
						break;
				}
				break;
		}
		$paginator = new Paginator ( new DbSelect ( $select, $this->getAdapter () ) );
		$paginator->setCurrentPageNumber ( $page );
		$paginator->setItemCountPerPage ( $this->getOptions ( 'item_count' ) );
		$viewModel = new ViewModel ( array (
				'effectTime' => $effectTime,
				'talkUserUrl' => '/wechat/index/talkUser',
				'groupSendUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'groupSend' 
				) ),
				'searchUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'search' 
				) ),
				'groupUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'group' 
				) ),
				'refreshLastUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'refreshLast' 
				) ),
				'baseUrl' => ClyLib::toUriStr ( $uri ),
				'baseUri' => $uri,
				'paginator' => $paginator,
				'groupList' => $groupList,
				'syncUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'sync' 
				) ),
				'service' => $this 
		) );
		$viewModel->setTemplate ( 'wechat/service/wc_user' );
		return $viewModel;
	}
	public function syncUserList() {
		$userService = $this->getService ( 'Wechat\Api\User\UserService' );
		$userList = $userService->getSubscribers ();
		foreach ( $userList as $openId ) {
			$wcUser = $this->getTable ( WcUser::TABLE )->selectOne ( array (
					WcUser::OPEN_ID => $openId 
			) );
			if ($wcUser) {
				if ($wcUser->needUpdate ( $this->getWcOptions ( 'expire_time' ) )) {
					$apiUser = $userService->getUserFromHttp ( $openId );
					if ($apiUser) {
						$wcUser->replaceArray ( $apiUser->getPopulateArray () );
						$wcUser->refreshUpdateTime ();
						$wcUser = $this->getTable ( WcUser::TABLE )->updateRow ( $wcUser );
					}
				}
			} else {
				$apiUser = $userService->getUserFromHttp ( $openId );
				if ($apiUser) {
					$wcUser = new WcUser ( $apiUser->getPopulateArray () );
					$wcUser->refreshUpdateTime ();
					$wcUser->setAid ( WcSession::get ( 'WcApp' )->getAid () );
					$wcUser = $this->getTable ( WcUser::TABLE )->insertRow ( $wcUser );
				}
			}
		}
	}
}