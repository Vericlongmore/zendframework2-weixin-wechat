<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcCode extends DbArrayObject {
	const TABLE = 'wc_code';
	const CID = 'cid';
	const SCENE_ID = 'sceneId';
	const CODE = 'code';
	const ACTIVE = 'active';
	const TYPE = 'type';
	const AID = 'aid';
}