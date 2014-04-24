<?php

// A little anti-fallback
if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));

// Manual access
require_once(__DIR__.'/system/PPMFWK/PPMFWK.php');
\PPMFWK\PPMFWK::registerAutoloader();

// Settings
\PPMFWK\PPMFWK::$SRVRROOT = __DIR__;
\PPMFWK\PPMFWK::$SITEROOT = 'http://localhost/TEMPLATE';

// Base site url - Not currently supported!
\PPMFWK\PPMFWK::$micro_config['site_url'] = '/';

// Enable debug mode?
\PPMFWK\PPMFWK::$micro_config['debug_mode'] = TRUE;

// Load boostrap file?
\PPMFWK\PPMFWK::$micro_config['bootstrap'] = FALSE;

// Available translations (Array of Locales)
\PPMFWK\PPMFWK::$micro_config['languages'] = array('en');

/**
* Database
*
* This system uses PDO to connect to MySQL, SQLite, or PostgreSQL.
* Visit http://us3.php.net/manual/en/pdo.drivers.php for more info.
*/
\PPMFWK\PPMFWK::$micro_config['database'] = array(
	'dns' => "mysql:host=127.0.0.1;port=3306;dbname=micromvc",
	'username' => 'root',
	'password' => '',
	//'dns' => "pgsql:host=localhost;port=5432;dbname=micromvc",
	//'username' => 'postgres',
	//'password' => 'postgres',
	'params' => array()
);

/**
* Cookie Handling
*
* To insure your cookies are secure, please choose a long, random key!
* @link http://php.net/setcookie
*/
\PPMFWK\PPMFWK::$micro_config['cookie'] = array(
	'key' => 'very-secret-key',
	'timeout' => time()+(60*60*4), // Ignore submitted cookies older than 4 hours
	'expires' => 0, // Expire on browser close
	'path' => '/',
	'domain' => '',
	'secure' => '',
	'httponly' => '',
);

\PPMFWK\PPMFWK::start();