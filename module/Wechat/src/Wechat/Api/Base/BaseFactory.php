<?php

namespace Wechat\Api\Base;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BaseFactory implements AbstractFactoryInterface {
	public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
		if (strpos ( $requestedName, 'Wechat' ) !== false && class_exists ( $requestedName ))
			return true;
		return false;
	}
	public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
		return new $requestedName ( $serviceLocator );
	}
}