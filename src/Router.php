<?php

namespace iframework;

/**
 * PPMFWK
 *
 * @package iframework
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
	
	public static $SESSION = NULL;
	
	public static $config = array();
	
	public static $navigation = '';

	private static $router = array();
	
	private static $route = '';

	private static $hook = '';
	
	private static $GET = array();
	
	/**
	 *
	 * @param unknown $login        	
	 */
	public static function construct($session = false)
	{
		ini_set('error_reporting', E_ALL);

		if (self::$DEBUG)
			ini_set('display_errors', 1);
		else
			ini_set('display_errors', 0);
		
		//
		self::root();
		
		// ////////////////////////////////////
		// SETTING TIMEZONE
		date_default_timezone_set(self::$TIMEZONE);
		
		// MYSQL SERVER TIME: Mexico/General -2 hours
		self::$HOUR = date('Y-m-d-H', strtotime('-2 hours'));
		
		// PREPARE THE ROUTE;
		try
		{
			self::$router = self::path($_SERVER['QUERY_STRING']);
			self::$route = strtolower(self::$router[1]);
		}
		catch (\Exception $e)
		{
			// Show the complete error
			if (self::$DEBUG)
				echo $e;
			
			self::_404('The page you are looking for does not exists.');
			return;
		}
	}

	/**
	 * 
	 * fetch the passed request
	 * 
	 * @param unknown $request
	 * @return multitype:|multitype:string mixed
	 */
	public static function path($request)
	{
		// parse the page request and other GET variables
		$parsed = explode('/', (string) $request);
		
		// the page is the first element
		$page = array_shift($parsed);
		$page = $page == '' ? 'Home' : preg_replace('/\s|\x2d/', '_', $page);
		
		// compute the path to the file
		$page = str_replace(' ', '_', ucwords(str_replace('_', ' ', $page)));
		$target = self::$SRVRROOT . '/' . self::$APP . '/controller/' . $page . '.php';
		
		if ( ! file_exists($target))
			throw new \Exception("The file '{$request}' doesn't exists for the target '{$target}'. \n");
		
		// the first of the array is the Action (Hook)
		// IF using sessions use default, then main.
		self::$hook = isset($parsed[0]) && ! empty($parsed[0]) ? array_shift($parsed) : 'main';
		
		// the rest of the array are get statements, parse them out.
		foreach ($parsed as $argument)
		{
			if (strpos($argument, '=') === FALSE) $argument .= '=';
			
			// split GET vars along '=' symbol to separate variable, values
			list ($variable, $value) = explode('=', $argument);
			self::$GET[$variable] = urldecode($value);
		}
		
		// get target
		return array(
			$target,
			$page
		);
	}

	/**
	 * 
	 * @param array $router
	 * @return unknown|boolean
	 */
	public static function start()
	{
		list ($file, $page) = self::$router;
		
		// fetch file
		include_once ($file);
		
		// instantiate the appropriate class
		if (class_exists($page))
		{
			// instantiate the controller
			$controller = new $page($page, isset(self::$GET['ajax'])? true: false );
			
			// For security reasons, obtain only controller
			// methods without implements or extents
			// http://stackoverflow.com/questions/1960365/php-get-class-methods-problem
			$controller_methods = array_diff(get_class_methods($controller), get_class_methods(get_parent_class($controller)));

			// If using logout function from another instance
			// Eg.: PPMFWK\MicroHandler Trait
			// $traits = class_uses(get_parent_class($controller));
			! method_exists($controller, 'logout') && ! in_array('logout', $controller_methods) ?  : array_push($controller_methods, 'logout');
			
			// Execute the method
			if (in_array(self::$hook, $controller_methods))
			{
				// pass any GET varaibles to the main method
				$controller->{self::$hook}(self::$GET);
			}
			// There's no hook to call
			else
			{
				self::_404('The action is not defined');
			}
			
			return $page;
		}
		
		// can't find the file in 'controllers'!
		if (self::$DEBUG)
			echo "The Class '{$page}' is not defined in '{$file}'. Please elaborate. \n";
				
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
		$_404 = new \iframework\Template('404');
		$_404->_404msg = $msg;
		$_404->renderModule();
		exit();
	}
	
	/**
	 * 
	 * @return boolean
	 */
	private static function root()
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
	
	// ////////////////////////////////////
	// ////////////////////////////////////
	// READ ONLY FUNCTIONS
	
	/**
	 *
	 * @return string
	 */
	public static function script( $just_route = false )
	{
		if($just_route)
			return self::$route;
	
		return self::$route. '/' . self::$hook;
	}
	
	/**
	 * 
	 * @return multitype:
	 */
	public static function params()
	{
		return self::$GET;
	}
	
	/**
	 * 
	 * @return string
	 */
	public static function route()
	{
		return self::$route;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public static function isHome()
	{
		return self::$route == 'home';
	}
}