<?php

namespace Wechat\Api;

use Zend\Session\Container;

class WcSession {
	protected static $session;
	protected  static function getSession() {
		if (! WcSession::$session) {
			WcSession::$session = new Container ( 'Wechat' );//why
		}
		return WcSession::$session;
	}
	public static function get($key) {
		$session = WcSession::getSession ();
		return $session [$key];
	}
	public static function save($value, $key = '') {
		if (! $key) {
			$key = WcSession::getKey ( $value );
		}
		$session = WcSession::getSession ();
		$session [$key] = $value;
	}
	protected static function getKey($value) {
		$array = explode ( '\\', get_class ( $value ) );
		return array_pop ( $array );
	}
}