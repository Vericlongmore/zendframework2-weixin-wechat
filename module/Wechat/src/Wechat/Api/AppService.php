<?php

namespace Wechat\Api;

use Wechat\Api\Base\BaseService;
use Wechat\Api\Event\MsgEvent;
use Wechat\Db\Entity\SysUser;
use Wechat\Db\Entity\WcApp;
use Zend\View\Model\ViewModel;

class AppService extends BaseService {
	const MENU = 'menu';
	const MSG_LIMIT_TIME = 'msg_limit_time';
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
		$id = $this->getIdentifier ();
		if ($id != 'debug') {
			$this->getAppConfig ( $id );
		}
	}
	protected function getAppConfig($userName) {
		$sysUser = $this->getTable ( SysUser::TABLE )->selectOne ( array (
				SysUser::USER_NAME => $userName 
		) );
		$wcApp = $this->getTable ( WcApp::TABLE )->selectOne ( array (
				WcApp::AID => $sysUser->getAid ()
		) );
		if (! $wcApp) {
			throw new \Exception ( __METHOD__ );
		}
		WcSession::save ( $sysUser );
		WcSession::save ( $wcApp );
		return $this->mergeOptions ( $wcApp->getConfig () );
	}
	protected function getIdentifier() {
		$application = $this->getApplication ();
		$routeMatch = $application->getMvcEvent ()->getRouteMatch ();
		$userName = $routeMatch->getParams ()['id'];
		return $userName;
	}
	public function run() {
		$e = new MsgEvent ();
		$e->setTarget ( $this )->setApplication ( $this->getApplication () )->setRequest ( $this->getApplication ()->getRequest () );
		$e->setParam ( 'PAGE_ACCESS_TIME', PAGE_ACCESS_TIME );
		$e->setParam ( 'WcApp', $this->getWcApp () );
		$e->setParam ( 'SysUser', WcSession::get ( 'SysUser' ) );
		$this->getLog ()->enable ( true );
		$this->getLog ()->debug ( 'PAGE_ACCESS_TIME:' . PAGE_ACCESS_TIME );
		$this->getLog ()->debug ( __METHOD__ );
		$this->getServiceLocator ()->get ( 'Wechat\Api\Event\ModuleManagerListenerService' )->triggerEvents ( $e );
		$this->getLog ()->debug ( 'finish' . PHP_EOL );
		$viewModel = new ViewModel ();
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate ( 'wechat/empty' );
		return $viewModel;
	}
	public function getApplication() {
		return $this->getServiceLocator ()->get ( 'Application' );
	}
	public function getMenu() {
		return $this->getOptions ( static::MENU );
	}
	public function getMsgLimitTime() {
		return $this->getOptions ( static::MSG_LIMIT_TIME );
	}
}