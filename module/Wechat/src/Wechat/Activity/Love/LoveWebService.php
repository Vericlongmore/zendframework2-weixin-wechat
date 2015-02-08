<?php

namespace Wechat\Activity\Love;

use Wechat\Web\Service\BaseWebService;
use Zend\View\Model\ViewModel;
use Wechat\Db\Entity\SysUser;
use Wechat\Db\Entity\WcApp;
use Wechat\Api\OAuth\OAuthService;
use Wechat\Db\Entity\ActUser;

class LoveWebService extends BaseWebService {
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$uri = $controller->getRequest ()->getUri ();
		$id = $controller->params ()->fromRoute ( 'id' );
		if (! $id)
			throw new \Exception ( __METHOD__ );
		$sysUser = $this->getTable ( SysUser::TABLE )->selectOne ( array (
				SysUser::USER_NAME => $id 
		) );
		$wcApp = $this->getTable ( WcApp::TABLE )->selectOne ( array (
				WcApp::AID => $sysUser->getAid () 
		) );
		if (! $sysUser || ! $wcApp)
			throw new \Exception ( __METHOD__ );
		$openId = $controller->params ()->fromQuery ( 'openId' );
		if (! $openId) {
			$appId = $wcApp->getAppId ();
			$secret = $wcApp->getSecret ();
			$url = $controller->getRequest ()->getUri ()->toString ();
			$oAuth = $this->getServiceLocator ()->get ( 'Wechat\Api\OAuth\OAuthService' );
			return $controller->redirect ()->toUrl ( $oAuth->getCodeUrl ( $appId, $secret, $url, OAuthService::SCOPE_BASE ) );
		}
		$action = $controller->params ()->fromQuery ( 'action' );
		$actUser = $this->getTable ( ActUser::TABLE )->selectOne ( array (
				ActUser::OPEN_ID => $openId 
		) );
		$lookId = $actUser && $actUser->getUid ();
		$queryArray = $uri->getQueryAsArray ();
		unset ( $queryArray ['openId'] );
		$uri->setQuery ( $queryArray );
		$viewModel = new ViewModel ( array (
				'lookUrl' => '/wechat/activity/index/' . $id . '?action=look',
				'exampleUrl' => '/wechat/activity/example/' . $id,
				'indexUrl' => '/wechat/activity/index/' . $id 
		) );
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate ( 'wechat/love/index' );
		switch ($action) {
			case 'submit' :
				$name = $controller->params ()->fromQuery ( 'name' );
				$oName = $controller->params ()->fromQuery ( 'oName' );
				$content = $controller->params ()->fromQuery ( 'content' );
				if ($name && $oName && $content) {
					if (! $actUser) {
						$actUser = new ActUser ();
						$actUser->setOpenId ( $openId );
						$actUser->setName ( $name );
						$actUser->setOName ( $oName );
						$actUser->setContent ( $content );
						$actUser->setAid ( $wcApp->getAid () );
						$actUser = $this->getTable ( ActUser::TABLE )->insertRow ( $actUser );
					} else {
						$actUser->setName ( $name );
						$actUser->setOName ( $oName );
						$actUser->setContent ( $content );
						$actUser = $this->getTable ( ActUser::TABLE )->updateRow ( $actUser );
					}
				}
				$lookId = $actUser->getUid ();
				return $controller->redirect ()->toUrl ( '/wechat/activity/index/' . $id . '?action=look' );
				break;
			case 'look' :
				if ($actUser) {
					$viewModel->setVariables ( array (
							'actUser' => $actUser 
					) );
					$viewModel->setTemplate('wechat/love/love');
				}
				break;
		}
		return $viewModel;
	}
	public function look(\Zend\Mvc\Controller\AbstractController $controller) {
		$id = $controller->params ()->fromRoute ( 'id' );
		$indexUrl = '/wechat/activity/index/' . $id;
		$lookId = $controller->params ()->fromQuery ( 'lookId' );
		if ($lookId) {
			$actUser = $this->getTable ( ActUser::TABLE )->selectOne ( array (
					ActUser::UID => $lookId 
			) );
			if ($actUser) {
				$viewModel = new ViewModel ( array (
						'indexUrl' => $indexUrl,
						'actUser' => $actUser 
				) );
				$viewModel->setTerminal ( true );
				$viewModel->setTemplate ( 'wechat/love/love' );
				return $viewModel;
			}
		}
		return $controller->redirect ()->toUrl ( $indexUrl );
	}
	public function example(\Zend\Mvc\Controller\AbstractController $controller) {
		$id = $controller->params ()->fromRoute ( 'id' );
		$indexUrl = '/wechat/activity/index/' . $id;
		$viewModel = new ViewModel ( array (
				'indexUrl' => $indexUrl 
		) );
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate ( 'wechat/love/example' );
		return $viewModel;
	}
}