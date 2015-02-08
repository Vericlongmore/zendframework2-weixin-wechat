<?php

namespace Wechat\Api\Event;

use Wechat\Api\Msg\MsgFactory;
use Wechat\Db\Entity\WcUser;
use Cly\Common\ClyLib;

class ModuleManagerListenerService extends BaseListenerService {
	protected $loadedModuleList = array ();
	protected $options = array (
			'module_name' => '模块管理器',
			'listeners' => array (
					array (
							'type' => 'msg.handle.event.click',
							'handler' => array (
									'this',
									'onHandleEventClick' 
							)
					)
			),
			'trigger_events' => array (
					'msg.resolve',
					'msg.dispatch',
					'msg.response' 
			),
			'module_list' => array (
					'Wechat\Api\Event\ModuleManagerListenerService' => array (
							'must' => 'on',
							'load' => 'on' 
					),
					'Wechat\Api\Event\MsgResolverListenerService' => array (
							'config' => 'off',
							'must' => 'on',
							'load' => 'on' 
					),
					'Wechat\Api\Event\MsgDispatchListenerService' => array (
							'config' => 'off',
							'must' => 'on',
							'load' => 'on' 
					),
					'Wechat\Api\Event\MsgResponseListenerService' => array (
							'config' => 'off',
							'must' => 'on',
							'load' => 'on' 
					),
					'Wechat\Api\Event\MsgHandleListenerService' => array (
							'config' => 'off',
							'must' => 'on',
							'load' => 'on' 
					),
					'Wechat\Robot\RobotService' => array (
							'load' => 'off' 
					),
					'Wechat\Travel\TravelService',
					'Wechat\TwoCode\TwoCodeService',
					'Wechat\KeySearch\KeySearchService',
					'Wechat\MicroLife\MicroLifeService' 
			),
			'open_event_type' => 'msg.handle.text',
			'default_open_module' => 'Wechat\Travel\TravelService',
			'dynamic_module' => 'off' 
	);
	protected $configPageOptions = array (
			'dynamic_module' => array (
					'name' => '动态模块功能',
					'annotation' => '在菜单中链接动态模块触发按钮，模块根据按钮动态触发。',
					'type' => 'select',
					'select_list' => array (
							array (
									'name' => '关闭',
									'value' => 'off' 
							),
							array (
									'name' => '启用',
									'value' => 'on' 
							) 
					) 
			),
			'default_open_module' => array (
					'name' => '默认开放模块',
					'annotation' => '启用动态模块功能后，关注订阅号后默认开启的模块。',
					'type' => 'select',
					'select_list' => array (
							'call' => 'getOpenModuleSelectList' 
					) 
			) 
	);
	protected function getOpenModuleSelectList() {
		$list = $this->filterModuleList ( array (
				'must' => 'off' 
		), false );
		foreach ( $list as $key => $value ) {
			$newList [$key] ['name'] = $value ['name'];
			$newList [$key] ['value'] = $key;
		}
		return $newList;
	}
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
		$this->initModuleList ();
		$this->initModules ();
	}
	public function getModuleNameByModuleList($key) {
		return $this->getOptions ( 'module_list' )[$key]['name'];
	}
	public function getModuleKeyByModuleList($name) {
		return $this->filterModuleList ( array (
				'name' => $name 
		) )[0];
	}
	public function getLoadedModuleList() {
		return $this->loadedModuleList;
	}
	public function getDefaultOpenModule() {
		return $this->getOptions ( 'default_open_module' );
	}
	public function isDynamic() {
		return ($this->getOptions ( 'dynamic_module' ) == 'on') ?  : false;
	}
	/**
	 * enable module for wcUser
	 *
	 * @param MsgEvent $e        	
	 * @throws \Exception
	 */
	public function onHandleEventClick(MsgEvent $e) {
		$requestMsg = $e->getParam ( 'request_msg' );
		$wcUser = $e->getParam ( 'wc_user' );
		if ($this->isDynamic ()) {
			$moduleList = $this->filterModuleList ( array (
					'load' => 'on',
					'must' => 'off' 
			) );
			foreach ( $moduleList as $moduleKey ) {
				$module = $this->getService ( $moduleKey );
				if ($requestMsg->getEventKey () == $module->getOptions ( 'module_on_key' )) {
					$openModuleName = $moduleKey;
					break;
				}
			}
			if ($openModuleName) {
				$wcUser->setOpenModule ( $openModuleName );
				$this->getTable ( WcUser::TABLE )->update ( array (
						WcUser::CONFIG => $wcUser [WcUser::CONFIG] 
				), array (
						WcUser::UID => $wcUser [WcUser::UID] 
				) );
				$openModule = $this->getService ( $openModuleName );
				$text = $openModule->getOptions ( 'module_welcome_text' );
			} else {
				$text = '该模块功能已关闭';
			}
			$responseMsg = MsgFactory::createResponseTextMsg ( $requestMsg, $text );
			$e->setParam ( 'response_msg', $responseMsg );
		}
	}
	/**
	 * init module for wcUser
	 *
	 * @param unknown $wcUser        	
	 */
	public function initModulesForUser($wcUser) {
		$openModule = $wcUser->getOpenModule ();
		$openModule = $openModule ?  : $this->getDefaultOpenModule ();
		if ($openModule) {
			$moduleList = $this->filterModuleList ( array (
					'load' => 'on',
					'must' => 'off' 
			), false );
			unset ( $moduleList [$openModule] );
			$closeModuleList = array_keys ( $moduleList );
			$closeModuleList && $this->removeModules ( $closeModuleList );
		}
	}
	public function addModules($moduleNames) {
		$moduleNames = ( array ) $moduleNames;
		if (count ( $moduleNames ) > 1) {
			foreach ( $moduleNames as $moduleName ) {
				$this->addModules ( $moduleName );
			}
			return;
		}
		$moduleName = $moduleNames [0];
		if (! class_exists ( $moduleName ))
			return;
		$addModule = $this->getService ( $moduleName );
		foreach ( $this->getLoadedModuleList () as $name ) {
			$module = $this->getService ( $name );
			$module->relateService ( $addModule );
		}
		$this->loadedModuleList [] = $moduleName;
	}
	public function removeModules($moduleNames) {
		$moduleNames = ( array ) $moduleNames;
		if (count ( $moduleNames ) > 1) {
			foreach ( $moduleNames as $moduleName ) {
				$this->removeModules ( $moduleName );
			}
			return;
		}
		$moduleName = $moduleNames [0];
		if (! class_exists ( $moduleName ))
			return;
		$module = $this->getService ( $moduleName );
		$module->detachShare ();
		$this->loadedModuleList = ClyLib::array_delete ( $this->loadedModuleList, $moduleName );
	}
	public function openModuleForApp($moduleName) {
		$this->setModuleForApp ( $moduleName, 'on' );
		$this->saveOptionsToDbForApp ();
	}
	public function closeModuleForApp($moduleName) {
		$this->setModuleForApp ( $moduleName, 'off' );
		$this->saveOptionsToDbForApp ();
	}
	public function configModuleForApp($moduleName, $controller) {
		$methodName = 'webPageConfig';
		$module = $this->getService ( $moduleName );
		if (method_exists ( $module, $methodName )) {
			return call_user_func ( array (
					$module,
					$methodName 
			), $controller );
		}
	}
	protected function setModuleForApp($moduleKey, $statu) {
		$moduleList = $this->getOptions ( 'module_list' );
		if (isset ( $moduleList [$moduleKey] ) && $moduleList [$moduleKey] ['must'] == 'off') {
			$moduleList [$moduleKey] ['load'] = $statu;
		}
		$this->setOption ( 'module_list', $moduleList );
	}
	public function filterModuleList(array $conditions = array(), $returnKeys = true) {
		$moduleList = $this->getOptions ( 'module_list' );
		foreach ( $moduleList as $key => $value ) {
			foreach ( $conditions as $k => $v ) {
				if ($value [$k] != $v) {
					unset ( $moduleList [$key] );
					break;
				}
			}
		}
		if ($returnKeys)
			return array_keys ( $moduleList );
		return $moduleList;
	}
	protected function initModules() {
		$loadModuleNames = $this->filterModuleList ( array (
				'load' => 'on' 
		) );
		$this->addModules ( $loadModuleNames );
	}
	protected function initModuleList() {
		$moduleList = $this->options ['module_list'];
		foreach ( $moduleList as $key => $value ) {
			if (is_numeric ( $key )) {
				unset ( $moduleList [$key] );
				$key = $value;
				$value = array ();
			}
			if (! isset ( $value ['load'] ))
				$value ['load'] = 'off';
			if (! isset ( $value ['config'] ))
				$value ['config'] = 'on';
			if (! isset ( $value ['must'] ))
				$value ['must'] = 'off';
			$moduleList [$key] = $value;
			$module = $this->getService ( $key );
			$moduleList [$key] ['name'] = $module->getModuleName ();
		}
		$this->options ['module_list'] = $moduleList;
	}
}