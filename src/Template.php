<?php

namespace PPMFWK;

/**
 * This file handles the retrieval and serving of news articles
 */
class Template
{
	
	use MicroHandler;
	
	use Registry;

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
		$this->modules = PPMFWK::$MODULES;
		
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
		$this->view = new View('master');
		$this->setGlobalVars($this->view);
		
		if (!$ajax)
		{
			// Default CSS form template name
			// It can be overriden or extended before render
			$this->setCSS('normalize', 'general', $this->_template, 'print'); 
			$this->setJS('modernizr', 'underscore', 'jquery', 'general', $this->_template);
			
			// Modules can't be overriden or extended
			// this will depend on a well stablishd DB
			// If using DB mode framework
			if(PPMFWK::$LOGIN)
			{
				$this->setNavModule();
				$this->view->assign('nav_module:global', $this->nav_module);
			}
			
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
		$section->assign('mainurl:global', PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() );
		$section->assign('currenturl:global', PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . PPMFWK::getCurrentScript());
		$section->assign('currenturlnoscript:global', PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . PPMFWK::getCurrentScript(true));
		$section->assign('templateurl:global', PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . PPMFWK::$APP . '/views/' . PPMFWK::$TEMPLATE );
		$section->assign('templatename:global', $this->_template);
		$section->assign('title:global', PPMFWK::$TEMPLATE);
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
		$new_section = new View(PPMFWK::$SECTIONS . '/' . $section);
		
		if ($inherit)
		{
			$new_section->inherit($this->view);
		}
		
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
		$this->template = $this->modules . '/' . $module;
		$new_module = new View($this->template);
		
		if ($inherit)
		{
			$new_module->inherit($this->view);
		}
		
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
		$this->css = '';
		$args = func_get_args();
		foreach($args as $css)
		{
			if(file_exists(PPMFWK::$SRVRROOT . '/' . PPMFWK::$APP . '/views/' . PPMFWK::$TEMPLATE . '/public/css/' . strtolower($css) . '.css'))
			{
				$print = strtolower($css) == 'print' ? 'media="print"': '';
				$css = PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . PPMFWK::$APP . '/views/' . PPMFWK::$TEMPLATE . '/public/css/' . strtolower($css) . '.css';
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
		$this->js = '';
		$args = func_get_args();
		foreach($args as $js)
		{
			if(file_exists(PPMFWK::$SRVRROOT . '/' . PPMFWK::$APP . '/views/' . PPMFWK::$TEMPLATE . '/public/js/' . strtolower($js) . '.js'))
			{
				$js = PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . PPMFWK::$APP . '/views/' . PPMFWK::$TEMPLATE . '/public/js/' . strtolower($js) . '.js';
				$this->js .= "\t<script type='text/javascript' src='{$js}'></script>\n";
			}
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param Object $usr
	 */
	public function setNavModule($usr = NULL)
	{
		$this->nav_module = "\t<ul>\n";
		$modules = $this->getModules($usr);
		
		foreach($modules as $access)
		{
			$href = PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . $access->module->slug;
			$this->nav_module .= "\t<li><a href='{$href}'>{$access->module->name}</a></li>\n";
		}
		
		$current = PPMFWK::$SITEROOT . PPMFWK::getRealSafeArea() . PPMFWK::getCurrentScript(true);
		$this->nav_module .= "\t<li><a href='{$current}/logout'>Cerrar Sessión</a></li></li>\n";
		
		$this->nav_module .= "\t</ul>\n";
	}
}