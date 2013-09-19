<?php 

/**
 * ObjectApply
 * 
 * 
 * @package    Custom MVC
 * @subpackage Library
 * @author     ElPepe Segoviano <aunsoyjoven {at} hotmail.com>
 */
class ObjectApply_Library {
	
	private $_value;

	public function __construct($value = null) {
		$this->_value = $value;
	}
	
	public function value() {
		return $this->_value;
	}

	/**
	 * Check if provided string is a SHA1 hash
	 */
	public function is_sha1() {
		return ( bool ) preg_match( '/^[0-9a-f]{40}$/i', $this->_value );
	}

	/**
	 * Apply PHP functions to object
	 */
	public function __call($name, $arguments = array()) {
		if(strpos($name,'is_')!== false){
			return $name($this->_value);
		} elseif (strpos($name,'to_')!== false){
			$name = str_replace('to_', '', $name);
			if(empty($arguments)) {
				return $name($this->_value);
			} else {
				return call_user_func_array($name, $arguments);
			}
			
			
		} else {
			$tot = count($arguments);
			$arguments[] = $this->_value;
			
			// The arguments position vary with syntax
			// Pretty bad syntax if you ask me
			// Need to list each case or use Reflection Class
			switch($name) {
				case 'strstr':
				case 'strpos':
					$args = array_reverse($arguments);
				break;
				default:
					$args = $arguments;
			}
			return call_user_func_array($name, $args);
		}
	}

	

}