<?php

namespace Wechat\Api\Event;

use Wechat\Api\Base\BaseService;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Feed\Reader\Http\ResponseInterface;
use Zend\EventManager\SharedEventManagerInterface;

abstract class BaseListenerService extends BaseService implements ListenerAggregateInterface {
	protected $listeners = array ();
	protected $shareListeners = array ();
	protected $events;
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
		$this->attach ();
		$this->saveOptionsToDbForApp ();
	}
	public function getListeners() {
		return $this->listeners;
	}
	public function getShareListeners() {
		return $this->shareListeners;
	}
	public function setEvents($events) {
		$this->events = $events;
	}
	public function getEvents() {
		if (! $this->events) {
			$this->setEvents ( $this->getServiceLocator ()->get ( 'EventManager' ) );
			$this->events->setIdentifiers ( get_class ( $this ) );
		}
		return $this->events;
	}
	public function getModuleName() {
		$name = $this->getOptions ( 'module_name' );
		$name = $name ?  : get_class ( $this );
		return $name;
	}
	public function getShareEvents() {
		return $this->getEvents ()->getSharedManager ();
	}
	public function callBack($result) {
		return $result;
	}
	public function attach(EventManagerInterface $events = null) {
		$events = $events ?  : $this->getEvents ();
		foreach ( $this->getOptionListeners () as $value ) {
			if (! (isset ( $value ['share'] ) && ($value ['share'] == 'inner' || $value ['share'] == 'both')))
				continue;
			$value = $this->initOptionListener ( $value );
			$this->listeners [] = $events->attach ( $value ['type'], $value ['handler'], $value ['priority'] );
		}
	}
	public function detach(EventManagerInterface $events = null) {
		$events = $events ?  : $this->getEvents ();
		foreach ( $this->getListeners () as $index => $callback ) {
			if ($events->detach ( $callback )) {
				unset ( $this->listeners [$index] );
			}
		}
	}
	public function attachShare($identifiers, $event = null, SharedEventManagerInterface $shareEvents = null) {
		$shareEvents = $shareEvents ?  : $this->getShareEvents ();
		foreach ( $this->getOptionListeners () as $value ) {
			if (isset ( $value ['share'] ) && $value ['share'] == 'inner')
				continue;
			$value = $this->initOptionListener ( $value );
			if ($event !== null && $event != $value ['type'])
				continue;
			foreach ( $identifiers as $identifier ) {
				$listener = $shareEvents->attach ( $identifiers, $value ['type'], $value ['handler'], $value ['priority'] );
				if (is_array ( $listener ))
					throw new \Exception ( __METHOD__ . ':is_array ( $listener )' );
				$this->shareListeners [] = array (
						'identifier' => $identifier,
						'listener' => $listener 
				);
			}
		}
	}
	public function detachShare(SharedEventManagerInterface $shareEvents = null) {
		$shareEvents = $shareEvents ?  : $this->getShareEvents ();
		foreach ( $this->getShareListeners () as $key => $value ) {
			if ($shareEvents->detach ( $value ['identifier'], $value ['listener'] )) {
				unset ( $this->shareListeners [$key] );
			}
		}
	}
	public function initAddService() {
		$services = $this->getOptionsServices ();
		foreach ( $services as $value ) {
			$service = $this->getServiceLocator ()->get ( $value );
			$service->attachShare ( $this->getEvents ()->getIdentifiers () );
		}
	}
	public function relateService(BaseListenerService $service) {
		$this->addService ( $service );
		$service->addService ( $this );
	}
	public function addService(BaseListenerService $service) {
		$shareEvents = $this->getShareEvents ();
		$shareListenEvents = $service->getShareListenerTypes ();
		$triggerEvents = $this->getOptionTriggerEvents ();
		$autoTriggerEvents = $this->getOptionAutoTriggerEvents ();
		foreach ( $shareListenEvents as $value ) {
			if (in_array ( $value, $triggerEvents ) || in_array ( $value, $autoTriggerEvents ) || $this->isAddable ( $value )) {
				$service->attachShare ( $this->getEvents ()->getIdentifiers (), $value );
			}
		}
	}
	protected function isAddable($shareListenEvent) {
		return false;
	}
	public function triggerEvents(MsgEvent $e) {
		$shortCircuit = function ($r) use($e) {
			if ($r instanceof ResponseInterface) {
				return true;
			}
			if ($e->getError ()) {
				return true;
			}
			return false;
		};
		foreach ( $this->getOptions ( 'trigger_events' ) as $eventName ) {
			$this->getLog ()->debug ( __METHOD__ . ' ' . $eventName );
			$result = $this->getEvents ()->trigger ( $eventName, $e, $shortCircuit );
			if ($e->getError ()) {
				return $this->getEvents ()->trigger ( MsgEvent::EVENT_MSG_ERROR, $e );
			}
			if ($result->stopped ()) {
				return;
			}
		}
	}
	public function initOptionListener($configListener) {
		if (! isset ( $configListener ['share'] ))
			$configListener ['share'] = 'share';
		if (! isset ( $configListener ['type'] ) || ! isset ( $configListener ['handler'] ))
			throw new \Exception ( __METHOD__ . ':listeners config error' );
		if ($configListener ['handler'] [0] == 'this')
			$configListener ['handler'] [0] = $this;
		if (! is_callable ( $configListener ['handler'] ))
			throw new \Exception ( __METHOD__ . ':handler is not callable ' . $configListener ['handler'] [1] );
		if (! isset ( $configListener ['priority'] ))
			$configListener ['priority'] = 1;
		return $configListener;
	}
	public function getOptionListeners() {
		return $this->getOptions ( 'listeners' );
	}
	public function getOptionsServices() {
		return $this->getOptions ( 'service' );
	}
	public function getOptionTriggerEvents() {
		return $this->getOptions ( 'trigger_events' );
	}
	protected function getOptionAutoTriggerEvents() {
		return $this->getOptions ( 'auto_trigger_events' );
	}
	public function getShareListenerTypes() {
		$listeners = $this->getOptionListeners ();
		$shareListeners = array ();
		foreach ( $listeners as $value ) {
			if (isset ( $value ['share'] ) && $value ['share'] == 'inner')
				continue;
			$shareListeners [] = $value ['type'];
		}
		return array_unique ( $shareListeners );
	}
}