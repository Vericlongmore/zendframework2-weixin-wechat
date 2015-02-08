<?php

namespace Wechat\Api\Base;

use Wechat\Api\WcSession;

class TokenService extends HttpService {
	public $token;
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
	}
	public function readToken() {
		if (! ($this->doReadToken () && ! $this->isExpired ())) {
			$this->getTokenFromHttp ();
			$this->writeToken ();
		}
		return $this->getToken ();
	}
	public function setToken($token) {
		$this->token = $token;
	}
	public function getToken() {
		return $this->token;
	}
	protected function getCacheKey() {
		return $this->getWcApp ()->getAppId ();
	}
	protected function doReadToken($key = '') {
		$key = $key ?  : $this->getCacheKey ();
		if ($token = $this->getService ( 'wechat_cache' )->getItem ( $key )) {
			$this->setToken ( $token );
			return true;
		}
		return false;
	}
	protected function writeToken($key = '') {
		$key = $key ?  : $this->getCacheKey ();
		return $this->getService ( 'wechat_cache' )->setItem ( $key, $this->getToken () );
	}
	protected function getTokenFromHttp($appid = '', $secret = '') {
		$appId = $appid ?  : WcSession::get ( 'WcApp' )->getAppId ();
		$secret = $secret ?  : WcSession::get ( 'WcApp' )->getSecret ();
		$query_array = array (
				"grant_type" => $this->getOptions ( 'grant_type' ),
				"appid" => $appId,
				"secret" => $secret 
		);
		$url = $this->getOptions ( 'token_url' ) . '?' . http_build_query ( $query_array );
		$time = time ();
		if ($result = $this->httpGet ( $url )) {
			if (! $this->isError ( $result )) {
				$token = json_decode ( $result, true );
				$token ['access_time'] = $time;
				$this->setToken ( $token );
			}
		}
	}
	protected function isExpired() {
		$token = $this->getToken ();
		if ($token ['access_time'] + $token ['expires_in'] > time () - 300)
			return false;
		return true;
	}
}
?>