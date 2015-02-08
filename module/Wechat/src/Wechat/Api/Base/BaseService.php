<?php

namespace Wechat\Api\Base;

use Wechat\Api\WcSession;
use Wechat\Db\Entity\WcApp;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Cly\Common\ClyLib;

abstract class BaseService extends FactoryService {
	protected $options = array ();
	protected $configPageOptions = array ();
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
		$this->initOptions ();
	}
	public function getOptions($key = null, $options = null) {
		$result = array ();
		$options = $options ?  : $this->options;
		if ($key == null) {
			$result = $options;
		} else if (isset ( $options [$key] )) {
			$result = $options [$key];
		}
		return $result ?  : array ();
	}
	public function setOptions($options) {
		$this->options = $options;
	}
	public function setOption($key, $value) {
		$this->options [$key] = $value;
	}
	public function saveOptionsToDbForApp() {
		$wcApp = $this->getWcApp ();
		if ($wcApp) {
			$config = $wcApp->getConfig ();
			$config [get_class ( $this )] = $this->getOptions ();
			$wcApp->setConfig ( $config );
			return $this->getTable ( WcApp::TABLE )->updateRow ( $wcApp );
		}
	}
	public function webPageConfig(AbstractController $controller) {
		$uri = $controller->getRequest ()->getUri ();
		$data = $controller->params ()->fromPost ( 'data' );
		$data = json_decode ( $data, true );
		if (is_array ( $data )) {
			foreach ( $data as $option ) {
				$keys = array_keys ( $this->options );
				if (in_array ( $option ['key'], $keys )) {
					$this->setOption ( $option ['key'], $option ['value'] );
				}
			}
			if ($this->saveOptionsToDbForApp ())
				exit ( 'success' );
			exit ( 'error' );
		}
		$template = 'wechat/config/module_manager';
		$pageOptions = $this->initConfigPageOptions ();
		if (! $pageOptions) {
			$controller->redirect ()->toUrl ( $uri->getPath () );
		}
		$viewModel = new ViewModel ( array (
				'baseUrl' => $uri->getPath (),
				'moduleUrl' => $uri,
				'options' => $pageOptions 
		) );
		$viewModel->setTemplate ( $template );
		return $viewModel;
	}
	protected function initConfigPageOptions() {
		if (! ClyLib::is_assoc ( $this->configPageOptions ))
			throw new \Exception ( __METHOD__ . ':$configPageOptions must be associate array' );
		foreach ( $this->configPageOptions as $key => $value ) {
			if (! ClyLib::is_assoc ( $value ))
				throw new \Exception ( __METHOD__ . ':$configPageOptions value must be associate array' );
			foreach ( $value as $k => $v ) {
				if (is_array ( $v ) && method_exists ( $this, $v ['call'] )) {
					$params = $v ['params'];
					$this->configPageOptions [$key] [$k] = call_user_func_array ( array (
							$this,
							$v ['call'] 
					), ( array ) $params );
				}
			}
			$this->configPageOptions [$key] ['value'] = $this->getOptions ( $key );
			$this->configPageOptions [$key] ['key'] = $key;
		}
		return $this->configPageOptions;
	}
	protected function initOptions() {
		$this->mergeConfigOptions ();
		$this->mergeWcAppDbOptions ();
	}
	protected function mergeConfigOptions() {
		$options = $this->getWcOptions ( $this->getConfigName () );
		if ($options) {
			$this->mergeOptions ( $options );
		}
	}
	protected function getWcOptions($key = null) {
		$options = $this->getService ( 'config' )['wechat_config'];
		return $this->getOptions ( $key, $options );
	}
	protected function getConfigName() {
		$class = get_class ( $this );
		$class = substr ( $class, strrpos ( $class, '\\' ) + 1 );
		$array = preg_split ( "/(?=[A-Z])/", $class );
		array_shift ( $array );
		return strtolower ( implode ( $array, '_' ) );
	}
	protected function mergeWcAppDbOptions() {
		$wcApp = WcSession::get ( 'WcApp' );
		if ($wcApp) {
			$config = $wcApp->getConfig ();
			$class = get_class ( $this );
			if (isset ( $config [$class] )) {
				$this->mergeOptions ( $config [$class] );
			}
		}
	}
	protected function mergeOptions($options) {
		return $this->setOptions ( array_merge ( $this->options, $options ) );
	}
}