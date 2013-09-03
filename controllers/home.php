<?php

/**
 * This file handles the retrieval and serving of news articles
 */
class Home_Controller extends Template_Controller {


	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	public $template = 'home';	

	function __construct() {
		$this->_init();
		$this->_template .= $this->template;
		$this->model = new Home_Model;
	}

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function main(array $getVars) {
		
		//get an article
		$article = $this->model->get_article($getVars['author']);
		
		$home = new View_Model($this->_template);
		$home->assign('title' , $article->title);
		
		$this->view->assign('content' , $home->render(FALSE));
		$this->view->render();
		
	}
}