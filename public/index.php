<?php
/**
 * This makes our life easier when dealing with paths.
 * Everything is relative
 * to the application root now.
 */
chdir ( dirname ( __DIR__ ) );
// Setup autoloading
require 'init_autoloader.php';

ini_set ( 'xdebug.var_display_max_children', 128 ); // xdebug.var_display_max_children Type: integer, Default value: 128

ini_set ( 'xdebug.var_display_max_data', 512 ); // Type: integer, Default value: 512

ini_set ( 'xdebug.var_display_max_depth', 15 ); // Type: integer, Default value: 3

define ( 'PAGE_ACCESS_TIME', microtime () );
date_default_timezone_set ( 'PRC' );
//exit($_GET["echostr"]);
// Run the application!
Zend\Mvc\Application::init ( require 'config/application.config.php' )->run ();
