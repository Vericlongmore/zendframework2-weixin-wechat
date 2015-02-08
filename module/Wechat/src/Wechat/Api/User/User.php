<?php

namespace Wechat\Api\User;

use Wechat\Api\Base\BaseArrayObject;
use Wechat\Db\Entity\WcUser;

class User extends BaseArrayObject {
	const SUBSCRIBE = 'subscribe';
	const OPEN_ID = 'openid';
	const NICK_NAME = 'nickname';
	const SEX = 'sex';
	const LANGUAGE = 'language';
	const CITY = 'city';
	const PROVINCE = 'province';
	const COUNTRY = 'country';
	const HEAD_IMG_URL = 'headimgurl';
	const SUBSCRIBE_TIME = 'subscribe_time';
	public function getSubscribe() {
		return $this [static::SUBSCRIBE];
	}
	public function getFieldsTable() {
		return array (
				static::SUBSCRIBE => WcUser::SUBSCRIBE,
				static::OPEN_ID => WcUser::OPEN_ID,
				static::NICK_NAME => WcUser::NICK_NAME,
				static::SEX => WcUser::SEX,
				static::LANGUAGE => WcUser::LANGUAGE,
				static::CITY => WcUser::CITY,
				static::PROVINCE => WcUser::PROVINCE,
				static::COUNTRY => WcUser::COUNTRY,
				static::HEAD_IMG_URL => WcUser::HEAD_IMG_URL,
				static::SUBSCRIBE_TIME => WcUser::SUBSCRIBE_TIME 
		);
	}
}