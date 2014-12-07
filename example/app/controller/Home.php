<?php

/** 
 * This file handles the retrieval and serving of news articles
 */
class Home extends PPMFWK\Template
{

	/**
	 * This is the default function that will be called by the router
	 *
	 * @param array $getVars
	 *        	the GET variables posted to index.php
	 */
	public function main(array $getVars)
	{
		
		// $this->load_database();
		$this->algo = 'algodón';
		
		print_r($_SESSION);
		
		$this->module->assign('title:global', 'Inicio general');
		$this->renderModule();
	}
	
	public function recepcion(array $getVars)
	{
		// OVERRIDE MODULE
		$this->setModule('recepcion');
		$this->module->assign('title:global', 'Recepción');
		$this->renderModule();
	}
}