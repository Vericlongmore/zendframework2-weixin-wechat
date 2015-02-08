<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Wechat for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Wechat\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AppController extends AbstractActionController {
	public function indexAction() {
        echo "asdf";
		$this->getServiceLocator ()->get ( 'Wechat\Api\AppService' )->run ();

		return array ();
	}
}
