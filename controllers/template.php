<?php

/**
 * This file handles the retrieval and serving of news articles
 */
class Template_Controller {

	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	protected $_template = 'sections/';

	protected $view;
	
	protected $model;

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	protected function _init() {
    
		$header = new View_Model('modules/header');
		$footer = new View_Model('modules/footer');
		$this->view = new View_Model('master');
		
		$this->view->assign('header', $header->render(FALSE));
		$this->view->assign('footer', $footer->render(FALSE));
		
	}
	
}