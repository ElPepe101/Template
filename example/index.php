<?php
// A little anti-fallback
if (! defined('__DIR__')) define('__DIR__', dirname(__FILE__));

if( ! class_exists('Locale', false)) {
	die('You must install the <a href="http://php.net/manual/en/book.intl.php">PHP INTL</a> extension.');
}

setlocale(LC_ALL, 'es_ES.UTF-8');

// Manual access
require_once (__DIR__ . '/system/Micro/common.php');
require_once (__DIR__ . '/system/PPMFWK/PPMFWK.php');

\PPMFWK\PPMFWK::registerAutoloader();

// Settings
\PPMFWK\PPMFWK::$SRVRROOT = __DIR__;
\PPMFWK\PPMFWK::$SITEROOT = array('localhost/example.com', '189.137.75.112/example.com', 'example.com', 'www.example.com');
\PPMFWK\PPMFWK::$SAFEAREA = 'portal';
\PPMFWK\PPMFWK::$DEBUG = TRUE;
//\PPMFWK\PPMFWK::$TEMPLATE = 'example'; 

/**
 * Database
 *
 * This system uses PDO to connect to MySQL, SQLite, or PostgreSQL.
 * Visit http://us3.php.net/manual/en/pdo.drivers.php for more info.
 */
if($_SERVER['HTTP_HOST'] == 'localhost')
{
	\PPMFWK\PPMFWK::$micro_config['database'] = array(
		'dns' => "mysql:host=127.0.0.1;port=3306;dbname=",
		'username' => '',
		'password' => '',
		// 'dns' => "pgsql:host=localhost;port=5432;dbname=micromvc",
		// 'username' => 'postgres',
		// 'password' => 'postgres',
		'params' => array()
	);
}
else
{
	\PPMFWK\PPMFWK::$micro_config['database'] = array(
		'dns' => "mysql:host=127.0.0.1;port=3306;dbname=",
		'username' => '',
		'password' => '',
		'params' => array()
	);
}

/**
 * Cookie Handling 
 *
 * To insure your cookies are secure, please choose a long, random key!
 *
 * @link http://php.net/setcookie
 *      
 */

\Micro\Cookie::$settings = array(
	'key' => '',
	'timeout' => time() + (60 * 60 * 4), // Ignore submitted cookies older than 4 hours
	'expires' => 0, // Expire on browser close
	'path' => '/',
	'domain' => '',
	'secure' => '',
	'httponly' => ''
);

new \PPMFWK\PPMFWK(true);