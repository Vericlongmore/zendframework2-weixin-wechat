<?php

namespace Wechat\Api\Msg;

class ResponseTextMsg extends ResponseMsg {
	private $msgTpl = "<Content><![CDATA[%s]]></Content>";
	public function __construct(array $values = null) {
	    parent::__construct($values);
		$this ['MsgType'] = 'text';
	}
	protected function fillTpl() {
		return parent::fillTpl () . sprintf ( $this->msgTpl, $this ['Content'] );
	}
	protected function toSendArray() {
		return array_merge ( parent::toSendArray (), array (
				'text' => array (
						'content' => $this ['Content'] 
				) 
		) );
	}
}