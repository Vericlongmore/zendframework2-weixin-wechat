<?php

namespace Wechat\Web\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Validator\Authentication;
use Zend\Authentication\Adapter\DbTable;
use Wechat\Web\Form\LoginForm;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractController;
use Wechat\Db\Entity\SysUser;
use Wechat\Db\Entity\WcApp;
use Wechat\Api\WcSession;

class LoginWebService extends BaseWebService {
	protected $authentication;
	public function __construct(ServiceLocatorInterface $serviceLocator) {
		parent::__construct ( $serviceLocator );
		$adapter = $this->getService ( 'Adapter' );
		$this->authentication = new Authentication ();
		$this->authentication->setAdapter ( new DbTable ( $adapter, SysUser::TABLE, SysUser::USER_NAME, SysUser::PASSWORD, 'MD5(?)' ) );
		$this->authentication->setService ( new AuthenticationService () );
	}
	public function run(AbstractController $controller) {
		$form = new LoginForm ( 'login', $controller );
		$data = $controller->params ()->fromPost ();
		if (! empty ( $data )) {
			$form->setData ( $data );
			if ($form->isValid ()) {
				$userName = $form->get ( 'username' )->getValue ();
				$password = $form->get ( 'password' )->getValue ();
				if ($this->authenticate ( $userName, $password )) {
					return $controller->redirect ()->toRoute ( 'home' );
				}
			}
		}
		$controller->layout('layout/index');
		return new ViewModel ( array (
				'form' => $form 
		) );
	}
	public function authenticate($userName, $password) {
		if (empty ( $userName ) || empty ( $password ))
			return false;
		$this->authentication->setIdentity ( $userName );
		$this->authentication->setCredential ( $password );
		if ($this->authentication->isValid ()) {
			$sysUser = $this->getTable ( SysUser::TABLE )->selectOne (
                array (
					SysUser::USER_NAME => $userName 
			) );
			$wcApp = $this->getTable ( WcApp::TABLE )->selectOne (
                array (
					WcApp::AID => $sysUser->getAid () 
			) );
			WcSession::save ( $sysUser );
			WcSession::save ( $wcApp );
			return true;
		}
		return false;
	}
}