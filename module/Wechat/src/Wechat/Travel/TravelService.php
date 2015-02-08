<?php

namespace Wechat\Travel;

use Wechat\Api\Event\MsgEvent;
use Wechat\Api\Msg\MsgFactory;
use Wechat\Api\Event\WcListenService;

class TravelService extends WcListenService {
	protected $options = array (
			'module_name' => '旅游团搜索',
			'listeners' => array (
					array (
							'type' => 'msg.handle.text',
							'handler' => array (
									'this',
									'onHandleText' 
							) 
					),
					array (
							'type' => 'msg.handle.event.click',
							'handler' => array (
									'this',
									'onHandleEventClick' 
							) 
					) 
			),
			'searchUrl' => 'http://www.wechatzf2local.com.cn',
			'searchQuery' => array (
					'c' => 'searcher',
					'm' => 'ls',
					'type' => 'all' 
			),
			'attachMsg' => array (),
			'today_cheap' => array (),
			'today_type' => 'news',
			'today_cheap_key' => 'today_on_click',
			'dkf' => 'off',
			'error_text' => '对不起，没有您搜索的路线。请输入“客服”进行人工咨询服务。',
			'module_welcome_text' => '旅游团搜索功能启用',
			'module_on_key' => 'travel_on_click',
			'display_type' => 'news' 
	);
	protected $configPageOptions = array (
			'module_welcome_text' => array (
					'name' => '关注自动回复内容',
					'type' => 'text' 
			),
			'attachMsg' => array (
					'name' => '搜索结果的附加图文消息',
					'type' => 'news' 
			),
			'today_cheap' => array (
					'name' => '今日特惠',
					'type' => 'news' 
			),
			'today_type' => array (
					'name' => '今日特惠显示类型',
					'type' => 'select',
					'select_list' => array (
							array (
									'name' => '图文显示',
									'value' => 'news' 
							),
							array (
									'name' => '文本显示',
									'value' => 'text' 
							) 
					) 
			),
			'error_text' => array (
					'name' => '无搜索记录,返回消息内容',
					'type' => 'text' 
			),
			'dkf' => array (
					'name' => '无搜索记录,跳转客服',
					'type' => 'select',
					'select_list' => array (
							array (
									'name' => '启用',
									'value' => 'on' 
							),
							array (
									'name' => '关闭',
									'value' => 'off' 
							) 
					) 
			),
			'display_type' => array (
					'name' => '结果显示类型',
					'type' => 'select',
					'select_list' => array (
							array (
									'name' => '图文显示',
									'value' => 'news' 
							),
							array (
									'name' => '文本显示',
									'value' => 'text' 
							) 
					) 
			) 
	);
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
	}
	public function onHandleEventClick(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		switch ($requestMsg ['EventKey']) {
			case $this->getOptions ( 'today_cheap_key' ) :
				$displayType = $this->getOptions ( 'today_type' );
				$todayCheap = $this->getOptions ( 'today_cheap' );
				switch ($displayType) {
					case 'news' :
						$todayCount = count ( $this->getOptions ( 'today_cheap' ) );
						$msg ['ArticleCount'] = $todayCount;
						$msg ['news'] = $todayCheap;
						$responseMsg = MsgFactory::createResponseNewsMsg ( $requestMsg, $msg );
						break;
					case 'text' :
						$str = '';
						foreach ( $todayCheap as $value ) {
							$str .= "$value[Title]-$value[Description]-$value[Url]\n";
						}
						$str = substr ( $str, 0, - 1 );
						$responseMsg = MsgFactory::createResponseTextMsg ( $requestMsg, $str );
						break;
				}
				$e->setParam ( 'response_msg', $responseMsg );
				break;
		}
	}
	public function onHandleText(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$searchResult = $this->searchRoute ( $requestMsg ['Content'] );
		if ($searchResult) {
			switch ($this->getOptions ( 'display_type' )) {
				case 'text' :
					$responseMsg = $this->createTextStr ( $requestMsg, $searchResult );
					break;
				case 'news' :
					$responseMsg = $this->createNewsArray ( $requestMsg, $searchResult );
					break;
			}
		} elseif ($this->getOptions ( 'dkf' ) == 'on') {
			$e->setParam ( 'send_type', 'dkf' );
		} else {
			$errorText = $this->getOptions ('error_text');
			$responseMsg = MsgFactory::createResponseTextMsg ( $requestMsg, $errorText );
		}
		$e->setParam ( 'response_msg', $responseMsg );
	}
	/**
	 *
	 * @example http://www.wechatzf2local.com.cn?c=searcher&m=ls&type=all&key=%E6%B3%B0%E5%9B%BD
	 */
	public function searchRoute($searchStr = '') {
		$queryData = $this->getOptions ('searchQuery') + array (
				'key' => $searchStr 
		);
		$httpService = $this->getServiceLocator ()->get ( 'Wechat\Api\Base\HttpService' );
		$url = $httpService->buildUrl ( $this->getOptions ('searchUrl'), $queryData );
		if ($result = $httpService->httpGet ( $url )) {
			if ($result = json_decode ( $result, true ))
				return $result;
		}
		return false;
	}
	public function createNewsArray($requestMsg, $searchResult) {
		$msg = array ();
		$resultCount = count ( $searchResult );
		$attachCount = count ( $this->getOptions ( 'attachMsg' ) );
		for($i = 0; $i < $resultCount - 10 + $attachCount; $i ++) {
			array_pop ( $searchResult );
		}
		$msg ['ArticleCount'] = count ( $searchResult ) + $attachCount;
		$msg ['news'] = array ();
		foreach ( $searchResult as $value ) {
			$item ['Title'] = $value ['name'];
			$item ['Description'] = $value ['brief'];
			$item ['PicUrl'] = $value ['pic'];
			$item ['Url'] = $value ['url'];
			$msg ['news'] [] = $item;
		}
		foreach ( $this->getOptions ( 'attachMsg' ) as $value ) {
			$msg ['news'] [] = $value;
		}
		return MsgFactory::createResponseNewsMsg ( $requestMsg, $msg );
	}
	public function createTextStr($requestMsg, $searchResult) {
		foreach ( $searchResult as $value ) {
			$msg = array ();
			$msg [] = $value ['name'];
			$msg [] = $value ['price'] . '元';
			$msg [] = $value ['url'];
			$responseStr .= implode ( '-', $msg ) . "\n";
		}
		return MsgFactory::createResponseTextMsg ( $requestMsg, $responseStr );
	}
}