<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;
use Zend\Serializer\Serializer;

class WcApp extends DbArrayObject {
	const TABLE = 'wc_app';
	const AID = 'aid';
	const APP_ID = 'appId';
	const SECRET = 'secret';
	const TOKEN = 'token';
	const IS_OPEN = 'isOpen';
	const APP_USER = 'appUser';
	const APP_TYPE = 'appType';
	const GROUP_LIST = 'groupList';
	const CONFIG = 'config';
	public function isServer() {
		if ($this [static::APP_TYPE] == '订阅号')
			return false;
		if ($this [static::APP_TYPE] == '服务号')
			return true;
		return false;
	}
	public function isOpen() {
		return $this [static::IS_OPEN];
	}
	public function getConfig() {
		$config = Serializer::unserialize ( $this [static::CONFIG] );
		return $config ? $config : array ();
	}
	public function setConfig($config) {
		$this [WcApp::CONFIG] = Serializer::serialize ( $config );
	}
	public function getGroupList() {
		return unserialize ( $this [static::GROUP_LIST] );
	}
	public function setGroupList($group) {
		$group ['time'] = time ();
		$this [static::GROUP_LIST] = serialize ( $group );
	}
	public function getMenu() {
		$config = $this->getConfig ();
		return $config ['menu'];
	}
	public function setMenu($menu) {
		$config = $this->getConfig ();
		$config ['menu'] = $menu;
		$this->setConfig ( $config );
	}
	public function __toString(){
	    return $this[static::APP_USER].$this[static::APP_TYPE];
	}
}