<?php

namespace iframework\lib;

/** 
 * @author ElPepe
 *
 * Session control library.
 * Currently implementing RedBeanPHP Syntax.
 * 
 * For it's use, first you'll need a model "\iframework\lib\Session::$model = new User();",
 * then you will call it's singleton "$_sess = \iframework\lib\Session::getInstance();",
 * later you'll verify the session "$_sess->verify();".
 * 
 * This library is ment to use before any router
 * 
 * If you want to extract the navigation assigned to this model: "$_sess->navigation();" 
 * 
 * The minimun requirements for the model are:
 * 
 *  - USER ID: 				$_SESSION['id'] = $usr->id;
 *	- PROFILE NAME:			$_SESSION['profile'] = $usr->role->name;
 *	- FIRST MODULE SLUG: 	$_SESSION['default'] = $usr->role->sharedModuleList[1]->slug;
 *  - ALLOWED MODULES:		$_SESSION['modules'] = $this->modules;
 * 
 * 
 * @uses Singleton Trait: \iframework\traits\Singleton
 * 
 */
class Session
{

	use \iframework\traits\Singleton;
	
	/**
	 * 
	 * @var Model | RedBean Instance
	 */
	public static $model;
	
	/**
	 * 
	 * @var RedBean Object
	 */
	private $modules;
	
	/**
	 * 
	 * @var string | HTML
	 */
	private $nav_module = '';

	/**
	 * 
	 * Singleton construct
	 */
	protected function construct()
	{
		if(isset($_GET['logout']) && $_GET['logout'])
		{
			$this->logout();
			exit();
		}
	}
	
	/**
	 * Will check the module in the database for its use with the session
	 *
	 */
	public function verify()
	{
		// Home will never be secure
		// it may be a login page
		if(\iframework\Router::isHome())
			return;
			
		\iframework\lib\Micro\Session::start();
		
		// IF USER SENDS LOGIN DATA
		if (isset($_POST['login'], $_POST['pass'], $_POST['token']))
			$this->init();
		
		// IF SESSION EXISTS: ONLY CHECK MODULE ACCESS
		elseif (\iframework\lib\Micro\Session::token((string) $_SESSION['token']))
			$this->update(); // Prevent session fixation. EXTREMELY IMPORTANT!

		// Cache the modules
		try
		{
			$this->modules();
		}
		catch (\Exception $e)
		{
			// BAD LOGIN
			$this->end();
			$this->logout($e);
		}
		
		// if not allowed to the module,
		// but it's a certified user
		// take it back to where belongs
		if( ! $this->access())
		{
			header('location: ' . \iframework\Router::$SITEROOT . '/' . $_SESSION['default']);
			exit();
		}
		
		return;
	}

	/**
	 * This will start the sessions,
	 * Warning: heavy use of DB conn.
	 * 
	 * @return void
	 */
	private function init()
	{
		// Clean session
		// this will prevent some session hijack
		$this->end();
		
		$login = (string) $_POST['login'];
		
		// Ready to use
		self::$model->start();
		
		// GET SALT FROM DB
		$salt = self::$model->find('login = ?', [ $login ]);
		
		// GET USER CREDENTIALS
		$usr = self::$model->find('login = ? AND pass = ?', [ $login, hash("whirlpool", (string) $_POST['pass'] . $salt->_salt) ]);
		
		// IF USER CREDENTIALS
		// here we set the session vars and set Micro Session
		if (isset($usr->login))
		{
			$this->populate($usr);
			/*, array(
				'fname',
				'lname1'
			));*/

			// Save cookies
			$this->send();
			
			// If default hook on profile
			if(!empty(explode('/', $_SESSION['default'])[1]))
			{
				header('location: '. self::$SITEROOT . $_SESSION['default']);
				exit();
			}
		}
		
		return;
	}

	/**
	 * Save user session to use on cookies
	 * 
	 */
	public function send()
	{
		\iframework\lib\Micro\Session::save();
		return;
	}

	/**
	 * End Session
	 * 
	 */
	public function end()
	{
		\iframework\lib\Micro\Session::destroy();
		unset($_COOKIE['PHPSESSID']);
		setcookie("PHPSESSID", "", time() - 3600, "/");
		return;
	}

	/**
	 * Refresh cookie token and session
	 * 
	 */
	public function update()
	{	
		// IF USER CREDENTIALS
		// here we set the session vars and set Micro Session
		if (isset($usr))
		{
			$this->populate($usr[0], array(
				'usr_fname',
				'usr_lname1'
			));
		}
		
		// GENERATE NEW TOKEN
		\iframework\lib\Micro\Session::token();
		
		// Save cookies
		$this->send();
		return;
	}
	
	/**
	 * This will setup the data array 
	 * of the user for it's use.
	 * 
	 * Currently unused.
	 * 
	 * @todo remove or implement
	 * @return Ambigous <string, NULL>|boolean
	 */
	public function user()
	{
		// IF THE USER IS MAKING A NEW LOGIN
		if(isset($_POST['login'], $_POST['pass']))
		{
			// GET SALT
			$salt = array(
				'usr_login' => (string) $_POST['login']
			);
				
			// GET USER CREDENTIALS
			$usr =array(
				'usr_login' => (string) $_POST['login'],
				'usr_pass' => hash("whirlpool", (string) $_POST['pass'] . $salt)
			);
			
			return $usr[0];
		}
		
		return false;
	}

	/**
	 * Create $_SESSION vars from usr table
	 * 
	 * @param unknown $usr
	 * @param array $usr_data
	 * @param string $token
	 */
	private function populate($usr, array $usr_data = array(), $token = NULL)
	{
		$token = ! is_null($token) ? $token : (string) $_POST['token'];
		
		$_SESSION['token'] = $token;
		$_SESSION['id'] = $usr->id;
		$_SESSION['profile'] = $usr->role->name;
		$_SESSION['default'] = $usr->role->sharedModuleList[1]->slug . '/';// . $usr->role->default_hook;

		foreach ($usr_data as $data)
		{
			$_SESSION[$data] = $usr->usr_ext->$data;
		}
		
		return;
	}

	/**
	 * Check assigned modules to profile
	 * 
	 * @TODO assign Actions (Hooks) to every module in DB and check for permission
	 * 
	 * @param Object $usr
	 * @return boolean
	 */
	private function access()
	{
		foreach ($this->modules as $access)
		{
			if ($access->slug == \iframework\Router::route())
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get all modules of given user or session user
	 * 
	 * @param Object $usr
	 * 
	 * @return void
	 */
	private function modules($usr = NULL)
	{
		if( !isset($_SESSION['id']))
			throw new \Exception('No active session.');
		
		// No need to change it
		if( isset($_SESSION['modules']) && $_SESSION['modules'] == $this->modules)
			return;
		
		// Ready to use
		self::$model->start();
		
		if( !!! $usr)
			$usr = self::$model->load([ $_SESSION['id'] ], 'load');
		
		$this->modules = $usr->role->sharedModuleList;
		
		// Cache for performance
		$_SESSION['modules'] = $this->modules;
		
		return;
	}

	/**
	 * This will create an <ul> HTML element
	 * based in the session modules.
	 * 
	 * @return string
	 */
	public function navigation()
	{
		if( !isset($_SESSION['id']) ) 
			return $this->nav_module;
		
		$current = \iframework\Router::$SITEROOT . '/' . \iframework\Router::script(true);
		$this->nav_module = "\t<ul>\n";
		
		foreach($this->modules as $access)
		{
			$href = \iframework\Router::$SITEROOT . '/' . $access->slug;
			$active = $href == $current ? "class='active'" : '';
			$this->nav_module .= "\t<li><a {$active} href='{$href}'>{$access->name}</a></li>\n";
		}
		
		$this->nav_module .= "\t<li><a href='{$current}/?logout=true'>Cerrar Sessión</a></li></li>\n";
		$this->nav_module .= "\t</ul>\n";
		
		return $this->nav_module;
	}

	/**
	 * Hook /?logout=true
	 * 
	 * The param is for queue the next page to send instead the default.
	 * 
	 * @todo Implement $return var for redirection in router
	 * @param string $return
	 */
	public function logout($return = NULL)
	{
		$return = is_null($return) ?  : '&return=' . $return;
		$this->end();
		header('location: ' . \iframework\Router::$SITEROOT);
	}
}