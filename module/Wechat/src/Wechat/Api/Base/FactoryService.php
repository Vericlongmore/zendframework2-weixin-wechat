<?php

namespace Wechat\Api\Base;

use Zend\ServiceManager\ServiceLocatorInterface;
use Wechat\Api\WcSession;

class FactoryService {
	protected $serviceLocator;
	public function __construct(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}
	public function getServiceLocator() {
		return $this->serviceLocator;
	}
	public function getAdapter() {
		return $this->getServiceLocator ()->get ( 'Adapter' );
	}
	public function getDb() {
		return $this->getService ( 'Wechat\Db\DbService' );
	}
	public function getWcApp() {
		return WcSession::get ( 'WcApp' );
	}
	public function getLog($type = 'debugLog') {
		switch ($type) {
			case 'debugLog' :
				$logService = $this->getService ( 'Wechat\Api\Base\LogService' );
				break;
			default :
				$logService = $this->getService ( $type );
				break;
		}
		return $logService;
	}
	public function getTable($dbTable, $features = null, $sql = null) {
		return $this->getDb ()->getTable ( $dbTable, $features, $sql );
	}
	public function getService($service) {
		if ($service === get_class ( $this ))
			return $this;
		return $this->getServiceLocator ()->get ( $service );
	}
}