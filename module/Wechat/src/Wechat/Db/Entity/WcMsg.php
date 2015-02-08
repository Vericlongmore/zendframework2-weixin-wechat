<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcMsg extends DbArrayObject {
	const TABLE = 'wc_msg';
	const MID = 'mid';
	const TO_USER_NAME = 'toUserName';
	const FROM_USER_NAME = 'fromUserName';
	const CREATE_TIME = 'createTime';
	const MSG_TYPE = 'msgType';
	const EID = 'eid';
	const WID = 'wid';
	public function setWid($wid) {
		$this [WcMsg::WID] = $wid;
	}
}