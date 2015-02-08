<?php

namespace Wechat\Api\Msg;

class RequestMsg extends BaseMsg {
	/**
	 * base
	 */
	const TO_USER_NAME = 'ToUserName';
	const FORM_USER_NAME = 'FromUserName';
	const CREATE_TIME = 'CreateTime';
	const MSG_TYPE = 'MsgType';
	/**
	 * word
	 */
	const MSG_ID = 'MsgId';
	const CONTENT = 'Content';
	const MEDIA_ID = 'MediaId';
	const PIC_URL = 'PicUrl';
	const FORMAT = 'Format';
	const THUMB_MEDIA_ID = 'ThumbMediaId';
	const LOCATION_X = 'Location_X';
	const LOCATION_Y = 'Location_Y';
	const SCALE = 'Scale';
	const LABEL = 'Label';
	const TITLE = 'Title';
	const DESCRIPTION = 'Description';
	const URL = 'Url';
	/**
	 * event
	 */
	const EVENT = 'Event';
	const EVENT_KEY = 'EventKey';
	const TICKET = 'Ticket';
	const LATITUDE = 'Latitude';
	const LONGITUDE = 'Longitude';
	const PRECISION = 'Precision';
	private $msgTpl = "
	    <xml>
	    <ToUserName><![CDATA[%s]]></ToUserName>
	    <FromUserName><![CDATA[%s]]></FromUserName>
	    <CreateTime>%s</CreateTime>
	    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
	    </xml>";
	public function toDkfXml() {
		return sprintf ( $this->msgTpl, $this->getFromUserName (), $this->getToUserName (), $this->getCreateTime () );
	}
	public function getMsgData() {
		return $this->getPopulateArray ( $this->getFieldsTable () );
	}
	public function getWordData() {
		return $this->getPopulateArray ( $this->getWordTable () );
	}
	public function getEventData() {
		return $this->getPopulateArray ( $this->getEventTable () );
	}
	public function getFieldsTable() {
		return array (
				static::TO_USER_NAME,
				static::FORM_USER_NAME,
				static::CREATE_TIME,
				static::MSG_TYPE 
		);
	}
	public function getWordTable() {
		return array (
				static::MSG_ID,
				static::CONTENT,
				static::MEDIA_ID,
				static::PIC_URL,
				static::FORMAT,
				static::THUMB_MEDIA_ID,
				static::LOCATION_X,
				static::LOCATION_Y,
				static::SCALE,
				static::LABEL,
				static::TITLE,
				static::DESCRIPTION,
				static::URL 
		);
	}
	public function getEventTable() {
		return array (
				static::EVENT,
				static::EVENT_KEY,
				static::TICKET,
				static::LATITUDE,
				static::LONGITUDE,
				static::PRECISION 
		);
	}
}