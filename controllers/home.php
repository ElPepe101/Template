<?php

/**
 * This file handles the retrieval and serving of news articles
 */
class Home_Controller extends Template_Controller {

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function main(array $getVars) {
		
		$home = new View_Model($this->template);
		$home->assign('title' , 'algo');
		
		$this->view->assign('content' , $home->render(FALSE));
		
		$this->algo = 'asdasdafsav29019hr0';

		if($this->algo->is_string()){
			echo 'is string: '. $this->algo->value().'<br />';
		}

		if($this->algo->to_sha1()){
			echo 'is sha: '.$this->algo->to_sha1().'<br />';
		}

		echo $this->algo->strpos('sdas').'<br />';
		
		$this->view->render();
		
		
		
	}
}