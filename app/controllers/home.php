<?php

/** 
 * This file handles the retrieval and serving of news articles
 */
class Home extends PPMFWK\Template {

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function main(array $getVars) {

		$this->initialize('user');
		
		$this->load_database();

		print_r($getVars);
		print_r($_COOKIE);
		print_r($_SESSION);

		$this->algo = 'algodón';
		
		$this->module->assign('title:global', 'algo');
		$this->renderModule();

	}
}