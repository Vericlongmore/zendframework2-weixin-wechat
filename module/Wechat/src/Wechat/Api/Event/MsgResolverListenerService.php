<?php

namespace Wechat\Api\Event;

use Wechat\Api\Msg\MsgFactory;

class MsgResolverListenerService extends BaseListenerService {
	protected $options = array (
			'module_name' => '消息解析器',
			'listeners' => array (
					array (
							'type' => 'msg.resolve',
							'handler' => array (
									'this',
									'onCheckSignature' 
							) 
					),
					array (
							'type' => 'msg.resolve',
							'handler' => array (
									'this',
									'onAppOpen' 
							) 
					),
					array (
							'type' => 'msg.resolve',
							'handler' => array (
									'this',
									'onMsgCreate' 
							) 
					) 
			) 
	);
	public function onMsgCreate(MsgEvent $e) {
		$msgStr = $e->getRequest ()->getContent ();
		if ($xml = simplexml_load_string ( $msgStr )) {
			$requestMsg = MsgFactory::createRequestMsgFromXml ( $xml );
			$e->setParam ( 'request_msg', $requestMsg );
			return;
		}
		$e->setError ( __METHOD__ . "-xml parse error:$msgStr" );
		return true;
	}
	public function onAppOpen(MsgEvent $e) {
		$wcApp = $e->getParam ( 'WcApp' );
		if ($wcApp->isOpen ())
			return;
		$params = $e->getRequest ()->getQuery ();
		exit ( $params ['echostr'] );
	}
	public function onCheckSignature(MsgEvent $e) {
		$params = $e->getRequest ()->getQuery ();
		$signature = $params ['signature'];
		$timestamp = $params ['timestamp'];
		$nonce = $params ['nonce'];
		$token = $e->getParam ( 'WcApp' )->getToken ();
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		sort ( $tmpArr, SORT_STRING );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		if ($tmpStr == $signature) {
			return;
		} else {
			$e->setError ( 'error signature' );
			return true;
		}
	}
}