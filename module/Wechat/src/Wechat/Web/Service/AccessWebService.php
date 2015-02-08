<?php

namespace Wechat\Web\Service;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Wechat\Db\Entity\WcApp;
use Wechat\Api\WcSession;

class AccessWebService extends BaseWebService {
	const FLAG_NONE = 'flag_none';
	const FLAG_TOKEN = 'flag_token';
	const FLAG_ALL = 'flag_all';
	public function run(AbstractController $controller) {
		$wcApp = $this->getWcApp ();
		$flag = $this->buildFlag ( $wcApp );
		$requestData = $controller->params ()->fromPost ();
		$action = $controller->params ()->fromQuery ( 'action' );
		switch ($action) {
			case 'modify' :
				$flag = 'flag_none';
				break;
			case 'token' :
				$this->saveTokenToDb ( $requestData [WcApp::TOKEN] );
				$flag = 'flag_token';
				break;
			case 'app' :
				$this->saveAppToDb ( $requestData [WcApp::APP_ID], $requestData [WcApp::SECRET], $requestData [WcApp::APP_USER], $requestData [WcApp::APP_TYPE] );
				$flag = 'flag_all';
				break;
		}
		$url = $this->buildUrl ( WcSession::get ( 'SysUser' )->getUserName () );
		$viewModel = new ViewModel ( array (
				'url' => $url,
				'flag' => $flag,
				'wcApp' => $wcApp 
		) );
		$viewModel->setTemplate ( 'wechat/service/access.phtml' );
		return $viewModel;
	}
	protected function buildFlag($wcApp) {
		if ($wcApp->getToken ()) {
			if ($wcApp->getAppId () && $wcApp->getSecret ()) {
				return static::FLAG_ALL;
			} else {
				return static::FLAG_TOKEN;
			}
		}
		return static::FLAG_NONE;
	}
	protected function buildUrl($userName) {
		return $this->getWcOptions ( 'serverUrl' ) . $this->getWcOptions ( 'accessUrl' ) . $userName;
	}
	protected function saveTokenToDb($token) {
		$wcApp = WcSession::get ( 'WcApp' );
		$wcApp->setToken ( $token );
		$wcApp->setIsOpen ( 0 );
		$this->getTable ( WcApp::TABLE )->updateRow ( $wcApp, WcApp::AID );
	}
	protected function saveAppToDb($appId, $secret, $appUser, $appType) {
		$wcApp = WcSession::get ( 'WcApp' );
		$wcApp->setAppId ( $appId );
		$wcApp->setSecret ( $secret );
		$wcApp->setAppUser ( $appUser );
		$wcApp->setAppType ( $appType );
		$wcApp->setIsOpen ( 1 );
		$this->getTable ( WcApp::TABLE )->updateRow ( $wcApp, WcApp::AID );
	}
}