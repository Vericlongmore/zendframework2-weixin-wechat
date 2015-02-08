<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcWord extends DbArrayObject {
    const TABLE = 'wc_word';
	const WID = 'wid';
	const MSG_ID = 'msgId';
	const CONTENT = 'content';
	const MEDIA_ID = 'mediaID';
	const PIC_ID = 'picId';
	const FORMAT = 'format';
	const THUMB_MEDIA_ID = 'thumbMediaId';
	const LOCATION_X = 'locationX';
	const LOCATION_Y = 'locationY';
	const SCALE = 'scale';
	const LABEL = 'label';
	const TITLE = 'title';
	const DESCRIPTION = 'description';
	const URL = 'url';
	public function getWid() {
		return $this [static::WID];
	}
}