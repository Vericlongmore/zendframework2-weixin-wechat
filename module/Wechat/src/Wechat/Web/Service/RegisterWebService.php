<?php

namespace Wechat\Web\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Wechat\Web\Form\RegisterForm;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractController;
use Wechat\Db\Entity\SysUser;
use Wechat\Db\Entity\WcApp;

class RegisterWebService extends BaseWebService {
	public function __construct(ServiceLocatorInterface $serviceLocator) {
		parent::__construct ( $serviceLocator );
	}
	public function run(AbstractController $controller) {
		$requestData = $controller->params ()->fromPost ();
		$form = new RegisterForm ( 'register' );
		if ($controller->params ()->fromQuery ( 'action' ) == 'submit') {
			$form->setData ( $requestData );
			if ($form->isValid ()) {
				$userName = $requestData ['username'];
				$password = $requestData ['password'];
				if (! empty ( $userName ) && ! empty ( $password )) {
					if (! $this->selectForUserName ( $userName )) {
						$this->saveRegisterToDb ( $userName, $password );
						$loginService = $this->getService ( 'Wechat\Web\Service\LoginWebService' );
						$loginService->authenticate ( $userName, $password );
						return $controller->redirect ()->toRoute ( 'home' );
					}
				}
			}
		}
		$controller->layout ( 'layout/index' );
		$view = new ViewModel ( array (
				'form' => $form 
		) );
		$view->setTemplate ( 'wechat/service/register' );
		return $view;
	}
	protected function selectForUserName($userName) {
		return $this->getTable ( SysUser::TABLE )->selectOne ( array (
				SysUser::USER_NAME => $userName 
		) );
	}
	protected function saveRegisterToDb($userName, $password) {
		$wcApp = $this->getTable ( WcApp::TABLE )->insertRow ( new WcApp (), WcApp::AID );
		$sysUser = new SysUser ();
		$sysUser->setUserName ( $userName );
		$sysUser->setPassword ( $password );
		$sysUser->setAid ( $wcApp->getAid () );
		$this->getTable ( SysUser::TABLE )->insertRow ( $sysUser, SysUser::UID );
	}
}