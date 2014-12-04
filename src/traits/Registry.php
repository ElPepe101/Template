<?php

namespace iframework\traits;

trait Registry
{

	private $_Registry;
	
	// //////////////////////////////////////////////////
	// //////////////////////////////////////////////////
	// THINK ILL BE BETTER TO USE TRAITS FROM THIS PART ON
	
	/**
	 * Obtain Controller created vars
	 */
	public function __get($varName)
	{
		return $this->_Registry->$varName;
	}

	/**
	 * Multiple Chained Actions (Ruby style)
	 *
	 * This is where the magic happens:
	 * the idea is to create a SOLID convention param
	 * with an instance of the ObjectApply Library,
	 * save it to the Controller registry and make use
	 * of singleton to generate a global-in-local history
	 * for control of the qty of requests.
	 *
	 * All vars in Controller must use the same principle.
	 *
	 * @usage $this->new_var = 'some string';
	 */
	public function __set($varName, $value)
	{
		$this->_Registry->$varName = new \iframework\lib\ObjectApply($value);
	}
}