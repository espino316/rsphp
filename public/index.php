<?php
/**
 * Start point for the application
 *
 * Please report bugs on https://github.com/espino316/rsphp/issues
 *
 * @author Luis Espino <luis@espino.info>
 * @copyright Copyright (c) 2016, Luis Espino. All rights reserved.
 * @license MIT License
 */

//  Global variables definition
define( "DS", DIRECTORY_SEPARATOR);
define( "ROOT", dirname( dirname( __FILE__ ) ) );
define( "APPPATH", ROOT . DS . "application" );
define( "CWD", getcwd());
define( "TAB", "\t");
define( "CRLF", "\r\n");
define( "NEW_LINE","\r\n");
define( "IS_CLI", ( php_sapi_name() === "cli" ) );

//  Requires
require_once (ROOT . DS . "config". DS . "tdeskeys.php");
require ROOT.DS."vendor".DS."autoload.php";

//  Exception handlers
//set_exception_handler( array("RS", "handleExeption") );
//set_error_handler( array("RS", "handleExeption") );
//register_shutdown_function( array("RS", "handleExeption") );

//  Startup the application
RSPhp\Framework\RS::startUp();
