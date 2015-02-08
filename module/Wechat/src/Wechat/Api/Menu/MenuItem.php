<?php

namespace Wechat\Api\Menu;

class MenuItem {
	const CLICK = 'click';
	const VIEW = 'view';
	protected $hasSubButton = false;
	protected $subButtons = array ();
	public $type;
	public $name;
	public $value;
	function __construct($type = '', $name = '', $value = '') {
		if (is_object ( $type )) {
			$this->createByJson ( $type );
		} else {
			$this->type = $type;
			$this->name = $name;
			$this->value = $value;
		}
	}
	protected function createByJson($json) {
		$this->type = $type = $json->type;
		$this->name = $json->name;
		if ($type == self::CLICK) {
			$this->value = $json->key;
		} elseif ($type = self::VIEW) {
			$this->value = $json->url;
		}
	}
	public function setSubButton(array $subButtons) {
		$this->subButtons = $subButtons;
		$this->hasSubButton = $this->subButtons ? true : false;
	}
	public function hasSubButton() {
		return $this->hasSubButton;
	}
	public function toJsonArray() {
		$array = array ();
		if ($this->hasSubButton == false) {
			$array = array (
					'type' => $this->type,
					'name' => $this->name 
			);
			if ($this->type == self::CLICK) {
				$array = array_merge ( $array, array (
						'key' => $this->value 
				) );
			} elseif ($this->type == self::VIEW) {
				$array = array_merge ( $array, array (
						'url' => $this->value 
				) );
			}
		} else {
			foreach ( $this->subButtons as $key => $value ) {
				array_push ( $array, $value->toJsonArray () );
			}
			$array = array (
					'name' => $this->name,
					'sub_button' => $array 
			);
		}
		return $array;
	}
}