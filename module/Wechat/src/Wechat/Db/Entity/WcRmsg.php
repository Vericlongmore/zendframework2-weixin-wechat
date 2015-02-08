<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcRmsg extends DbArrayObject {
	const TABLE = 'wc_rmsg';
	const RID = 'rid';
	const TO_USER_NAME = 'toUserName';
	const FROM_USER_NAME = 'fromUserName';
	const CREATE_TIME = 'createTime';
	const MSG_ID = 'msgId';
	const MSG_TYPE = 'msgType';
	const CONTENT = 'content';
	const MEDIA_ID = 'mediaId';
	const TITLE = 'title';
	const DESCRIPTION = 'description';
	const MUSIC_URL = 'musicUrl';
	const H_Q_MUSIC_URL = 'hQMusicUrl';
	const THUMB_MEDIA_ID = 'thumbMediaId';
	const ARTICLE_COUNT = 'articleCount';
	const NEWS = 'news';
	public function setNews($news) {
		$this ['news'] = serialize ( $news );
	}
	public function getNews() {
		return unserialize ( $this ['news'] );
	}
}