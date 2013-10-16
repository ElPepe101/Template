<?php

/**
 * This file handles the retrieval and serving of news articles
 */
class Template_Controller {

	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	 
	protected $modules = MODULES;
	 
	protected $template;

	protected $view;
	
	protected $model;

	protected $_template;

	function __construct($template = null) {
		if(is_string($template)){
			$this->_template = $template;
			$this->template = $this->modules.'/'.$this->_template;
		}
		$this->_init();
		//$this->model = new Home_Model;
	}

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	protected function _init() {
		$header = new View_Model(SECTIONS.'/header');
		$header->assign('mainurl' , SITEROOT);
		$header->assign('templateurl' , SITEROOT.'/views/'.TEMPLATE);
		
		$footer = new View_Model(SECTIONS.'/footer');
		$footer->assign('mainurl' , SITEROOT);
		$footer->assign('templateurl' , SITEROOT.'/views/'.TEMPLATE);
		
		$this->view = new View_Model('master');
		$this->view->assign('mainurl', SITEROOT);
		$this->view->assign('templateurl' , SITEROOT.'/views/'.TEMPLATE);
		$this->view->assign('templatename', $this->_template);
		
		$this->view->assign('header', $header->render(FALSE));
		$this->view->assign('footer', $footer->render(FALSE));
		
	}
	
}