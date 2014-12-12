<?php

namespace iframework;

/**
 * This file handles the retrieval and serving of news articles
 */
class Template
{
	
	use traits\Registry;

	private $modules;

	/**
	 * This template variable will hold the 'view' portion of our MVC for this
	 * controller
	 *
	 * April 3, 2014: there's no need to let the master view running wild over the fences,
	 * has better chances with the module container.
	 */
	private $view;

	private $_template;

	protected $template;

	/**
	 * Now, there you are little fella!
	 * This one is the most important.
	 * It holds the master view instance with all the other properties.
	 */
	public $module;
	
	public $section = array();
	
	public $_404msg = '404';
	
	private $css;
	
	private $js;
	
	private $nav_module;

	function __construct($template = null, $ajax = false)
	{
		$this->modules = \iframework\Router::$MODULES;
		
		if (is_string($template))
		{
			$this->_template = $template;
		}
		
		$this->_Registry = new \StdClass();
		$this->module = new \StdClass();
		$this->_init($ajax);
	}

	/**
	 * This is the default function that will be called by the router
	 *
	 * @param array $getVars
	 *        	the GET variables posted to index.php
	 */
	protected function _init($ajax = false)
	{
		try 
		{
			$this->view = new \iframework\View('master');
		}
		catch (\Exception $e)
		{
			die($e);
		}
		
		$this->setGlobalVars($this->view);
		
		if (!$ajax)
		{
			// Default CSS form template name
			// It can be overriden or extended before render
			$this->setCSS('normalize', 'general', $this->_template, 'print'); 
			$this->setJS('modernizr', 'underscore', 'jquery', 'general', $this->_template);
			
			$header = $this->setSection('header', true);
			$footer = $this->setSection('footer', true);
		}
		
		$this->setModule($this->_template, true);
	}

	/**
	 * List of global data
	 *
	 * @param Object|View $section        	
	 */
	protected function setGlobalVars($section)
	{
		$root = \iframework\Router::$SITEROOT . '/';
		$template = \iframework\Router::$TEMPLATE;
		
		$section->assign('mainurl:global', $root );
		$section->assign('currenturl:global', $root . \iframework\Router::script());
		$section->assign('currenturlnoscript:global', $root . \iframework\Router::script(true));
		$section->assign('templateurl:global', $root . \iframework\Router::$APP . '/views/' . $template );
		$section->assign('templatename:global', $this->_template);
		$section->assign('title:global', $template);
	}

	/**
	 * Create a section in the View instance
	 *
	 * @param String $section
	 *        	Name of the section
	 * @param Bool $inherit
	 *        	Inherit global vars from master view. Default: false
	 */
	public function setSection($section, $inherit = false)
	{
		try
		{
			$new_section = new \iframework\View(\iframework\Router::$SECTIONS . '/' . $section);
		}
		catch (\Exception $e)
		{
			die($e);
		}
		
		if ($inherit)
			$new_section->inherit($this->view);
		
		// Don't assign to 'content' section
		// wait for it...
		// ...
		// Oh! ok now, save it for the render.
		$this->section[] = $new_section;
		
		return $new_section;
	}

	/**
	 * 
	 * @param string $module
	 * @param string $inherit
	 */
	public function setModule($module = '', $inherit = false)
	{
		try
		{
			$new_module = new \iframework\View($this->modules . '/' . $module);
		}
		catch (\Exception $e)
		{
			die($e);
		}
		
		if ($inherit)
			$new_module->inherit($this->view);
		
		// The same, wait for it...
		$this->module = $new_module;
		
		return $new_module;
	}

	/**
	 * Renders module and template view
	 */
	public function renderModule()
	{
		// if module missing
		if($this->_404msg != '404')
		{
			$this->view->assign('title:global', '404');
			$this->module->assign('msg:global', $this->_404msg);
		}
		
		// call externals
		$this->view->assign('css:global', $this->css);
		$this->view->assign('js:global', $this->js);
		
		// get all sections
		foreach($this->section as $section)
		{
			$this->view->assign($section->name . ':section', $section->render(FALSE));
		}
		
		// get modules
		$this->view->assign('content:module', $this->module->render(FALSE));
		
		//output ob
		$this->view->render();
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function setCSS()
	{
		$root = \iframework\Router::$SRVRROOT;
		$site = \iframework\Router::$SITEROOT . '/'; 
		$app = \iframework\Router::$APP;
		$template = \iframework\Router::$TEMPLATE;
		
		$this->css = '';
		$args = func_get_args();
		
		foreach($args as $css)
		{
			if(file_exists($root . '/' . $app . '/view/' . $template . '/public/css/' . strtolower($css) . '.css'))
			{
				$print = strtolower($css) == 'print' ? 'media="print"': '';
				$css = $site . $app . '/view/' . $template . '/public/css/' . strtolower($css) . '.css';
				$this->css .= "\t<link rel='stylesheet' type='text/css' href='{$css}' {$print} />\n";
			}	
		}

		return false;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function setJS()
	{
		$root = \iframework\Router::$SRVRROOT;
		$site = \iframework\Router::$SITEROOT . '/';
		$app = \iframework\Router::$APP;
		$template = \iframework\Router::$TEMPLATE;
		
		$this->js = '';
		$args = func_get_args();
		foreach($args as $js)
		{
			if(file_exists($root . '/' . $app . '/view/' . $template . '/public/js/' . strtolower($js) . '.js'))
			{
				$js = $site . $app . '/view/' . $template . '/public/js/' . strtolower($js) . '.js';
				$this->js .= "\t<script type='text/javascript' src='{$js}'></script>\n";
			}
		}
		
		return false;
	}

}