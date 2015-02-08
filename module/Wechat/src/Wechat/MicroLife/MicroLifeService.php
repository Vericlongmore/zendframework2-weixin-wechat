<?php

namespace Wechat\MicroLife;

use Wechat\Api\Event\BaseListenerService;
use Wechat\Api\Event\MsgEvent;
use Wechat\Api\Msg\MsgFactory;

class MicroLifeService extends BaseListenerService {
	protected $options = array (
			'module_name' => '搜索接口',
			'listeners' => array (
					array (
							'type' => 'msg.handle.text.key',
							'handler' => array (
									'this',
									'onHandleTextKey' 
							),
							200 
					) 
			),
			'searchInterface' => array (
					'microLife' => array (
							'name' => '微生活',
							'searchUrl' => 'http://www.wechatzf2local.com.cn/ext/searcher.php',
							'searchQuery' => array (
									'fid' => '231',
									'page' => '1' 
							) 
					),
					'news' => array (
							'name' => '新闻互动',
							'searchUrl' => 'http://www.wechatzf2local.com.cn/ext/searcher.php',
							'searchQuery' => array (
									'fid' => '192',
									'page' => '1' 
							) 
					) 
			),
			'searchSelect' => 'microLife' 
	);
	protected $configPageOptions = array (
			'searchInterface' => array (
					'name' => '接口列表',
					'type' => 'interface' 
			),
			'searchSelect' => array (
					'name' => '启动接口',
					'type' => 'select',
					'select_list' => array (
							'call' => 'getSearchSelectList' 
					) 
			) 
	);
	protected function getSearchSelectList() {
		$interfaceArray = $this->getOptions ( 'searchInterface' );
		foreach ( $interfaceArray as $k => $v ) {
			$item ['name'] = $v ['name'];
			$item ['value'] = $k;
			$array [] = $item;
		}
		return $array;
	}
	public function onHandleTextKey(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$responseMsg = $e->getParam ( 'response_msg' );
		$keyword = $requestMsg->getContent ();
		$result = $this->search ( $keyword );
		if ($result && isset ( $result ['cont'] )) {
			if (! $responseMsg) {
				$responseMsg = MsgFactory::createResponseNewsMsg ( $requestMsg, array () );
			}
			$responseMsg = $this->combineResult ( $responseMsg, $result ['cont'] );
			if (count ( $responseMsg->getNews () ) <= 0)
				return;
			$e->setParam ( 'response_msg', $responseMsg );
		}
	}
	/**
	 *
	 * @example http://www.wechatzf2local.com.cn/ext/searcher.php?fid=231&page=1&key=dd
	 */
	public function search($searchStr = '') {
		$interfaceArray = $this->getOptions ( 'searchInterface' );
		$interfaceKey = $this->getOptions ( 'searchSelect' );
		if (isset ( $interfaceArray [$interfaceKey] )) {
			$interface = $interfaceArray [$interfaceKey];
		} else {
			throw new \Exception ( 'no interface select' );
		}
		$queryData = $interface ['searchQuery'] + array (
				'keyword' => $searchStr 
		);
		$httpService = $this->getService ( 'Wechat\Api\Base\HttpService' );
		$url = $httpService->buildUrl ( $interface ['searchUrl'], $queryData );
		if ($result = $httpService->httpGet ( $url )) {
			
			if ($data = json_decode ( $result, true )) {
				return $data;
			} else {
			}
		}
		return false;
	}
	protected function resultToNewsMsgItems($data) {
		$items = array ();
		if (! is_array ( $data ))
			return $items;
		$data = array_splice ( $data, 0, 10 );
		foreach ( $data as $v ) {
			if (isset ( $v ['subject'] ))
				$item ['Title'] = $v ['subject'];
			if (isset ( $v ['content'] ))
				$item ['Description'] = $v ['content'];
			if (isset ( $v ['wap_url'] ))
				$item ['Url'] = $v ['wap_url'];
			if (isset ( $v ['img'] ))
				$item ['PicUrl'] = $v ['img'];
			$items [] = $item;
		}
		return $items;
	}
	public function combineResult($responseMsg, $result) {
		$type = $responseMsg->getMsgType ();
		switch ($type) {
			case 'text' :
				
				break;
			case 'news' :
				$result = $this->resultToNewsMsgItems ( $result );
				$responseMsg->addItems ( $result );
				break;
		}
		return $responseMsg;
	}
}