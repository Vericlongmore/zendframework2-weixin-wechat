<?php

namespace Wechat\Api\Event;

use Zend\Mvc\MvcEvent;

class MsgEvent extends MvcEvent {
	const EVENT_MSG_RESOLVE = 'msg.resolve';
	const EVENT_MSG_DISPATCH = 'msg.dispatch';
	const EVENT_MSG_STORE = 'msg.store';
	const EVENT_MSG_HANDLE = 'msg.handle';
	const EVENT_MSG_HANDLE_TEXT = 'msg.handle.text';
	const EVENT_MSG_HANDLE_IMAGE = 'msg.handle.image';
	const EVENT_MSG_HANDLE_VOICE = 'msg.handle.voice';
	const EVENT_MSG_HANDLE_VEDIO = 'msg.handle.vedio';
	const EVENT_MSG_HANDLE_LOCATION = 'msg.handle.location';
	const EVENT_MSG_HANDLE_LINK = 'msg.handle.link';
	const EVENT_MSG_HANDLE_EVENT = 'msg.handle.event';
	const EVENT_MSG_RESPONE = 'msg.respone';
	const EVENT_MSG_ERROR = 'msg.error';
	const EVENT_MSG_APP_OPEN = 'msg.app.open';
}