<?php

namespace Wechat\Api\Menu;

class MenuFactory {
	public static function factory() {
		$args = func_get_args ();
		$menuItems = array ();
		// 多个参数，每个参数都是一个menuitem项
		if(count($args)<1)
		    return false;
		if (count ( $args ) > 1) {
			foreach ( $args as $menuItem ) {
				array_push ( $menuItems, $menuItem );
			}
		} else {
			// 参数是menuitem
			if ($args [0] instanceof MenuItem) {
				$menuItems = array_merge ( $menuItems, array (
						'button' => $args [0] 
				) );
				// 参数是menu数组
			} elseif (is_array ( $args [0] )) {
				$menuItems = $args [0];
				// 参数是json对象
			} elseif (is_object ( $args [0] )) {
				$menuItems = Menu::parseJson ( $args [0] );
				// 参数是json字符串
			} elseif (is_string ( $args [0] )) {
				$menuItems = Menu::parseJson ( json_decode ( $args [0] ) );
			}
		}
		return new Menu ( $menuItems );
	}
	public static function createMenuItemFromJson($json) {
		$menuItem ['type'] = $type = $json->type;
		$menuItem ['name'] = $json->name;
		if ($type == 'click') {
			$menuItem ['value'] = $json->key;
		} elseif ($type = 'view') {
			$menuItem ['value'] = $json->url;
		}
	}
}