<?php

namespace Wechat\Db;

use Wechat\Api\Base\AbstractArrayObject;

class DbArrayObject extends AbstractArrayObject {
	public function __construct(array $values = null) {
		if (! $values)
			$values = array ();
		parent::__construct ( $values );
	}
	public function getPrimaryKey() {
		$class = new \ReflectionClass ( get_class ( $this ) );
		$constants = $class->getConstants ();
		foreach ( $constants as $constant ) {
			if (strrpos ( $constant, 'id' ) == 1 && strlen ( $constant ) == 3) {
				return $constant;
			}
		}
	}
	protected function fieldRule($field) {
		return lcfirst ( $field );
	}
}