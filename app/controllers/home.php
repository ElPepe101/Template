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

		print_r($getVars);

		session_start('user');
		
		\Micro\Cookie::$settings = array(
			'expires' => '',
			'path' => '',
			'domain' => '',
			'secure' => '',
			'httponly' => ''
		);
		
		\Micro\Session::save('user');
		\Micro\Session::start('user');
		
		print_r($_COOKIE);
		print_r($_SESSION);
		
		@\Micro\Session::destroy('user');
		

		$this->algo = 'algodón';
		
		/*
		echo '<br />1 algo: '.$this->algo->value();
		echo '<br />2 is_string: '.$this->algo->is_string()->value();
		echo '<br />3 is_string: '.$this->algo->is_string()->value();
		echo '<br />4 is_string: '.$this->algo->is_string()->value();
		echo '<br />5 is_string: '.$this->algo->is_string()->value();
		echo '<br />6 is_string: '.$this->algo->is_string()->value();
		echo '<br />7 sha1: '.$this->algo->sha1()->value();
		echo '<br />8 sha1: '.$this->algo->sha1()->value();
		echo '<br />9 sha1,strpos: '.$this->algo->sha1()->strpos('afbf5941')->value();
		echo '<br />10 sha1,sha1: '.$this->algo->sha1()->sha1()->value();
		echo '<br />11 sha1,sha1: '.$this->algo->sha1()->sha1()->value();
		echo '<br />12 sha1,strpos: '.$this->algo->sha1()->strpos('afbf5941')->value();
		echo '<br />13 sha1: '.$this->algo->sha1()->value();
		echo '<br />14 sha1: '.$this->algo->sha1()->value();
		echo '<br />15 sha1: '.$this->algo->sha1()->value();
		//echo 'sha1,strpos: '.$this->algo->sha1()->strpos('afbf5941')->value();
		//echo 'sha1,strpos: '.$this->algo->sha1()->strpos('afbf5941')->value();
		//*/

		/*
		if($this->algo->is_string()->value()){
			echo 'is string: '. $this->algo->value().'<br />';
		}

		if($this->algo->sha1()->is_sha1()){
			echo 'is sha: '.$this->algo->sha1()->value().'<br />';
		}

		print_r($this->algo->sha1());

		echo 'strpos: '.$this->algo->sha1()->strpos('afbf5941')->value().'<br />';
		//*/
		
		$this->module->assign('title:global', 'algo');
		$this->renderModule();

	}
}