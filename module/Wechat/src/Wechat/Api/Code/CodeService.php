<?php

namespace Wechat\Api\Code;

use Wechat\Api\Base\HttpService;

class CodeService extends HttpService {
	protected $options = array (
			'codeUrl' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create',
			'ticketUrl' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode' 
	);
	public function createTemporaryCode($sceneId, $expire = 1800) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'codeUrl' ) );
		$data = array (
				'expire_seconds' => $expire,
				'action_name' => 'QR_SCENE',
				'action_info' => array (
						'scene' => array (
								'scene_id' => $sceneId 
						) 
				) 
		);
		if ($result = $this->httpPost ( $url, $this->encode ( $data ) )) {
			if (! $this->isError ( $result )) {
				$result = json_decode ( $result, true );
				return $result ['ticket'];
			}
		}
	}
	public function createPermanentCode($sceneId) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'codeUrl' ) );
		$data = array (
				'action_name' => 'QR_LIMIT_SCENE',
				'action_info' => array (
						'scene' => array (
								'scene_id' => $sceneId 
						) 
				) 
		);
		if ($result = $this->httpPost ( $url, $this->encode ( $data ) )) {
			if (! $this->isError ( $result )) {
				$result = json_decode ( $result, true );
				return $result ['ticket'];
			}
		}
	}
	public function getCode($ticket) {
		$url = $this->getOptions ( 'ticketUrl' ) . "?ticket=$ticket";
		if ($result = $this->httpGet ( $url )) {
			return $result;
		}
	}
	public function getTCode($sceneId, $expire = 1800) {
		return $this->getCode ( $this->createTemporaryCode ( $sceneId, $expire ) );
	}
	public function getPCode($sceneId) {
		return $this->getCode ( $this->createPermanentCode ( $sceneId ) );
	}
}