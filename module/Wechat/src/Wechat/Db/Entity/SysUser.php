<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class SysUser extends DbArrayObject {
	const TABLE = 'sys_user';
	const UID = 'uid';
	const USER_NAME = 'userName';
	const PASSWORD = 'passwd';
	const AID = 'aid';
	public function getAid() {
		return $this [SysUser::AID];
	}
	public function setAid($aid) {
		$this [SysUser::AID] = $aid;
	}
	public function getUserName() {
		return $this [SysUser::USER_NAME];
	}
	public function setUserName($userName) {
		$this [SysUser::USER_NAME] = $userName;
	}
	public function setPassword($password) {
		$this [SysUser::PASSWORD] = md5 ( $password );
	}
}