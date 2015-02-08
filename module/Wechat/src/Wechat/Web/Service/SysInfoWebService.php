<?php

namespace Wechat\Web\Service;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Cly\Common\ClyLib;
use Wechat\Db\Entity\WcApp;

class SysInfoWebService extends BaseWebService {
	public function run(AbstractController $controller) {
		$uri = $controller->getRequest ()->getUri ();
		$action = $controller->params ()->fromQuery ( 'action' );
		$wcApp = $this->getWcApp ();
		switch ($action) {
			case 'reload' :
				$wcApp->setConfig ( array () );
				$this->getTable ( WcApp::TABLE )->updateRow ( $wcApp );
				return $controller->redirect ()->toUrl ( $uri->getPath () );
				break;
		}
		$viewModel = new ViewModel ( array (
				'reloadUrl' => ClyLib::addQuery ( $uri, array (
						'action' => 'reload' 
				) ),
				'config' => $wcApp->getConfig () 
		) );
		$viewModel->setTemplate ( 'wechat/service/sys_info' );
		return $viewModel;
	}
}