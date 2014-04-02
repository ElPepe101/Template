<?php

/**
 * This file handles the retrieval and serving of news articles
 */
class Template_Library {

	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	 
	protected $modules = MODULES;
	 
	protected $template;

	protected $view;
	
	protected $model;
	
	private $_Registry;

	protected $_template;
	
	function __construct($template = null) {
		if(is_string($template)){
			$this->_template = $template;
			$this->template = $this->modules.'/'.$this->_template;
		}
		$this->_init();
		//$this->model = new Home_Model;
		
		//Get class name from extended class
		//$instance_name = strtolower(preg_replace('(\_\w*)', '', get_class($this)));
		//$$instance_name = new View_Library($this->template);
		
		$this->_Registry = new StdClass();
	}

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	protected function _init($normal_template = true) {
		
		$this->view = new View_Library('master');
		$this->setGeneralSectionVars($this->view);
		$this->view->assign('templatename', $this->_template);
		
		if($normal_template) {
		
			$header = $this->getSection('header');
			$this->view->assign('header', $header->render(FALSE));
		
			$footer = $this->getSection('footer');
			$this->view->assign('footer', $footer->render(FALSE));
		}
		
		$content = new View_Library($this->template);
		$this->view->assign('content', $content->render(FALSE));
		
		
		$this->view->assign('title' , TEMPLATE);	
	}
	
	protected function setGeneralSectionVars($new_section) {
	
		$new_section->assign('mainurl' , SITEROOT);
		$new_section->assign('templateurl' , SITEROOT.'/views/'.TEMPLATE);
	}
	
	public function getSection($section, $is_general_section = true) {
	
		$new_section = new View_Library(SECTIONS.'/'.$section);
		if($is_general_section) {
			$this->setGeneralSectionVars($new_section);
		}
		
		return $new_section;
	}
	
	public function __get($varName) {
	
		return $this->_Registry->$varName;
	}

	public function __set($varName, $value) {
	
		$this->_Registry->$varName = new ObjectApply_Library($value);
	}

}