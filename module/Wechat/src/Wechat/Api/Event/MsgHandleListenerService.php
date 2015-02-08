<?php

namespace Wechat\Api\Event;

use Wechat\Api\Msg\MsgFactory;
use Wechat\Db\Entity\WcMedia;

class MsgHandleListenerService extends BaseListenerService {
	protected $options = array (
			'module_name' => '消息处理器',
			'listeners' => array (
					array (
							'type' => 'msg.handle',
							'handler' => array (
									'this',
									'onHandle' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.event',
							'handler' => array (
									'this',
									'onHandleEvent' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.event.click',
							'handler' => array (
									'this',
									'onHandleEventClick' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.text',
							'handler' => array (
									'this',
									'onHandleText' 
							),
							100 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.media',
							'handler' => array (
									'this',
									'onStoreMedia' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.location',
							'handler' => array (
									'this',
									'onHandleLocation' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.event.subscribe',
							'handler' => array (
									'this',
									'onHandleEventSubscribe' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.event.unsubscribe',
							'handler' => array (
									'this',
									'onHandleEventunSubscribe' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.event.scan',
							'handler' => array (
									'this',
									'onHandleEventScan' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.handle.event.location',
							'handler' => array (
									'this',
									'onHandleEventLocation' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => array (
									'msg.handle.event.subscribe.normal',
									'msg.handle.event.subscribe.code' 
							),
							'handler' => array (
									'this',
									'onHandleEventSubscribeNormal' 
							) 
					) 
			),
			'dkf_key' => '客服',
			'welcomeContent' => array (
					'type' => 'text',
					'textContent' => '欢迎使用微信服务'
			) 
	);
	protected $configPageOptions = array (
			'welcomeContent' => array (
					'name' => '关注回复',
					'type' => 'richContent' 
			) 
	);
	public function onHandle(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		if ($requestMsg->getMediaId ()) {
			$this->getEvents ()->trigger ( 'msg.handle.media', $e, array (
					$this,
					'callBack' 
			) );
		}
		$this->getEvents ()->trigger ( $this->getMsgType ( $e ), $e, array (
				$this,
				'callBack' 
		) );
	}
	public function onHandleText(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$text = $requestMsg->getContent ();
		$keyArray = explode ( ';', $this->getOptions ( 'dkf_key' ) );
		if (in_array ( $text, $keyArray )) {
			$e->setParam ( 'send_type', 'dkf' );
			return true;
		}
	}
	public function onStoreMedia(MsgEvent $e) {
		$wcApp = $e->getParam ( 'WcApp' );
		$appUser = $wcApp->getAppUser ();
		
		$requestMsg = $e->getParam ( 'request_msg' );
		$mediaService = $this->getService ( 'Wechat\Api\Base\MediaService' );
		
		$mediaType = $requestMsg->getMsgType ();
		$mediaId = $requestMsg->getMediaId ();
		$extension = $mediaService->getExtension ( $mediaType );
		
		$wcMedia = $this->getTable ( WcMedia::TABLE )->selectOne ( array (
				WcMedia::MEDIA_ID => $mediaId 
		) );
		if ($wcMedia)
			return; // is exist file
		$content = $mediaService->download ( $requestMsg->getMediaId () );
		if ($content) {
			$md5Code = md5 ( $content );
			$size = strlen ( $content );
			$result = $this->getTable ( WcMedia::TABLE )->select ( array (
					WcMedia::MD5 => $md5Code,
					WcMedia::SIZE => $size 
			) );
			if ($result->count () > 0) {
				$wcMedia = $result->current ();
				$path = $wcMedia->getPath ();
				if (! file_exists ( $path ))
					$mediaService->saveFile ( $content, $path );
			} else {
				$path = $mediaService->getFilePath ( $md5Code, $appUser, $extension );
				$mediaService->saveFile ( $content, $path );
			}
			$wcMedia = new WcMedia ();
			$wcMedia->setMediaId ( $mediaId );
			$wcMedia->setPath ( $path );
			$wcMedia->setType ( $mediaType );
			$wcMedia->setSize ( $size );
			$wcMedia->setMD5 ( $md5Code );
			$this->getTable ( WcMedia::TABLE )->insertRow ( $wcMedia );
		}
	}
	public function onHandleImage(MsgEvent $e) {
	}
	public function onHandleVoice(MsgEvent $e) {
	}
	public function onHandleVideo(MsgEvent $e) {
	}
	public function onHandleLocation(MsgEvent $e) {
	}
	public function onHandleEvent(MsgEvent $e) {
		$this->getEvents ()->trigger ( $this->getMsgEvent ( $e ), $e, array (
				$this,
				'callBack' 
		) );
	}
	public function onHandleEventClick(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$e->setParam ( 'response_msg', MsgFactory::createResponseTextMsg ( $requestMsg, $requestMsg ['EventKey'] ) );
	}
	public function onHandleEventSubscribe(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$eventKey = $requestMsg->getEventKey ();
		if (! empty ( $eventKey )) {
			$this->getEvents ()->trigger ( 'msg.handle.event.subscribe.code', $e );
		} else {
			$this->getEvents ()->trigger ( 'msg.handle.event.subscribe.normal', $e );
		}
	}
	public function onHandleEventSubscribeNormal(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$appService = $e->getApplication ()->getServiceManager ()->get ( 'Wechat\Api\AppService' );
		$richContent = $this->getOptions ( 'welcomeContent' );
		$responseMsg = MsgFactory::createRichContentMsg ( $requestMsg, $richContent );
		$e->setParam ( 'response_msg', $responseMsg );
	}
	public function onHandleEventUnsubscribe(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
	}
	public function onHandleEventScan(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$eventKey = $requestMsg->getEventKey ();
	}
	public function onHandleEventLocation(MsgEvent $e) {
	}
	protected function getMsgEvent(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		return $this->getMsgType ( $e ) . '.' . strtolower ( $requestMsg ['Event'] );
	}
	protected function getMsgType(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		return MsgEvent::EVENT_MSG_HANDLE . '.' . $requestMsg ['MsgType'];
	}
	protected function isAddable($shareListenEvent) {
		if (strpos ( $shareListenEvent, 'msg.handle.' ) !== false)
			return true;
		return false;
	}
}