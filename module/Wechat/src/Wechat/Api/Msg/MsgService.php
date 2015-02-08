<?php

namespace Wechat\Api\Msg;

use Wechat\Api\Base\HttpService;

class MsgService extends HttpService {
	public function sendMsg($jsonStr) {
		$url = $this->buildTokenUrl ( $this->getOptions ('send_url') );
		if ($result = $this->httpPost ( $url, $jsonStr )) {
			if (! $this->isError ( $result ))
				return true;
		}
		return false;
	}
}