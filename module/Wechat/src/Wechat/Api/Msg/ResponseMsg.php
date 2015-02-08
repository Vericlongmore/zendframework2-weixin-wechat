<?php

namespace Wechat\Api\Msg;

abstract class ResponseMsg extends BaseMsg implements ResponseMsgInterface {
	const TO_USER_NAME = 'ToUserName';
	const FORM_USER_NAME = 'FromUserName';
	const CONTENT = 'Content';
	private $msgTpl = "<ToUserName><![CDATA[%s]]></ToUserName>
	    <FromUserName><![CDATA[%s]]></FromUserName>
	    <CreateTime>%s</CreateTime>
	    <MsgType><![CDATA[%s]]></MsgType>";
	public function __construct(array $values = null) {
		parent::__construct ( $values );
		$this->initCreateTime ();
	}
	protected function initCreateTime() {
		$msgCreateTime = $this->getCreateTime ();
		$pageAccessTime = explode ( ' ', PAGE_ACCESS_TIME )[1];
		$now = time () - $this->correctTime ( $msgCreateTime, $pageAccessTime );
		$this ['CreateTime'] = $now;
	}
	protected function correctTime($msgCreateTime, $pageAccessTime) {
		if (! $msgCreateTime || ! $pageAccessTime)
			return 0;
		return $pageAccessTime - $msgCreateTime;
	}
	public function toResponseStr() {
		return '<xml>' . $this->fillTpl () . '</xml>';
	}
	protected function fillTpl() {
		return sprintf ( $this->msgTpl, $this ['ToUserName'], $this ['FromUserName'], $this ['CreateTime'], $this ['MsgType'] );
	}
	public function toSendStr() {
		return json_encode ( $this->toSendArray (), JSON_UNESCAPED_UNICODE );
	}
	protected function toSendArray() {
		return array (
				'touser' => $this ['ToUserName'],
				'msgtype' => $this ['MsgType'] 
		);
	}
}
