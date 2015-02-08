<?php

namespace Wechat\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Wechat\Api\OAuth\OAuthService;

class ServiceController extends AbstractActionController {
	public function oAuthAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Api\OAuth\OAuthService' )->run ( $this );
	}
	public function oAuthServiceAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Api\OAuth\OAuthService' )->codeUrlService ( $this );
	}
	public function testAction() {
		$token = $this->params ()->fromQuery ( 'token' );
		$info = $this->params ()->fromQuery ( 'info' );
		if (! $token) {
		    $appId = 'wx301cfefe3c09e73b';
		    $secret = '3eea2d0492f065244e0b0854ff4c2125';
		    $url = $this->getRequest ()->getUri ()->toString ();
			$oAuth = $this->getServiceLocator ()->get ( 'Wechat\Api\OAuth\OAuthService' );
			return $this->redirect ()->toUrl ( $oAuth->getCodeUrl ( $appId, $secret, $url, OAuthService::SCOPE_INFO ) );
		}
		var_dump ( unserialize ( $token ) );
		var_dump ( unserialize ( $info ) );
		exit ();
	}
}