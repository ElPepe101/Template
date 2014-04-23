<?php
if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));

require_once(__DIR__.'/system/PPMFWK/PPMFWK.php');
\PPMFWK\PPMFWK::registerAutoloader();
\PPMFWK\PPMFWK::$SRVRROOT = __DIR__;
\PPMFWK\PPMFWK::$SITEROOT = 'http://localhost/TEMPLATE';

$PPMFWK = new \PPMFWK\PPMFWK();
$PPMFWK->router($_SERVER['QUERY_STRING']);

use \Micro\Session;