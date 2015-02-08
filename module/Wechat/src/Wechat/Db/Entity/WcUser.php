<?php

namespace Wechat\Db\Entity;

use Zend\Serializer\Serializer;
use Wechat\Db\DbArrayObject;

class WcUser extends DbArrayObject {
	const TABLE = 'wc_user';
	const UID = 'uid';
	const SUBSCRIBE = 'subscribe';
	const OPEN_ID = 'openId';
	const NICK_NAME = 'nickName';
	const SEX = 'sex';
	const CITY = 'city';
	const COUNTRY = 'country';
	const PROVINCE = 'province';
	const LANGUAGE = 'language';
	const HEAD_IMG_URL = 'headImgUrl';
	const SUBSCRIBE_TIME = 'subscribeTime';
	const UPDATE_TIME = 'updateTime';
	const CONFIG = 'config';
	const AID = 'aid';
	const GROUP_ID = 'groupId';
	const LAST_MSG_TIME = 'lastMsgTime';
	const LAST_MSG_CONTENT = 'lastMsgContent';
	public function getUid() {
		return $this [static::UID];
	}
	public function getNickName() {
		return $this [static::NICK_NAME];
	}
	public function getConfig() {
		$config = Serializer::unserialize ( $this [static::CONFIG] );
		return $config ? $config : array ();
	}
	public function setConfig($config) {
		$this [static::CONFIG] = Serializer::serialize ( $config );
	}
	public function getOpenModule() {
		$config = $this->getConfig ();
		$list = $config ? $config ['openModule'] : '';
		return $list;
	}
	public function setOpenModule($openModule) {
		$config = $this->getConfig ();
		$config ['openModule'] = $openModule;
		$this->setConfig ( $config );
	}
	public function refreshUpdateTime() {
		$this->setUpdateTime ( time () );
	}
	public function needUpdate($expireTime) {
		if ($expireTime < 0)
			return false;
		if (time () - $this [static::UPDATE_TIME] <= $expireTime)
			return false;
		return true;
	}
}