<?php

namespace Wechat\Api\Event;

use Wechat\Db\Entity\WcUser;
use Wechat\Db\Entity\WcMsg;
use Wechat\Db\Entity\WcEvent;
use Wechat\Db\Entity\WcWord;

class MsgDispatchListenerService extends BaseListenerService {
	protected $options = array (
			'module_name' => '消息分发器',
			'listeners' => array (
					array (
							'type' => 'msg.dispatch',
							'handler' => array (
									'this',
									'triggerEvents' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.dispatch.store_user',
							'handler' => array (
									'this',
									'onUserStore' 
							) 
					),
					array (
							'share' => 'inner',
							'type' => 'msg.dispatch.store_msg',
							'handler' => array (
									'this',
									'onMsgStore' 
							) 
					) 
			),
			'trigger_events' => array (
					'msg.dispatch.store_msg',
					'msg.dispatch.store_user',
					'msg.handle' 
			) 
	);
	public function onMsgStore(MsgEvent $e) {
		$wcApp = $e->getParam ( 'WcApp' );
		$requestMsg = $e->getParam ( 'request_msg' );
		// log info
		$this->getLog ()->debug ( $wcApp->getAppType () . '消息' . $requestMsg );
		// if repeat
		$wcMsg = $this->getTable ( WcMsg::TABLE )->selectOne ( array (
				WcMsg::FROM_USER_NAME => $requestMsg->getFromUserName (),
				WcMsg::CREATE_TIME => $requestMsg->getCreateTime () 
		) );
		if ($wcMsg) {
			$this->getLog ()->debug ( __METHOD__ . ':repeat access ' );
			$e->stopPropagation ( true );
			return;
		}
		// save msg
		$msgData = $requestMsg->getMsgData ();
		$eventData = $requestMsg->getEventData ();
		$wordData = $requestMsg->getWordData ();
		$wcMsg = new WcMsg ( $msgData );
		$wcMsg->setAid ( $wcApp->getAid () );
		if ($requestMsg->getMsgType () == 'event') {
			$wcEvent = $this->getTable ( WcEvent::TABLE )->insertRow ( new WcEvent ( $eventData ) );
			$wcMsg->setEid ( $wcEvent->getEid () );
			$wcMsg = $this->getTable ( WcMsg::TABLE )->insertRow ( $wcMsg );
			$e->setParam ( 'wcEvent', $wcEvent );
			$e->setParam ( 'wcMsg', $wcMsg );
		} else {
			$wcWord = $this->getTable ( WcWord::TABLE )->insertRow ( new WcWord ( $wordData ) );
			$wcMsg->setWid ( $wcWord->getWid () );
			$wcMsg = $this->getTable ( WcMsg::TABLE )->insertRow ( $wcMsg );
			$e->setParam ( 'wcWord', $wcWord );
			$e->setParam ( 'wcMsg', $wcMsg );
		}
	}
	public function onUserStore(MsgEvent $e) {
		$wcApp = $e->getParam ( 'WcApp' );
		if (! $wcApp->isServer ())
			return;
		$requestMsg = $e->getParam ( 'request_msg' );
		$wcUser = $this->saveWcUser ( $e );
		if ($wcUser) {
			$e->setParam ( 'wc_user', $wcUser );
			$moduleManager = $this->getServiceLocator ()->get ( 'Wechat\Api\Event\ModuleManagerListenerService' );
			$moduleManager->isDynamic () && $moduleManager->initModulesForUser ( $wcUser );
		} else {
			throw new \Exception ( __METHOD__ . "\n" . var_dump ( $e ) );
		}
	}
	public function saveWcUser($e) {
		$wcApp = $e->getParam ( 'WcApp' );
		$requestMsg = $e->getParam ( 'request_msg' );
		$openId = $requestMsg->getFromUserName ();
		$lastMsgTime = $requestMsg->getCreateTime ();
		$lastMsgContent = $requestMsg->getContent ();
		$wcUser = $this->getTable ( WcUser::TABLE )->selectOne ( array (
				WcUser::OPEN_ID => $openId 
		) );
		$userService = $this->getService ( 'Wechat\Api\User\UserService' );
		if ($wcUser == false) {
			// insert
			$apiUser = $userService->getUserFromHttp ( $openId );
			if (! $apiUser)
				return false;
			$wcUser = new WcUser ( $apiUser->getPopulateArray () );
			$wcUser->refreshUpdateTime ();
			$wcUser->setAid ( $wcApp->getAid () );
			$wcUser->setLastMsgTime ( $lastMsgTime );
			$wcUser->setLastMsgContent ( $lastMsgContent );
			if ($apiUser->getSubscribe () == 1) {
				return $this->getTable ( WcUser::TABLE )->insertRow ( $wcUser );
			}
		} elseif ($wcUser->needUpdate ( $this->getWcOptions ( 'expire_time' ) )) {
			// update
			$apiUser = $userService->getUserFromHttp ( $openId );
			if ($apiUser && $apiUser->getSubscribe () == 1) {
				$wcUser->replaceArray ( $apiUser->getPopulateArray () );
				$wcUser->refreshUpdateTime ();
				$wcUser->setLastMsgTime ( $lastMsgTime );
				$wcUser->setLastMsgContent ( $lastMsgContent );
				return $this->getTable ( WcUser::TABLE )->updateRow ( $wcUser );
			}
		} else {
			$wcUser->setLastMsgTime ( $lastMsgTime );
			$wcUser->setLastMsgContent ( $lastMsgContent );
			return $this->getTable ( WcUser::TABLE )->updateRow ( $wcUser );
		}
		return $wcUser;
	}
}