<?php

/**
 * Handles the view functionality of our MVC framework
 */
class View_Library {

	/**
	 * Holds variables assigned to template
	 */
	private $data;

	/**
	 * Holds render status of view.
	 */
	private $render = FALSE;

	// Just for debbug
	private $template;

	/**
	 * Accept a template to load
	 */
	public function __construct($template) {
	
		//compose file name
		$file = SRVRROOT . '/app/views/'. TEMPLATE . '/' . strtolower($template) . '.php';
		//echo $file.'<br />';
		
		// for debbug
		$this->template = $template;

		$this->data = new stdClass();

		if (file_exists($file)) {
			/*
			 * trigger render to include file when this model is destroyed
			 * if we render it now, we wouldn't be able to assign variables
			 * to the view!
			 */
			$this->render = $file;
		}
	}
	
	/**
	* Refine the type of data
	* 
	* @param string $type 
	*/
	private function getData($type) {
		// $this->data				- is an object container
		// get_object_vars			- REFLECTION object to array
		// array_keys				- to inspect the related type keys
		// preg_grep				- will obtain the $type in keys
		// array_flip				- will put the filtered values like keys
		// array_intersect_key		- will filter matched keys
		// Lastly Type Casting is the key to return it like an object
		
		return (Object) array_intersect_key(get_object_vars($this->data), array_flip(preg_grep('/\:'.$type.'/', array_keys(get_object_vars($this->data)))));
	}

	/**
	 * Receives assignments from controller and stores in local data array
	 *
	 * @param String $variable
	 * @param String $value
	 */
	public function assign($variable , $value) {

		//echo $this->template.' - '.$variable.'<br />';
		$this->data->{$variable} = $value;
	}
	
	/**
	 * Inherit data type of another view
	 *
	 * @param Object|View $parent_view
	 * @param String $type
	 */
	public function inherit($parent_view , $type = 'global') {
	
		$this->data = $parent_view->getData($type);
	}
	
	/**
	 * Remove type of object in assigned data
	 *
	 * @param Object $data
	 * @TODO Reduce to one line, use only php functions, prevent use or creation of vars.
	 */
	private function cleanObjProperty($data) {
		
		$clean_data = new stdClass();
		
		foreach($data as $k => $d) {
			
			$clean_data->{preg_replace('/:(.*)/', '', $k)} = $d;	
		}
		
		return $clean_data;
	}
	
	/**
	 * Render the output directly to the page, or optionally, return the
	 * generated output to caller.
	 * 
	 * @param $direct_output Set to any non-TRUE value to have the 
	 * output returned rather than displayed directly.
	 */
	public function render($direct_output = TRUE) {

		// Turn output buffering on, capturing all output
		if ($direct_output !== TRUE) {
		
			ob_start();
		}

		// Parse data variables into local variables
		// Remove data type
		$data = $this->cleanObjProperty($this->data);
		
		//print_r($data);

		// Get template
		if(file_exists($this->render)) {
		
			include($this->render);	
		} else {
		
			echo '404';
		}

		// Get the contents of the buffer and return it
		if ($direct_output !== TRUE) {
		
			return ob_get_clean();
		}
	}
	
	public function __destruct() {}
} 