<?php

namespace iframework\lib;

/**
 * 
 * @author ElPepe
 *
 * @todo Commenting and Documenting
 * 
 */
class Session
{

	public $model;
	
	private $modules = array();

	public function __construct($model)
	{
		if(isset($_GET['logout']) && $_GET['logout'])
		{
			$this->logout();
			exit();
		}
		// Load database
		$this->model = $model;
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
	 * @return void
	 */
	private function init()
	{
		// Clean session
		// this will prevent some session hijack
		$this->end();
		
		$login = (string) $_POST['login'];
		
		// GET SALT FROM DB
		$salt = $this->model->find('login = ?', [ $login ]);
		
		// GET USER CREDENTIALS
		$usr = $this->model->find('login = ? AND pass = ?', [ $login, hash("whirlpool", (string) $_POST['pass'] . $salt->_salt) ]);
		
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
	 */
	public function send()
	{
		\iframework\lib\Micro\Session::save();
		return;
	}

	/**
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
	 * 
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
	public function access()
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
	 */
	public function modules($usr = NULL)
	{
		if( !isset($_SESSION['id']))
			throw new \Exception('No active session.');
		
		if( !!! $usr)
		{
			$usr = $this->model->load([ $_SESSION['id'] ], 'load');
		}
		
		$this->modules = $usr->role->sharedModuleList;
		return;
	}

	/**
	 *
	 * @param Object $usr
	 
	public function setNavModule($usr = NULL)
	{
		$this->nav_module = "\t<ul>\n";
	
		// Modules can't be overriden or extended
		// this will depend on a well stablishd DB
		// If using DB mode framework
		if(\iframework\Router::$LOGIN)
		{
		$this->setNavModule();
		$this->view->assign('nav_module:global', $this->nav_module);
		}
	
		$modules = $this->getModules($usr);
	
		foreach($modules as $access)
		{
		$href = \iframework\Router::$SITEROOT . $access->module->slug;
		$this->nav_module .= "\t<li><a href='{$href}'>{$access->module->name}</a></li>\n";
		}
	
		$current = \iframework\Router::$SITEROOT . \iframework\Router::script(true);
		$this->nav_module .= "\t<li><a href='{$current}/logout'>Cerrar Sessión</a></li></li>\n";
	
		$this->nav_module .= "\t</ul>\n";
	}*/

	/**
	 * Hook /?logout=true
	 * 
	 * @param string $return
	 */
	public function logout($return = NULL)
	{
		$return = is_null($return) ?  : '&return=' . $return;
		$this->end();
		header('location: ' . \iframework\Router::$SITEROOT);
	}
}
// End Trait