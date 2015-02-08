<?php

namespace Wechat\Api\User;

use Wechat\Api\Base\HttpService;
use Wechat\Api\User\User;

class UserService extends HttpService {
	public function getUserFromHttp($openId) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'infoUrl' ) ) . "&openid=$openId&lang=zh_CN";
		if ($result = $this->httpPost ( $url )) {
			if (! $this->isError ( $result )) {
				$result = json_decode ( $result, true );
				if ($result)
					return new User ( $result );
			}
		}
		return false;
	}
	public function getSubscribers($nextOpenId = null) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'subscriberUrl' ) );
		if ($nextOpenId)
			$url .= "&next_openid=$nextOpenId";
		if (($result = $this->httpGet ( $url )) && ! $this->isError ( $result )) {
			$result = json_decode ( $result, true );
			$total = $result ['total'];
			$count = $result ['count'];
			$nextOpenid = $result ['next_openid'];
			$array = $result ['data'] ['openid'];
			if ($nextOpenid) {
				array_merge ( $array, $this->getSubscribers ( $nextOpenid ) );
			}
			return ( array ) $array;
		}
		return false;
	}
}