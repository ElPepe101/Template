<?php

namespace PPMFWK;

/**
 * This file handles the retrieval and serving of news articles
 */
class Template {

	private $modules;
	 
	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 * 
	 * April 3, 2014: there's no need to let the master view running wild over the fences,
	 * has better chances with the module container.
	 */	
	private $view;
	
	private $_Registry;

	private $_template;
	
	protected $template;
	
	/**
	 * Now, there you are little fella.
	 * This one is the important.
	 * It holds the master view instance with all the other properties.
	 */
	public $module;
	
	function __construct($template = null) {

		$this->modules = PPMFWK::$MODULES;

		if(is_string($template)){

			$this->_template = $template;
		}

		$this->_Registry = new \StdClass();
		$this->module = new \StdClass();
		$this->_init();
	}

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	protected function _init($normal_template = true) {
		
		$this->view = new View('master');
		$this->setGlobalVars($this->view);
		
		if($normal_template) {
		
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
	protected function setGlobalVars($section) {
	
		$section->assign('mainurl:global' , PPMFWK::$SITEROOT);
		$section->assign('templateurl:global' , PPMFWK::$SITEROOT.'/app/views/'.PPMFWK::$TEMPLATE);
		$section->assign('templatename:global', $this->_template);
		$section->assign('title:global' , PPMFWK::$TEMPLATE);
	}
	
	/**
	 * Create a section in the View instance
	 *
	 * @param String $section Name of the section
	 * @param Bool $inherit Inherit global vars from master view. Default: false
	 */
	public function setSection($section, $inherit = false) {
	
		$new_section = new View(PPMFWK::$SECTIONS.'/'.$section);
		
		if($inherit){
			$new_section->inherit($this->view);
		}
		
		$this->view->assign($section.':section', $new_section->render(FALSE));
		
		return $new_section;
	}
	
	/**
	 * 
	 */
	public function setModule($module, $inherit = false) {

		$this->template = $this->modules.'/'.$this->_template;
		$new_module = new View($this->template);

		if($inherit){
			$new_module->inherit($this->view);
		}
		
		// Don't assign to 'content' section
		// wait for it...
		// ...
		// Ok now, save it for the render.
		$this->module = $new_module;
	}
	
	/**
	 * Renders module and template view
	 *
	 */
	public function renderModule() {

		$this->view->assign('content:module', $this->module->render(FALSE));
		$this->view->render();
	}
	
	
// //////////////////////////////////////////////////	
// //////////////////////////////////////////////////
// THINK ILL BE BETTER TO USE TRAITS FROM THIS PART	ON


	/**
	 * Obtain Controller created vars
	 *
	 */
	public function __get($varName) {
	
		return $this->_Registry->$varName;
	}

	/**
	 * Multiple Chained Actions (Ruby style)
	 *
	 * This is where the magic happens:
	 * 	the idea is to create a SOLID convention param 
	 * 	with an instance of the ObjectApply Library,
	 *  save it to the Controller registry and make use 
	 * 	of singleton to generate a global-in-local history
	 * 	for control of the qty of requests.
	 *
	 * 	All vars in Controller must use the same principle.
	 *
	 * @usage $this->new_var = 'some string';
	 */
	public function __set($varName, $value) {
	
		$this->_Registry->$varName = new libraries\ObjectApply($value);
	}

}