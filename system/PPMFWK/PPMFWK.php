<?php

namespace PPMFWK;

/**
 * PPMFWK
 * @package  PPMFWK
 * @author   Antonio "ElPepe" Segoviano aunsoyjoven {at} hotmail.com
 */
class PPMFWK {

	public static $APP = 'app';
	
	public static $SITEROOT = 'http://localhost/TEMPLATE';
	
	public static $SRVRROOT = NULL; 											
	
	// Optional; Default is default
	public static $TEMPLATE = 'default'; 										
	
	public static $TIMEZONE = 'Mexico/General';
	
	public static $HOUR = NULL;
	
	public static $MODULES = 'modules';
	
	public static $SECTIONS = 'sections';

	public static $DEBUG = true;
	
	public function __construct() {
	
		if(self::$DEBUG){
			ini_set('error_reporting', E_ALL);
			ini_set('display_errors', 1);	
		}
		
		// ////////////////////////////////////
		// SETTING TIMEZONE
		date_default_timezone_set(self::$TIMEZONE);
		//MYSQL SERVER TIME: Mexico/General -2 hours
		self::$HOUR = date('Y-m-d-H', strtotime('-2 hours'));
		
		//echo 'construct called';
	}

	/**
	 * PPMFWK PSR-0 autoloader
	 */
	public static function autoload($className) {
	
		//echo $className;
	
		$thisClass = str_replace(__NAMESPACE__.'\\', '', __CLASS__);

		$baseDir = __DIR__;

		if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
			$baseDir = substr($baseDir, 0, -strlen($thisClass));
		}

		$className = ltrim($className, '\\');
		$fileName  = $baseDir;
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if (file_exists($fileName)) {
			require $fileName;
		}
	}

	/**
     * Register PPMFWK's PSR-0 autoloader
     */
    public static function registerAutoloader() {
    
        spl_autoload_register(__NAMESPACE__ . "\\PPMFWK::autoload");
    }
    
    //fetch the passed request
    public function router($request) {
    
		// parse the page request and other GET variables
		$parsed = explode('/' , $request);
		
		// the page is the first element
		$page = array_shift($parsed);
		
		$page = $page == ''? 'Home': preg_replace('/\s|\x2d/', '_', $page);
		
		// the rest of the array are get statements, parse them out.
		$getVars = array();
		foreach ($parsed as $argument) {
		
			if(strpos($argument,'=')===FALSE) {
			
				$argument .= '=';
			}
		
			// split GET vars along '=' symbol to separate variable, values
			list($variable , $value) = explode('=' , $argument);
			$getVars[$variable] = urldecode($value);
		}
		
		// compute the path to the file
		$target = self::$SRVRROOT . '/'.self::$APP.'/controllers/' . $page . '.php';
		//echo 'Line 78: '.$target.'<br />';
		
		// get target
		if(file_exists($target)) {
		
			// fetch file
			include_once($target);
			
			// modify page to fit naming convention
			// $class = ucfirst($page) . '_Controller';
			
			// instantiate the appropriate class
			if(class_exists($page)) {
			
				$controller = new $page($page);
			} else {
			
				// did we name our class correctly?
				die('class does not exist!');
			}
		} else {
			// can't find the file in 'controllers'!
			// 404
			die('page does not exist!');
		}
		
		//once we have the controller instantiated, execute the default function
		//pass any GET varaibles to the main method
		$controller->main($getVars);
    }
}