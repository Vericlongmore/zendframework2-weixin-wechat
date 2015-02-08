<?php

namespace Wechat\Api\OAuth;

use Wechat\Web\Service\BaseWebService;
use Zend\Uri\Uri;
use Cly\Common\ClyLib;
use Zend\View\Model\ViewModel;

class OAuthService extends BaseWebService {
	const SCOPE_BASE = 'snsapi_base';
	const SCOPE_INFO = 'snsapi_userinfo';
	protected $options = array (
			'redirectUri' => 'http://wechat.wechatzf2local.com.cn/wechat/service/oauth',
			'codeUrl' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
			'tokenUrl' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
			'infoUrl' => 'https://api.weixin.qq.com/sns/userinfo',
			'scope' => array (
					'base' => 'snsapi_base',
					'info' => 'snsapi_userinfo' 
			),
			'response_type' => 'code' 
	);
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$id = $controller->params ()->fromRoute ( 'id' );
		$code = $controller->params ()->fromQuery ( 'code' );
		$state = $controller->params ()->fromQuery ( 'state' );
		if ($code && $state) {
			$state = State::fromUrlStr ( $state );
			$appId = $state->getAppId ();
			$secret = $state->getSecret ();
			$uri = new Uri ( $state->getUrl () );
			$token = $this->readToken ( $appId, $secret, $code );
			if ($token) {
				$scope = $token ['scope'];
				$queryArray = array ();
				switch ($scope) {
					case static::SCOPE_BASE :
						$queryArray ['openId'] = $token ['openid'];
						break;
					case static::SCOPE_INFO :
						$info = $this->getInfo ( $token ['access_token'], $token ['openid'] );
						$info && $queryArray ['info'] = serialize ( $info );
						break;
				}
				return $controller->redirect ()->toUrl ( ClyLib::addQuery ( $uri, $queryArray ) );
			}
		}
		$viewModel = new ViewModel ();
		$viewModel->setTemplate ( 'wechat/app/index' );
		return $viewModel;
	}
	public function codeUrlService(\Zend\Mvc\Controller\AbstractController $controller) {
		$appId = $controller->params ()->fromQuery ( 'appId' );
		$secret = $controller->params ()->fromQuery ( 'secret' );
		$url = $controller->params ()->fromQuery ( 'url' );
		$scope = $controller->params ()->fromQuery ( 'scope' );
		if ($appId && $secret && $url && $scope) {
			echo $this->getCodeUrl ( $appId, $secret, $url, $scope );
			exit ();
		}
	}
	public function getCodeUrl($appId, $secret, $url, $scope) {
		$state = new State ();
		$state->setAppId ( $appId );
		$state->setSecret ( $secret );
		$state->setUrl ( $url );
		$queryStr = http_build_query ( array (
				'appid' => $appId,
				'redirect_uri' => $this->getOptions ( 'redirectUri' ),
				'response_type' => 'code',
				'scope' => $scope,
				'state' => $state->toUrlStr () 
		) );
		return $this->getOptions ( 'codeUrl' ) . '?' . $queryStr . '#wechat_redirect';
	}
	public function getToken($appId, $secret, $code) {
		$queryStr = http_build_query ( array (
				'appid' => $appId,
				'secret' => $secret,
				'code' => $code,
				'grant_type' => 'authorization_code' 
		) );
		$url = $this->getOptions ( 'tokenUrl' ) . '?' . $queryStr;
		$httpService = $this->getService ( 'Wechat\Api\Base\HttpService' );
		$result = $httpService->httpPost ( $url );
		if (! $this->isError ( $result )) {
			return json_decode ( $result, true );
		}
	}
	public function getInfo($token, $openId) {
		$queryStr = http_build_query ( array (
				'access_token' => $token,
				'openid' => $openId,
				'lang' => 'zh_CN' 
		) );
		$url = $this->getOptions ( 'infoUrl' ) . '?' . $queryStr;
		$httpService = $this->getService ( 'Wechat\Api\Base\HttpService' );
		$result = $httpService->httpPost ( $url );
		if (! $this->isError ( $result )) {
			return json_decode ( $result, true );
		}
	}
}