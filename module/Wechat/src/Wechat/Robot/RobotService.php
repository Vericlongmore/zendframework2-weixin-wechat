<?php

namespace Wechat\Robot;

use Wechat\Api\Event\MsgEvent;
use Wechat\Api\Msg\MsgFactory;
use Wechat\Api\Event\WcListenService;

class RobotService extends WcListenService {
	protected $options = array (
			'module_name' => '聊天机器人',
			'listeners' => array (
					array (
							'type' => 'msg.handle.text',
							'handler' => array (
									'this',
									'onHandleText' 
							) 
					) 
			),
			'robotUrl' => 'http://rmbz.net/Api/AiTalk.aspx',
			'searchQuery' => array (
					'key' => 'rmbznet' 
			),
			'talk_type' => array (
					'xiaoi',
					'sim' 
			),
			'talk_type_random' => 'on',
			'msg_key' => 'word',
			'noResultWord' => '听不懂，请你说点其他的。',
			'robot_name' => '小南南的玩偶',
			'module_welcome_text' => '互动机器人功能启用',
			'module_on_key' => 'robot_on_click' 
	);
	public function onHandleText(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$talkType = $this->getOptions ( 'talk_type' );
		$this->getOptions ('talk_type_random') == 'on' && shuffle ( $talkType );
		foreach ( $talkType as $type ) {
			if ($responseStr = $this->getRobotWord ( $requestMsg ['Content'], $type )) {
				$responseMsg = $this->handleRobotWord ( $requestMsg, $responseStr, $type );
				break;
			}
		}
		$e->setParam ( 'response_msg', $responseMsg );
	}
	public function getRobotWord($messageStr, $talkType = '') {
		$talkType = $talkType ?  : 'xiaoi';
		$queryData = $this->getOptions ('searchQuery') + array (
				'talk' => $talkType,
				$this->getOptions ('msg_key') => $messageStr 
		);
		$httpService = $this->getServiceLocator ()->get ( 'Wechat\Api\Base\HttpService' );
		$url = $httpService->buildUrl ( $this->getOptions ('robotUrl'), $queryData );
		if ($result = $httpService->httpGet ( $url )) {
			if ($result = json_decode ( $result, true )) {
				if ($result ['result']) {
					return $result ['content'];
				}
			}
		}
		return $this->getOptions ('noResultWord');
	}
	public function handleRobotWord($requestMsg, $word, $type = '') {
		switch ($type) {
			case 'xiaoi' :
				$word = str_replace ( '%username%', $this->getOptions ('robot_name'), $word );
				$result = preg_match ( '/__([a-z|A-Z]*)=(\[{.*}\])/', $word, $matches );
				if ($result) {
					$msgType = $matches [1];
					$jsonArrayStr = $matches [2];
					$content = json_decode ( $jsonArrayStr, true );
					if ($msgType = '__WSEXimgtxtmsg') {
						return MsgFactory::createResponseNewsMsg ( $requestMsg, $this->handleImageText ( $content ) );
					}
				}
				$result = preg_match ( '/\[link\surl="(.*)"\].*\[\/link\]/', $word, $matches );
				if ($result) {
					$word = preg_replace ( '/\[link\surl="(.*)"\].*\[\/link\]/', $this->handleUrl ( $matches [1] ), $word );
				}
				break;
			case 'sim' :
				break;
		}
		return MsgFactory::createResponseTextMsg ( $requestMsg, $word );
	}
	public function handleImageText($imageTextArray) {
		$msg = array ();
		$msg ['ArticleCount'] = count ( $imageTextArray );
		$msg ['news'] = array ();
		foreach ( $imageTextArray as $value ) {
			$item ['Title'] = $value ['title'];
			$item ['Description'] = $value ['description'];
			$item ['PicUrl'] = $this->handleUrl ( $value ['image'] );
			$item ['Url'] = $this->handleUrl ( $value ['url'] );
			$msg ['news'] [] = $item;
		}
		return $msg;
	}
	public function handleUrl($url) {
		$url = preg_replace ( '/[\n|\s]/', "", $url );
		$result = preg_match ( '/([^\/]*\.)(\/.*)/', $url, $matches );
		if ($result) {
			$url = $matches [1] . 'xiaoi.com' . $matches [2];
		}
		return $url;
	}
}