<?php

namespace Wechat\Api\Base;

use Cly\Common\ClyLib;

abstract class BaseArrayObject extends AbstractArrayObject {
	public function getPopulateArray($table = null) {
		if (! $table)
			$table = $this->getFieldsTable ();
		if ($table) {
			// list rules
			if (ClyLib::is_assoc ( $table )) {
				$keys = array_keys ( $table );
				$dbKeys = array_values ( $table );
				$values = $this->getValuesFromArrayByKeys ( $this->getArrayCopy (), $keys );
				return array_combine ( $dbKeys, $values );
			} else {
				// list column
				$data = $this->filterKeys ( $this->getArrayCopy (), $table );
				$keys = array_keys($data);
				$values = $this->getValuesFromArrayByKeys ( $data, $keys );
			}
		} else {
			// all data
			$keys = array_keys ( $this->getArrayCopy () );
			$values = array_values ( $this->getArrayCopy () );
		}
		$dbKeys = $this->getDbKeysInRules ( $keys );
		return array_combine ( $dbKeys, $values );
	}
	public function getDbKeysInRules($apiKeys) {
		$dbKeys = array ();
		foreach ( $apiKeys as $key ) {
			$key = str_replace ( '_', ' ', $key );
			$key = ucwords ( $key );
			$key = str_replace ( ' ', '', $key );
			$dbKeys [] = lcfirst ( $key );
		}
		return $dbKeys;
	}
	public function getFieldsTable() {
	    return null;
	}
}