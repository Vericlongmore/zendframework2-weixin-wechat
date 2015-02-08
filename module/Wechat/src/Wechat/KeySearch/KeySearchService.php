<?php

namespace Wechat\KeySearch;

use Wechat\Api\Event\BaseListenerService;
use Wechat\Api\Event\MsgEvent;
use Wechat\Api\Msg\MsgFactory;

class KeySearchService extends BaseListenerService {
	protected $options = array (
			'module_name' => '关键字搜索',
			'listeners' => array (
					array (
							'type' => 'msg.handle.text',
							'handler' => array (
									'this',
									'onHandleText' 
							),
							200 
					) 
			),
			'keyList' => array () 
	);
	protected $configPageOptions = array (
			'keyList' => array (
					'name' => '关键字回复',
					'type' => 'keyList' 
			) 
	);
	public function onHandleText(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$keyList = $this->getOptions ( 'keyList' );
		$key = $requestMsg->getContent ();
		if (in_array ( $key, array_keys ( $keyList ) )) {
			$responseMsg = MsgFactory::createRichContentMsg ( $requestMsg, $keyList [$key] );
		} elseif ($keyList ['*']) {
			$responseMsg = MsgFactory::createRichContentMsg ( $requestMsg, $keyList ['*'] );
		}
		$e->setParam ( 'response_msg', $responseMsg );
		$this->getEvents ()->trigger ( 'msg.handle.text.key', $e, array (
				$this,
				'callBack' 
		) );
		return true;
	}
	protected function isAddable($shareListenEvent) {
		if (strpos ( $shareListenEvent, 'msg.handle.text.key' ) !== false)
			return true;
		return false;
	}
}