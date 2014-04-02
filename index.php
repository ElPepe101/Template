<?php

$URL = 'http://localhost/TEMPLATE';
$SRVRROOT = NULL; 							// Optional fallback If __DIR__ == NULL
$TEMPLATE = NULL; 							// Optional; Default is default 
$TIMEZONE = 'Mexico/General';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
//ini_set('log_errors', 1);

//testing

// ////////////////////////////////////x
// SETTING TIMEZONE
date_default_timezone_set($TIMEZONE);
//MYSQL SERVER TIME: Mexico/General -2 hours
$hour = date('Y-m-d-H', strtotime('-2 hours'));

/**
 * Define document paths
 */
if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));
define('TEMPLATE' , $TEMPLATE ? $TEMPLATE : 'default');
define('SRVRROOT' , $SRVRROOT ? $SRVRROOT : __DIR__);
define('SITEROOT' , $URL);
define('MODULES', 'modules');
define('SECTIONS', 'sections');

/**
 * Fetch the router
 */
require_once(SRVRROOT . '/sys/' . 'router.php');

//echo SRVRROOT;