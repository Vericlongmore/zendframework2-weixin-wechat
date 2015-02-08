<?php
return array (
		'wechat_config' => include 'wechat.config.php',
		'log' => array (
				'errorLog' => array (
						'writers' => array (
								array (
										'name' => 'stream',
										'options' => array (
												'stream' => 'data/error.log' 
										) 
								) 
						) 
				) 
		),
		'caches' => array (
				'wechat_cache' => array (
						'adapter' => array (
								'name' => 'filesystem',
								'options' => array (
										'cacheDir' => 'data/cache' 
								) 
						),
						'plugins' => array (
								'serializer' 
						) 
				),
				'wechat_attachement' => array (
						'adapter' => array (
								'name' => 'filesystem',
								'options' => array (
										'cacheDir' => 'data/attachement' 
								) 
						),
						'plugins' => array (
								'serializer' 
						) 
				) 
		),
		'service_manager' => array (
				'abstract_factories' => array (
						'Wechat\Api\Base\BaseFactory',
						'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
						'Zend\Log\LoggerAbstractServiceFactory' 
				) 
		),
		'controllers' => array (
				'invokables' => array (
						'Wechat\Controller\Index' => 'Wechat\Controller\IndexController',
						'Wechat\Controller\App' => 'Wechat\Controller\AppController',
						'Wechat\Controller\Service' => 'Wechat\Controller\ServiceController',
						'Wechat\Controller\Activity' => 'Wechat\Controller\ActivityController',
						'Wechat\Controller\Test' => 'Wechat\Controller\TestController' 
				) 
		),
		'router' => array (
				'routes' => array (
						'home' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/',
										'defaults' => array (
												'__NAMESPACE__' => 'Wechat\Controller',
												'controller' => 'Index',
												'action' => 'menu' 
										) 
								) 
						),
						'logout' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/wechat/index/logout' 
								) 
						),
						'login' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/wechat/index/login' 
								) 
						),
						'wechat' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/wechat',
										'defaults' => array (
												'__NAMESPACE__' => 'Wechat\Controller',
												'controller' => 'Index',
												'action' => 'menu' 
										) 
								),
								'may_terminate' => true,
								'child_routes' => array (
										'default' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/[:controller[/][:action[/][:id]]]',
														'constraints' => array (
																'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
																'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
																'id' => '[a-zA-Z0-9_-]*' 
														),
														'defaults' => array () 
												) 
										) 
								) 
						) 
				) 
		),
		'view_manager' => array (
				'template_map' => array (
						'layout/layout' => __DIR__ . '/../view/layout/menu_content.phtml' 
				),
				'template_path_stack' => array (
						'Wechat' => __DIR__ . '/../view' 
				) 
		) 
);
