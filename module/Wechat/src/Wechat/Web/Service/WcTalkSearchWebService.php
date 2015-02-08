<?php

namespace Wechat\Web\Service;

use Wechat\Web\Service\BaseWebService;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Wechat\Api\WcSession;
use Wechat\Db\View\WcMsgUserWordView;
use Wechat\Db\Entity\WcMsg;
use Wechat\Db\Entity\WcApp;
use Cly\Common\ClyLib;
use Zend\Db\Sql\Where;
use Wechat\Db\Entity\WcWord;

class WcTalkSearchWebService extends BaseWebService {
	protected $options = array (
			'item_count' => '20' 
	);
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$wcApp = $this->getWcApp ();
		$page = $controller->params ()->fromQuery ( 'page' );
		$action = $controller->params ()->fromQuery ( 'action' );
		$uri = $controller->getRequest ()->getUri ();
		$sqlStr = "";
		$where = new Where ();
		$where->equalTo ( WcApp::AID, WcSession::get ( 'WcApp' )->getAid () );
		$where->equalTo ( WcMsg::MSG_TYPE, 'text' );
		switch ($action) {
			case 'search' :
				$searchText = $controller->params ()->fromPost ( 'search' );
				if ($searchText) {
					$where->like ( WcWord::CONTENT, "%$searchText%" );
				}
				break;
		}
		$select = new Select ( WcMsgUserWordView::VIEW );
		$select->columns ( array (
				'headImgUrl',
				'fromUserName',
				'nickName',
				'createTime',
				'content' 
		) );
		$select->where ( $where );
		$select->order ( WcMsg::CREATE_TIME . ' DESC' );
		$paginator = new Paginator ( new DbSelect ( $select, $this->getAdapter () ) );
		$this->getAdapter()->query('select * from wc_user limit 5');
		$paginator->setCurrentPageNumber ( $page );
		$paginator->setItemCountPerPage ( $this->getOptions ( 'item_count' ) );
		$viewModel = new ViewModel ( array (
				'talkUserUrl' => '/wechat/index/talkUser',
				'searchUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'search' 
				) ),
				'baseUrl' => $uri->getPath () . '?' . $uri->getQuery (),
				'baseUri' => $uri,
				'paginator' => $paginator 
		) );
		$viewModel->setTemplate ( 'wechat/service/wc_talk_search' );
		return $viewModel;
	}
}