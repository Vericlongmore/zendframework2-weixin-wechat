<?php

namespace Wechat\Api\Base;

use Zend\Stdlib\Parameters;

abstract class AbstractArrayObject extends Parameters {
	public static function getValuesFromArrayByKeys($array, $keys) {
		$keys = ( array ) $keys;
		$newArray = array ();
		foreach ( $keys as $key ) {
			$newArray [] = $array [$key];
		}
		return $newArray;
	}
	public function filterKeys($array, $keys) {
		$keys = ( array ) $keys;
		$newArray = array ();
		foreach ( $keys as $key ) {
			if (isset ( $array [$key] )) {
				$newArray [$key] = $array [$key];
			}
		}
		return $newArray;
	}
	public function replaceArray(array $data) {
		$keys = array_keys ( $data );
		foreach ( $keys as $key ) {
			if (isset ( $this [$key] )) {
				$this [$key] = $data [$key];
			}
		}
		return $this->getArrayCopy ();
	}
	public function __call($method, $params) {
		$getPre = strpos ( $method, 'get' );
		$setPre = strpos ( $method, 'set' );
		if ($getPre === 0) {
			$field = str_replace ( 'get', '', $method );
			$field = $this->fieldRule ( $field );
			return $this [$field];
		} elseif ($setPre === 0) {
			$field = str_replace ( 'set', '', $method );
			$field = $this->fieldRule ( $field );
			$this [$field] = $params [0];
		}
	}
	protected function fieldRule($field) {
		return $field;
	}
	public function __toString() {
		return var_export ( $this->getArrayCopy (), true );
	}
}