<?php

namespace iframework;

/**
 * PPMFWK
 *
 * @package PPMFWK
 * @author Antonio "ElPepe" Segoviano aunsoyjoven {at} hotmail.com
 */
class Router
{
	
	public static $APP = 'app';

	public static $SITEROOT = 'http://localhost/TEMPLATE';

	public static $SRVRROOT = NULL;

	/**
	 * Use only if login is outside the system
	 *
	 * @var string static $SAFEAREA
	 *     
	 *      @TODO IMPROVE use of this var,
	 *      need to declare safe routes
	 *      without login and safe areas
	 *      outside the system.
	 */
	public static $SAFEAREA = '/';
	
	// Optional; Default is default
	public static $TEMPLATE = 'default';

	public static $TIMEZONE = 'Mexico/General';

	public static $HOUR = NULL;

	public static $MODULES = 'modules';

	public static $SECTIONS = 'sections';

	public static $DEBUG = true;
	
	public static $config = array();

	private static $route = '';

	private static $hook = '';
	
	private static $GET = array();
	
	/**
	 *
	 * @param unknown $login        	
	 */
	public function __construct($session)
	{
		ini_set('error_reporting', E_ALL);

		if (self::$DEBUG)
			ini_set('display_errors', 1);
		else
			ini_set('display_errors', 0);
		
		$this->root();
		
		// ////////////////////////////////////
		// SETTING TIMEZONE
		date_default_timezone_set(self::$TIMEZONE);
		
		// MYSQL SERVER TIME: Mexico/General -2 hours
		self::$HOUR = date('Y-m-d-H', strtotime('-2 hours'));
		
		// PREPARE THE ROUTE;
		self::$route = strtolower(self::path($_SERVER['QUERY_STRING'])[1]);
		
		// NOT USING LOGIN?
		if (! $session)
		{
			self::$route = strtolower(self::start($route));
		}
		// CHECK LOGIN ACCESS TO MODULE
		elseif ($session->verify(true))
		{
			self::$route = strtolower(self::start($route));
		}
		// NO ACCESS? GO TO LOGIN
		else
		{
			header('location: ' . self::$SITEROOT.'?baduser=');
		}
	}

	/**
	 * PPMFWK PSR-0 autoloader
	 *
	public static function autoload($className)
	{
		$thisClass = str_replace(__NAMESPACE__ . '\\', '', __CLASS__);
		$baseDir = __DIR__;
		
		if (substr($baseDir, - strlen($thisClass)) === $thisClass)
		{
			$baseDir = substr($baseDir, 0, - strlen($thisClass));
		}
		
		$className = ltrim($className, '\\');
		$fileName = $baseDir;
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\'))
		{
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		if (file_exists($fileName))
		{
			require $fileName;
		}
	}

	/**
	 * Register PPMFWK's PSR-0 autoloader
	 *
	public static function registerAutoloader()
	{
		spl_autoload_register(__NAMESPACE__ . "\\PPMFWK::autoload");
	}

	/**
	 * fetch the passed request
	 *
	 * @param unknown $request        	
	 */
	public static function path($request)
	{
		// parse the page request and other GET variables
		$parsed = explode('/', (string) $request);
		
		// the page is the first element
		$page = array_shift($parsed);
		$page = $page == '' ? 'Home' : preg_replace('/\s|\x2d/', '_', $page);
		
		// the first of the array is the Action (Hook)
		// IF using sessions use default, then main.
		self::$hook = isset($parsed[0]) && ! empty($parsed[0]) ? array_shift($parsed) : 'main';
		
		// the rest of the array are get statements, parse them out.
		foreach ($parsed as $argument)
		{
			if (strpos($argument, '=') === FALSE) $argument .= '=';
			
			// split GET vars along '=' symbol to separate variable, values
			list ($variable, $value) = explode('=', $argument);
			self::$getVars[$variable] = urldecode($value);
		}
		
		// compute the path to the file
		$page = str_replace(' ', '_', ucwords(str_replace('_', ' ', $page)));
		$target = self::$SRVRROOT . '/' . self::$APP . '/controllers/' . $page . '.php';

		// get target
		if (file_exists($target))
		{
			return array(
				$target,
				$page
			);
		}
		
		return array();
	}

	/**
	 * 
	 * @param array $router
	 * @return unknown|boolean
	 */
	private function start(array $router)
	{
		list ($file, $page) = $router;
		
		// fetch file
		include_once ($file);
		
		// instantiate the appropriate class
		if (class_exists($page))
		{
			// instantiate the controller
			$controller = new $page($page, isset(self::$getVars['ajax'])? true: false );
			
			// For security reasons, obtain only controller
			// methods without implements or extents
			// http://stackoverflow.com/questions/1960365/php-get-class-methods-problem
			$controller_methods = array_diff(get_class_methods($controller), get_class_methods(get_parent_class($controller)));
			
			// If using logout function from another instance
			// Eg.: PPMFWK\MicroHandler Trait
			// $traits = class_uses(get_parent_class($controller));
			! method_exists($controller, 'logout') && ! in_array($controller_methods, 'logout') ?  : array_push($controller_methods, 'logout');
			
			// Execute the method
			if (in_array(self::$hook, $controller_methods))
			{
				// pass any GET varaibles to the main method
				$controller->{self::$hook}(self::$getVars);
			}
			// There's no hook to call
			else
			{
				self::_404('The action is not defined');
			}
			
			return $page;
		}
		
		// can't find the file in 'controllers'!
		self::_404("The page doesn't exists");
		return false;
	}

	/**
	 * 404
	 *
	 * @return string
	 */
	public static function _404($msg)
	{
		$_404 = new Template('404');
		$_404->_404msg = $msg;
		$_404->renderModule();
		return '';
	}
	
	/**
	 * 
	 * @return boolean
	 */
	private function root()
	{
		//['HTTP_HOST'] => 189.137.75.112
		//['HTTP_REFERER'] => http://189.137.75.112/stabilizat.com/
		
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' ;
		
		// exact coincidence
		foreach(self::$SITEROOT as $siteroot)
		{
			if($siteroot == $_SERVER['HTTP_HOST'])
			{
				self::$SITEROOT = $protocol . $siteroot;
				return true;
			}
		}
		
		// near coincidence 
		foreach(self::$SITEROOT as $siteroot)
		{
			if(strstr($siteroot, $_SERVER['HTTP_HOST']))
			{
				self::$SITEROOT = $protocol . $siteroot;
				return true;
			}
		}
				
		if(empty(self::$SITEROOT) || is_array(self::$SITEROOT) || self::$SITEROOT == '')
		{
			die('El sistema no tiene ruta de acceso asignado');
		}
		
		return false;	
	}
	
	/**
	 * 
	 * @return string
	 */
	public static function script( $just_route = false )
	{
		if($just_route)
		{
			return self::$route;
		}
		return self::$route. '/' . self::$hook;
	}
}