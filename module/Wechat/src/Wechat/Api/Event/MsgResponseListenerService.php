<?php

namespace Wechat\Api\Event;

use Wechat\Api\Event\MsgEvent;
use Wechat\Api\Msg\ResponseMsg;
use Wechat\Db\Entity\WcRmsg;
use Wechat\Db\Entity\WcWord;

class MsgResponseListenerService extends BaseListenerService {
	protected $options = array (
			'module_name' => '消息响应器',
			'async' => 'off',
			'listeners' => array (
					array (
							'type' => 'msg.response',
							'handler' => array (
									'this',
									'onMsgResponse' 
							) 
					),
					array (
							'type' => 'msg.error',
							'handler' => array (
									'this',
									'onMsgError' 
							) 
					) 
			) 
	);
	protected $configPageOptions = array (
			'async' => array (
					'name' => '消息响应类型',
					'annotation' => '',
					'type' => 'select',
					'select_list' => array (
							array (
									'name' => '同步',
									'value' => 'off' 
							),
							array (
									'name' => '异步',
									'value' => 'on' 
							) 
					) 
			) 
	);
	public function onMsgResponse(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$responseMsg = $e->getParam ( 'response_msg' );
		$sendType = $e->getParam ( 'send_type' );
		if ($sendType == 'dkf') {
			if ($wcWord = $e->getParam ( 'wcWord' )) {
				$wcWord->setDkf ( 1 );
				$this->getTable ( WcWord::TABLE )->updateRow ( $wcWord );
				echo $requestMsg->toDkfXml ();
			}
		} elseif ($responseMsg) {
			if ($this->isAsync ( $e )) {
				$msgService = $e->getApplication ()->getServiceManager ()->get ( 'Wechat\Api\Msg\MsgService' );
				if ($msgService->sendMsg ( $responseMsg->toSendStr () ))
					$this->saveMsg ( $responseMsg );
			} else {
				echo $responseMsg->toResponseStr ();
				$this->saveMsg ( $responseMsg );
			}
		} else {
			exit ( '' );
		}
		$this->getLog ()->debug ( $responseMsg );
	}
	public function saveMsg(ResponseMsg $responseMsg) {
		$wcRmsg = new WcRmsg ( $responseMsg->getPopulateArray () );
		$wcRmsg = $this->getTable ( WcRmsg::TABLE )->insertRow ( $wcRmsg );
	}
	protected function isAsync(MsgEvent $e) {
		// variable
		$sendType = $e->getParam ( 'send_type' );
		// server
		$wcApp = $e->getParam ( 'WcApp' );
		$appServer = $wcApp->isServer ();
		// time
		$pageAccessTime = $e->getParam ( 'PAGE_ACCESS_TIME' );
		$timeArray = explode ( ' ', $pageAccessTime );
		$pageAccessTime = $timeArray [1] + $timeArray [0];
		$msgLimitTime = $e->getApplication ()->getServiceManager ()->get ( 'Wechat\Api\AppService' )->getMsgLimitTime ();
		$msgLimitTime = $msgLimitTime ?  : 4;
		$now = microtime ( true );
		// $this->getLog ()->debug ( __METHOD__ . "pageAccessTime:$pageAccessTime msgLimitTime:$msgLimitTime now:$now" );
		if ($now - $pageAccessTime >= $msgLimitTime || $sendType == 'async' && $appServer) {
			$this->getLog ()->debug ( __METHOD__ . ':isAsync true ' );
			return true;
		}
		return false;
	}
	public function onMsgError(MsgEvent $e) {
		$error = $e->getError ();
		$this->getService ( 'errorLog' )->debug ( $error );
		exit ( $e->getError () );
	}
}