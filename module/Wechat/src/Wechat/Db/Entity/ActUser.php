<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class ActUser extends DbArrayObject {
	const TABLE = 'act_user';
	const UID = 'uid';
	const OPEN_ID = 'openId';
	const NAME = 'name';
	const O_NAME = 'oName';
	const CONTENT = 'content';
	const AID = 'aid';
}