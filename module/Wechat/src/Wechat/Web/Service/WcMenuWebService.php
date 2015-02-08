<?php

namespace Wechat\Web\Service;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Wechat\Db\Entity\WcApp;

class WcMenuWebService extends BaseWebService {
	public function run(AbstractController $controller) {
		$menuService = $this->getService ( 'Wechat\Api\Menu\MenuService' );
		$action = $controller->params ()->fromQuery ( 'action' );
		$data = $controller->params ()->fromPost ();
		$menuStr = isset ( $data ['menu'] ) ? $data ['menu'] : '';
		switch ($action) {
			case 'submit' :
				if ($menuStr) {
					$menuService->createByJsonStr ( $menuStr );
					$this->storeMenuToDb ( $menuStr );
				}
				break;
			case 'store' :
				if ($menuStr) {
					$this->storeMenuToDb ( $menuStr );
				}
				break;
			case 'read' :
				$menuStr = $this->readMenuFromDb ();
				break;
			case 'delete' :
				$menuService->delete ();
			default :
				$menu = $menuService->query ();
				$menuStr = $menu->toJsonStr ();
		}
		$viewModel = new ViewModel ( array (
				'menu' => $menuStr 
		) );
		$viewModel->setTemplate ( 'wechat/service/wc_menu' );
		return $viewModel;
	}
	protected function storeMenuToDb($menuStr) {
		$wcApp = $this->getWcApp ();
		$wcApp->setMenu ( $menuStr );
		$this->getTable ( WcApp::TABLE )->updateRow ( $wcApp, WcApp::AID );
	}
	protected function readMenuFromDb() {
		$aid = $this->getWcApp ()->getAid ();
		$tableGateway = $this->getTable ( WcApp::TABLE );
		$wcApp = $tableGateway->selectOne ( array (
				WcApp::AID => $aid 
		) );
		return $wcApp->getMenu ();
	}
}