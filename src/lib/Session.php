<?php

/**
 * MyController | Common
 *
 * Basic DEMO outline for standard controllers
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace iframework\lib;

//trait MicroHandler

class Session
{
	
	// Global view template
	// public $template = 'Layout';
	public $db;

	public function __construct()
	{
		$this->db = $this->database();
	}
	
	/**
	 * Will check the module in the database for its use with the session
	 *
	 * @param string $method
	 *        	name
	 */
	public function verify($module)
	{
		\lib\Micro\Session::start();
		
		// IF SESSION EXISTS: ONLY CHECK MODULE ACCESS
		if (\lib\Micro\Session::token((string) $_SESSION['token']))
		{
			// Prevent session fixation
			$this->update(); // EXTREMELY IMPORTANT
				
			$allowed = $this->modules($_SESSION['id']);
				
			// if not allowed to the module,
			// but it's a certified user
			// take it back to where belongs
			if( ! $allowed)
			{
				header('location: ' . iframework\Router::$SITEROOT . $_SESSION['default']);
				exit();
			}
				
			return $allowed;
		}
		// IF USER SENDS LOGIN DATA
		elseif (isset($_POST['login'], $_POST['pass'], $_POST['token']))
		{
			return $this->init();
		}
			
		// IF NOT LOGGED IN AND SEND A BAD LOGIN
		$this->end();
			
		// DO NOT RETURN TRUE, EVER...
		return false;
	}

	/**
	 *
	 * @param string $fail        	
	 */
	public function init()
	{
		// GET SALT FROM DB
		$salt = array( 
			'login' => (string) $_POST['login'] 
		);
		
		// GET USER CREDENTIALS
		$usr = array(
			'login' => (string) $_POST['login'],
			'pass' => hash("whirlpool", (string) $_POST['pass'] . $salt)
		);
		
		// IF USER CREDENTIALS
		// here we set the session vars and set Micro Session
		if (isset($usr[0]))
		{
			$this->populate($usr[0], array(
				'fname',
				'lname1'
			));

			// Save cookies
			$this->send();

			// If default hook on profile
			if(!empty(explode('/', $_SESSION['default'])[1]))
			{
				header('location: '. self::$SITEROOT . $_SESSION['default']);
				exit();
			}

			return $this->modules($usr[0]);
		}
		else
		{
			$this->end();
			return false;
		}
	}

	/**
	 * Load database connection
	 */
	public function database($name = 'database')
	{
		// Load database
		return new Database();
	}

	/**
	 * Save user session to use on cookies
	 */
	public function send()
	{
		\Micro\Session::save();
	}

	/**
	 * 
	 */
	public function end()
	{
		\lib\Micro\Session::destroy();
		unset($_COOKIE['PHPSESSID']);
		setcookie("PHPSESSID", "", time() - 3600, "/");
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
		\lib\Micro\Session::token();
		
		// Save cookies
		$this->send();
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
	public function populate($usr, array $usr_data, $token = NULL)
	{
		$token = ! is_null($token) ? $token : (string) $_POST['token'];
		
		$_SESSION['token'] = $token;
		$_SESSION['id'] = $usr->id_usr;
		$_SESSION['profile'] = $usr->profile->profile;
		$_SESSION['default'] = $usr->profile->access()[0]->module->module_slug.'/'.$usr->profile->default_hook;

		foreach ($usr_data as $data)
		{
			$_SESSION[$data] = $usr->level_one_data->$data;
		}
	}

	/**
	 * Check assigned modules to profile
	 * 
	 * @TODO assign Actions (Hooks) to every module in DB and check for permission
	 * 
	 * @param Object $usr
	 * @return boolean
	 */
	public function module($m)
	{
		foreach ($m as $access)
		{
			// $access->module->id_module;
			if ($access->module->slug == PPMFWK::$router)
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
	public function getModules($usr = NULL)
	{
		if(is_null($usr))
		{
			$usr = models\User::fetch(array('id_usr' => $_SESSION['id']))[0];
		}
		return $usr->profile->access();
	}

	/**
	 * Hook
	 * 
	 * @param string $return
	 */
	public function logout($return = NULL)
	{
		$return = is_null($return) ?  : '&return=' . $return;
		$this->sess_end();
		header('location: ' . PPMFWK::$SITEROOT);
	}
}
// End Trait