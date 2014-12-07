<?php
// A little anti-fallback
if (! defined('__DIR__')) define('__DIR__', dirname(__FILE__));

if( ! class_exists('Locale', false)) {
	die('You must install the <a href="http://php.net/manual/en/book.intl.php">PHP INTL</a> extension.');
}

setlocale(LC_ALL, 'es_ES.UTF-8');

require 'vendor/autoload.php';

// Settings
\iframework\Router::$SRVRROOT = __DIR__;
\iframework\Router::$SITEROOT = array('localhost/example.com', '189.137.75.112/example.com', 'example.com', 'www.example.com');
\iframework\Router::$SAFEAREA = 'portal';
\iframework\Router::$DEBUG = TRUE;
//\iframework\Router::$TEMPLATE = 'example'; 

/**
 * Database
 *
 * This system uses PDO to connect to MySQL, SQLite, or PostgreSQL.
 * Visit http://us3.php.net/manual/en/pdo.drivers.php for more info.
 */
if($_SERVER['HTTP_HOST'] == 'localhost')
{
	\iframework\Router::$config['database'] = array(
		'dns' => "mysql:host=127.0.0.1;port=3306;dbname=icodb",
		'username' => 'ElPepe',
		'password' => '%1a2s3d4f%G_'
	);
}
else
{
	\iframework\Router::$config['database'] = array(
		'dns' => "mysql:host=127.0.0.1;port=3306;dbname=",
		'username' => '',
		'password' => ''
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
\iframework\lib\Micro\Cookie::$settings = array(
	'key' => '',
	'timeout' => time() + (60 * 60 * 4), // Ignore submitted cookies older than 4 hours
	'expires' => 0, // Expire on browser close
	'path' => '/',
	'domain' => '',
	'secure' => '',
	'httponly' => ''
);

\iframework\Router::construct();
$_ses = new \iframework\lib\Session();

// Check login access to module
$_ses->verify();

\iframework\Router::start();