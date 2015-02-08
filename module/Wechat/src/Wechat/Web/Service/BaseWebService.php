<?php

namespace Wechat\Web\Service;

use Wechat\Api\Base\BaseService;
use Zend\Mvc\Controller\AbstractController;

abstract class BaseWebService extends BaseService {
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
		$this->getService ( 'Wechat\Api\Event\ModuleManagerListenerService' );
	}
	abstract public function run(AbstractController $controller);
	public function isError($result) {
		$array = json_decode ( $result, true );
		$errcode = $array ['errcode'];
		$errmsg = $array ['errmsg'];
		if ($errcode && $errcode != 0 || $errmsg && $errmsg != 'ok') {
			$this->doError ( $array );
			return true;
		}
		return false;
	}
	public function doError($json) {
		var_dump ( $json );
		return;
	}
}