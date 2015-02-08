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
use Zend\Authentication\Storage\Session;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Exception;
use Wechat\Api\WcSession;

class IndexController extends AbstractActionController {
	protected $noLoginAction = array (
			'login',
			'register' 
	);
	protected $noWcAccessAction = array (
			'menu',
			'access' 
	);
	public function onDispatch(MvcEvent $e) {
        //event first start show
		$routeMatch = $e->getRouteMatch ();
		if (! $routeMatch) {
			/**
			 *
			 * @todo Determine requirements for when route match is missing.
			 *       Potentially allow pulling directly from request metadata?
			 */
			throw new Exception\DomainException ( 'Missing route matches; unsure how to retrieve action' );
		}
		
		$action = $routeMatch->getParam ( 'action', 'not-found' );
		$method = static::getMethodFromAction ( $action );
		
		if (! method_exists ( $this, $method )) {
			$method = 'notFoundAction';
		}
		
		session_start ();
		if ($action == 'logout') {
			session_destroy ();
			return $this->redirect ()->toRoute ( 'login' );
		}
		if ($method != 'notFoundAction' && ! in_array ( $action, $this->noLoginAction )) {
			$session = new Session ();
			if ($session->isEmpty ()) {
				return $this->redirect ()->toRoute ( 'login' );
			} else {
				$wcApp = WcSession::get ( 'WcApp' );//？
				if (! $wcApp->getIsOpen () && ! in_array ( $action, $this->noWcAccessAction )) {
					exit ( '微信还未接入，请接入后再使用本功能' );
				}
			}
		}
		
		$actionResponse = $this->$method ();
		
		$e->setResult ( $actionResponse );
		
		return $actionResponse;
	}
	public function loginAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\LoginWebService' )->run ( $this );
	}
	public function registerAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\RegisterWebService' )->run ( $this );
	}
	public function menuAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\MenuWebService' )->run ( $this );
	}
	public function accessAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\AccessWebService' )->run ( $this );
	}
	public function wcMenuAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\WcMenuWebService' )->run ( $this );
	}
	public function moduleAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\ModuleWebService' )->run ( $this );
	}
	public function userListAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\WcUserWebService' )->run ( $this );
	}
	public function groupListAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\WcGroupWebService' )->run ( $this );
	}
	public function talkSearchAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\WcTalkSearchWebService' )->run ( $this );
	}
	public function talkUserAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\WcTalkUserWebService' )->run ( $this );
	}
	public function sysInfoAction() {
		return $this->getServiceLocator ()->get ( 'Wechat\Web\Service\SysInfoWebService' )->run ( $this );
	}
	public function wcCodeAction() {
 		return $this->getServiceLocator ()->get ( 'Wechat\TwoCode\WcCodeWebService' )->run ( $this );
	}
}
