<?php

namespace Wechat\TwoCode;

use Wechat\Api\Event\BaseListenerService;
use Wechat\Api\Event\MsgEvent;
use Wechat\Api\WcSession;
use Wechat\Db\Entity\WcCode;
use Wechat\Db\Entity\WcCodeActive;

class TwoCodeService extends BaseListenerService {
	protected $options = array (
			'module_name' => '二维码模块',
			'listeners' => array (
					array (
							'type' => 'msg.handle.event.scan',
							'handler' => array (
									'this',
									'onHandleEventScan' 
							) 
					),
					array (
							'type' => 'msg.handle.event.subscribe.code',
							'handler' => array (
									'this',
									'onHandleEventSubscribeNormalCode' 
							) 
					) 
			) 
	);
	public function onHandleEventScan(MsgEvent $e) {
		$wcApp = $this->getWcApp ();
		$aid = $wcApp->getAid ();
		$requestMsg = $e->getParam ( 'request_msg' );
		$sceneId = $requestMsg->getEventKey ();
		$wcCode = $this->getTable ( WcCode::TABLE )->selectOne ( array (
				WcCode::SCENE_ID => $sceneId,
				WcCode::AID => $aid 
		) );
		$cid = $wcCode->getCid ();
		$today = date ( "Ymd", time () );
		$wcCodeActive = $this->getTable ( WcCodeActive::TABLE )->selectOne ( array (
				WcCodeActive::CID => $cid,
				WcCodeActive::DATE => $today 
		) );
		if ($wcCodeActive) {
			$wcCodeActive->addActive ();
			$this->getTable ( WcCodeActive::TABLE )->updateRow ( $wcCodeActive );
		} else {
			$wcCodeActive = new WcCodeActive ();
			$wcCodeActive->setCid ( $cid );
			$wcCodeActive->setDate ( $today );
			$wcCodeActive->addActive ();
			$this->getTable ( WcCodeActive::TABLE )->insertRow ( $wcCodeActive );
		}
	}
	public function onHandleEventSubscribeNormalCode(MsgEvent $e) {
		$wcApp = WcSession::get ( 'WcApp' );
		$aid = $wcApp->getAid ();
		$requestMsg = $e->getParam ( 'request_msg' );
		$sceneId = $requestMsg->getEventKey ();
		$wcCode = $this->getTable ( WcCode::TABLE )->selectOne ( array (
				WcCode::SCENE_ID => $sceneId,
				WcCode::AID => $aid 
		) );
		$cid = $wcCode->getCid ();
		$today = date ( "Ymd", time () );
		$wcCodeActive = $this->getTable ( WcCodeActive::TABLE )->selectOne ( array (
				WcCodeActive::CID => $cid,
				WcCodeActive::DATE => $today 
		) );
		if ($wcCodeActive) {
			$wcCodeActive->addActive ();
			$this->getTable ( WcCodeActive::TABLE )->updateRow ( $wcCodeActive );
		} else {
			$wcCodeActive = new WcCodeActive ();
			$wcCodeActive->setCid ( $cid );
			$wcCodeActive->setDate ( $today );
			$wcCodeActive->addActive ();
			$this->getTable ( WcCodeActive::TABLE )->insertRow ( $wcCodeActive );
		}
	}
}