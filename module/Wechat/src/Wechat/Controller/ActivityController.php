<?php

namespace Wechat\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ActivityController extends AbstractActionController {
	public function indexAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Activity\Love\LoveWebService' )->run ( $this );
	}
	public function exampleAction(){
	    return $this->getServiceLocator ()->get ( 'Wechat\Activity\Love\LoveWebService' )->example ( $this );
	}
	public function lookAction(){
	    return $this->getServiceLocator ()->get ( 'Wechat\Activity\Love\LoveWebService' )->look ( $this );
	}
}