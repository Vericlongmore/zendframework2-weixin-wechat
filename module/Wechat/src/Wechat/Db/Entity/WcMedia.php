<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcMedia extends DbArrayObject {
	const TABLE = 'wc_media';
	const MID = 'mid';
	const MEDIA_ID = 'mediaId';
	const PATH = 'path';
	const TYPE = 'type';
	const SIZE = 'size';
	const MD5 = 'md5';
}