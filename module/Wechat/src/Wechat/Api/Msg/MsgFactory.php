<?php

namespace Wechat\Api\Msg;

class MsgFactory {
	function factory() {
	}
	public static function createRequestMsgFromXml($xml) {
		$array = self::xml2Array ( $xml );
		return new RequestMsg ( $array );
	}
	public static function createRichContentMsg($requestMsg, $richContent) {
		$type = $richContent ['type'];
		$content = $richContent ["{$type}Content"];
		switch ($type) {
			case 'text' :
				$responseMsg = MsgFactory::createResponseTextMsg ( $requestMsg, $content );
				break;
			case 'news' :
				$msg ['ArticleCount'] = count ( $content );
				$msg ['news'] = $content;
				$responseMsg = MsgFactory::createResponseNewsMsg ( $requestMsg, $msg );
				break;
		}
		return $responseMsg;
	}
	public static function createResponseTextMsg(RequestMsg $requestMsg, $content = '') {
		$textMsgArray ['ToUserName'] = $requestMsg ['FromUserName'];
		$textMsgArray ['FromUserName'] = $requestMsg ['ToUserName'];
		$textMsgArray ['MsgId'] = $requestMsg ['MsgId'] ? $requestMsg ['MsgId'] : '';
		$textMsgArray ['CreateTime'] = $requestMsg ['CreateTime'];
		$textMsgArray ['Content'] = $content;
		return new ResponseTextMsg ( $textMsgArray );
	}
	public static function createResponseNewsMsg(RequestMsg $requestMsg, $newsMsgArray) {
		$newsMsgArray ['ToUserName'] = $requestMsg ['FromUserName'];
		$newsMsgArray ['FromUserName'] = $requestMsg ['ToUserName'];
		$newsMsgArray ['MsgId'] = $requestMsg ['MsgId'] ? $requestMsg ['MsgId'] : '';
		$newsMsgArray ['CreateTime'] = $requestMsg ['CreateTime'];
		return new ResponseNewsMsg ( $newsMsgArray );
	}
	public static function xml2Array($xml) {
		$array = array ();
		foreach ( $xml->children () as $child ) {
			$array ["{$child->getName ()}"] = $child->__toString ();
		}
		return $array;
	}
}