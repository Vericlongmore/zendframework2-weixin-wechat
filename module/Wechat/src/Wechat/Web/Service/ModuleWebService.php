<?php

namespace Wechat\Web\Service;

use Zend\View\Model\ViewModel;
use Wechat\Db\Entity\WcApp;

class ModuleWebService extends BaseWebService {
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$baseUrl = $controller->getRequest ()->getUri ()->getPath ();
		$wcApp = $this->getWcApp ();
		$moduleManager = $controller->getServiceLocator ()->get ( 'Wechat\Api\Event\ModuleManagerListenerService' );
		$action = $controller->params ()->fromQuery ( 'action' );
		$moduleClass = $controller->params ()->fromQuery ( 'module' );
		if ($action && $moduleClass && class_exists ( $moduleClass )) {
			switch ($action) {
				case 'open' :
					$moduleManager->openModuleForApp ( $moduleClass );
					return $controller->redirect ()->toUrl ( $baseUrl );
				case 'close' :
					$moduleManager->closeModuleForApp ( $moduleClass );
					return $controller->redirect ()->toUrl ( $baseUrl );
				case 'reload' :
					$config = $wcApp->getConfig ();
					if ($config) {
						unset ( $config [$moduleClass] );
					}
					$wcApp->setConfig ( $config );
					$this->getTable ( WcApp::TABLE )->updateRow ( $wcApp );
					break;
				case 'submit' :
				case 'config' :
					$result = $moduleManager->configModuleForApp ( $moduleClass, $controller );
					if ($result)
						return $result;
			}
		}
		$innerModuleList = $moduleManager->filterModuleList ( array (
				'must' => 'on' 
		), false );
		$outerModuleList = $moduleManager->filterModuleList ( array (
				'must' => 'off' 
		), false );
		$innerModules = array ();
		$outerModules = array ();
		foreach ( $innerModuleList as $key => $value ) {
			$m ['name'] = $moduleManager->getModuleNameByModuleList ( $key );
			$m ['config_url'] = $baseUrl . '?' . http_build_query ( array (
					'action' => 'config',
					'module' => $key 
			) );
			$m ['reload_url'] = $baseUrl . '?' . http_build_query ( array (
					'action' => 'reload',
					'module' => $key 
			) );
			$innerModules [] = $m;
		}
		foreach ( $outerModuleList as $key => $value ) {
			$m ['name'] = $moduleManager->getModuleNameByModuleList ( $key );
			$m ['statu'] = $value ['load'];
			$m ['reload_url'] = $baseUrl . '?' . http_build_query ( array (
					'action' => 'reload',
					'module' => $key 
			) );
			$m ['open_url'] = $baseUrl . '?' . http_build_query ( array (
					'action' => 'open',
					'module' => $key 
			) );
			$m ['close_url'] = $baseUrl . '?' . http_build_query ( array (
					'action' => 'close',
					'module' => $key 
			) );
			$m ['config_url'] = $baseUrl . '?' . http_build_query ( array (
					'action' => 'config',
					'module' => $key 
			) );
			$outerModules [] = $m;
		}
		$viewModel = new ViewModel ( array (
				'inner_modules' => $innerModules,
				'outer_modules' => $outerModules 
		) );
		$viewModel->setTemplate ( 'wechat/service/module' );
		return $viewModel;
	}
}