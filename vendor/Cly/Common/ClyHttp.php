<?php

namespace Cly\Common;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;

class ClyHttp {
	public static function httpGet($url) {
		$client = new Client ( $url, array (
				'sslverifypeer' => false 
		) );
		$response = $client->send ();
		if ($response->isOk ()) {
			return $response->getBody ();
		}
		return false;
	}
	public static function httpPost($url, $content = '', $files = array()) {
		$client = new Client ( $url, array (
				'sslverifypeer' => false 
		) );
		$client->getRequest ()->setMethod ( Request::METHOD_POST )->setContent ( $content )->setFiles ( new Parameters ( $files ) );
		$response = $client->send ();
		if ($response->isOk ()) {
			return $response->getBody ();
		}
		return false;
	}
	public static function curlPost($url, $data = array(), $timeout = 30) {
		$ssl = substr ( $url, 0, 8 ) == "https://" ? TRUE : FALSE;
		$ch = curl_init ();
		$opt = array (
				CURLOPT_URL => $url,
				CURLOPT_POST => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_POSTFIELDS => ( array ) $data,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT => $timeout 
		);
		if ($ssl) {
			$opt [CURLOPT_SSL_VERIFYHOST] = 1;
			$opt [CURLOPT_SSL_VERIFYPEER] = FALSE;
		}
		curl_setopt_array ( $ch, $opt );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		return $data;
	}
}