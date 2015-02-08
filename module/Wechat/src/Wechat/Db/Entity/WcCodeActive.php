<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcCodeActive extends DbArrayObject {
	const TABLE = 'wc_code_active';
	const AID = 'aid';
	const DATE = 'date';
	const ACTIVE = 'active';
	const CID = 'cid';
	public function addActive() {
		if ($this [static::ACTIVE]) {
			$this [static::ACTIVE] = $this [static::ACTIVE] + 1;
		} else {
			$this [static::ACTIVE] = 1;
		}
	}
}