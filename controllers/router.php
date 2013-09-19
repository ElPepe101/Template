<?php

/**
 * This controller routes all incoming requests to the appropriate controller
 */

//Automatically includes files containing classes that are called
function __autoload($className) {
	// Parse out filename where class should be located
	// This supports names like 'Example_Model' as well as 'Example_Two_Model'
	list($suffix, $filename) = preg_split('/_/', strrev($className), 2);
	$filename = strrev($filename);
	$suffix = strrev($suffix);

	//echo strtolower($suffix). ': '. $filename. '<br />';

	//select the folder where class should be located based on suffix
	switch (strtolower($suffix)) {
	case 'model':
		$folder = '/models/';
		break;

	case 'library':
		$folder = '/libraries/';
		break;

	case 'driver':
		$folder = '/libraries/drivers/';
		break;
		
	case 'controller':
		$folder = '/controllers/';
		break;
	}

	//compose file name
	$file = SRVRROOT . $folder . strtolower($filename) . '.php';
	// echo $file.'<br />';

	//fetch file
	if (file_exists($file)) {
		//get file
		include_once($file);
	} else {
		//file does not exist!
		die("File '$filename' containing class '$className' not found in
'$folder'.");
	}
}

//fetch the passed request
$request = $_SERVER['QUERY_STRING'];

//parse the page request and other GET variables
$parsed = explode('&' , $request);

//the page is the first element
$page = array_shift($parsed);

$page = $page == ''? 'home': $page;

//the rest of the array are get statements, parse them out.
$getVars = array();
foreach ($parsed as $argument) {

	if(strpos($argument,'=')===FALSE) {
		$argument .= '=';
	}

	//split GET vars along '=' symbol to separate variable, values
	list($variable , $value) = explode('=' , $argument);
	$getVars[$variable] = urldecode($value);
}

//compute the path to the file
$target = SRVRROOT . '/controllers/' . $page . '.php';

//get target
if (file_exists($target)) {
	include_once($target);

	//modify page to fit naming convention
	$class = ucfirst($page) . '_Controller';

	//instantiate the appropriate class
	if (class_exists($class)) {
		$controller = new $class($page);
	} else {
		//did we name our class correctly?
		die('class does not exist!');
	}
} else {
	//can't find the file in 'controllers'!
	die('page does not exist!');
}

//once we have the controller instantiated, execute the default function
//pass any GET varaibles to the main method
$controller->main($getVars);