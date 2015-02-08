<?php

namespace Wechat\Api\Base;

use Wechat\Api\Base\BaseService;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class LogService extends BaseService {
	protected $options = array (
			'separate' => true,
			'debugLog' => array (
					'writers' => array (
							array (
									'name' => 'stream',
									'options' => array (
											'stream' => 'data/collect.log',
											'filters' => array (
													array (
															'name' => 'suppress',
															'options' => array (
																	'suppress' => true 
															) 
													) 
											) 
									) 
							) 
					) 
			) 
	);
	protected $logger;
	protected $enable = false;
	public function __construct($serviceLocator) {
		parent::__construct ( $serviceLocator );
		$config = $this->getOptions ( 'debugLog' );
		$logger = new Logger ( $config );
		$wcApp = $this->getWcApp ();
		if ($this->getOptions ( 'separate' ) == true && $wcApp) {
			$stream = 'data/log';
			$stream .= '/' . $wcApp->getAppUser ();
			$stream .= '/' . date ( "ymd", time () ) . '.log';
			@mkdir ( dirname ( $stream ), 0777, true );
			$separateWriter = new Stream ( $stream );
			$logger->addWriter ( $separateWriter );
		}
		$this->setLogger ( $logger );
	}
	public function debug($message, $extra = array()) {
		if (! $this->enable ())
			return;
		$microtime = microtime ();
		$this->getLogger ()->debug ( $microtime . ' ' . $message );
	}
	public function setLogger($logger) {
		$this->logger = $logger;
	}
	public function getLogger() {
		return $this->logger;
	}
	public function enable($enable = null) {
		if ($enable === null)
			return $this->enable;
		$this->enable = $enable;
	}
}