<?php

namespace Wechat\Web\Service;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\ViewModel;

class MenuWebService extends BaseWebService {
	protected $options = array (
			'config' => array (
					'name' => '系统管理',
					'items' => array (
							'cfg_access' => array (
									'微信接入',
									'/wechat/index/access' 
							),
							'cfg_menu' => array (
									'菜单管理',
									'/wechat/index/wcMenu' 
							),
							'cfg_module' => array (
									'模块管理',
									'/wechat/index/module' 
							),
							'cfg_wc_code' => array (
									'二维码管理',
									'/wechat/index/wcCode' 
							),
							'cfg_info' => array (
									'系统信息',
									'/wechat/index/sysInfo' 
							) 
					) 
			),
			'user' => array (
					'name' => '用户管理',
					'items' => array (
							'user_list' => array (
									'用户列表',
									'/wechat/index/userList' 
							),
							'user_group' => array (
									'分组列表',
									'/wechat/index/groupList' 
							) 
					) 
			),
			'talk' => array (
					'name' => '聊天管理',
					'items' => array (
							'takl_search' => array (
									'聊天搜索',
									'/wechat/index/talkSearch' 
							) 
					) 
			) 
	);
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
	}
	public function run(AbstractController $controller) {
		$controller->layout ( 'layout/menu_layout' );
		$viewModel = new ViewModel ( array (
				'menu' => $this->getMenu ( $this->getOptions () ),
				'items' => $this->getItems ( $this->getOptions () ) 
		) );
		$viewModel->setTemplate ( 'wechat/service/menu' );
		return $viewModel;
	}
	protected function getMenu($menuArray) {
		$menu = array ();
		foreach ( $menuArray as $key => $item ) {
			$menu [] = array (
					'id' => $key,
					'name' => $item ['name'] 
			);
		}
		return json_encode ( $menu, JSON_UNESCAPED_UNICODE );
	}
	protected function getItems($menuArray) {
		$items = array ();
		foreach ( $menuArray as $key => $item ) {
			$items [$key] = array (
					'id' => $key,
					'items' => array () 
			);
			foreach ( $item ['items'] as $itemKey => $itemValue ) {
				array_push ( $items [$key] ['items'], array (
						'id' => $itemKey,
						'name' => $itemValue [0],
						'url' => $itemValue [1] 
				) );
			}
		}
		return json_encode ( $items, JSON_UNESCAPED_UNICODE );
	}
}