<?php

namespace Wechat\Api\Menu;

class Menu {
	protected $menuItems = array ();
	public function getMenuItems() {
		return $this->menuItems;
	}
	public function __construct($menuItems) {
		$this->menuItems = $menuItems;
	}
	public function toJsonStr() {
		return json_encode ( $this->toJsonArray (), JSON_UNESCAPED_UNICODE );
	}
	public function toJsonArray() {
		$array = array ();
		foreach ( $this->menuItems as $key => $value ) {
			if ($value)
				array_push ( $array, $value->toJsonArray () );
		}
		return array (
				'button' => $array 
		);
	}
	public static function parseJson($json) {
		if ($json->menu) {
			$json = $json->menu;
			return self::parseJson ( $json );
		} elseif ($json->button) {
			$json = $json->button;
			return self::parseJson ( $json );
		} else if (is_array ( $json )) {
			$array = array ();
			foreach ( $json as $key => $value ) {
				array_push ( $array, self::parseJson ( $value ) );
			}
			return $array;
		} elseif (count ( $json->sub_button )) {
			$button = new MenuItem ();
			$button->name = $json->name;
			$button->setSubButton ( self::parseJson ( $json->sub_button ) );
			return $button;
		} elseif (! count ( $json->sub_button )) {
			return new MenuItem ( $json );
		}
	}
}
?>