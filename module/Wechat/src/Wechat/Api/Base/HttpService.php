<?php

namespace Wechat\Api\Base;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;

class HttpService extends BaseService {
	public function buildTokenUrl($url) {
		$args = func_get_args ();
		$queryData = array (
				'access_token' => $this->getService ( 'Wechat\Api\Base\TokenService' )->readToken ()['access_token'] 
		);
		if (is_array ( $args [1] ))
			$queryData = array_merge ( $args [1] );
		return $this->buildUrl ( $url, $queryData );
	}
	public function buildUrl($url, array $queryData) {
		$str = count ( $queryData ) > 0 ? '?' . http_build_query ( $queryData ) : '';
		return $url . $str;
	}
	public function encode($array) {
		return json_encode ( $array, JSON_UNESCAPED_UNICODE );
	}
	public function doError($json) {
		var_dump ( $json );
		return;
	}
	public function isError($result) {
		$array = json_decode ( $result, true );
		$errcode = $array ['errcode'];
		$errmsg = $array ['errmsg'];
		if ($errcode && $errcode != 0 || $errmsg && $errmsg != 'ok') {
			$this->doError ( $array );
			return true;
		}
		return false;
	}
	public function httpGet($url) {
		$client = new Client ( $url, array (
				// 'adapter' => 'Zend\Http\Client\Adapter\Curl',
				'sslverifypeer' => false 
		) );
		return $this->doSend ( $client );
	}
	public function httpPost($url, $content = '', $files = array()) {
		$client = new Client ( $url, array (
				'sslverifypeer' => false 
		) );
		$client->getRequest ()->setMethod ( Request::METHOD_POST )->setContent ( $content )->setFiles ( new Parameters ( $files ) );
		return $this->doSend ( $client );
	}
	public function doSend($client) {
		$startTime = microtime ( true );
		$response = $client->send ();
		$endTime = microtime ( true );
		$cost = $endTime - $startTime;
		$this->getLog ()->debug ( __METHOD__ . " cost $cost" );
		if ($response->isOk ()) {
			return $response->getBody ();
		}
		return false;
	}
}