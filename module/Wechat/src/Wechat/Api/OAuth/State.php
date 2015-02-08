<?php

namespace Wechat\Api\OAuth;

use Wechat\Api\Base\AbstractArrayObject;

class State extends AbstractArrayObject {
	public function toUrlStr() {
		$appId = $this->getAppId ();
		$secret = $this->getSecret ();
		$url = $this->getUrl ();
		return "$appId#$secret#$url";
	}
	public static function fromUrlStr($str) {
		$array = explode ( '#', $str );
		$state = new State ();
		$state->setAppId ( $array [0] );
		$state->setSecret ( $array [1] );
		$state->setUrl ( $array [2] );
		return $state;
	}
}