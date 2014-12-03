<?php
// Start common Micro functions
/**
 * Create a random 32 character MD5 token
 *
 * @return string
 */
function token()
{
	return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
}

/**
 * Make a request to the given URL using cURL.
 *
 * @param string $url
 *        	to request
 * @param array $options
 *        	for cURL object
 * @return object
 */
function curl_request($url, array $options = NULL)
{
	$ch = curl_init($url);
	
	$defaults = array(
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 5
	);
	
	// Connection options override defaults if given
	curl_setopt_array($ch, (array) $options + $defaults);
	
	// Create a response object
	$object = new stdClass();
	
	// Get additional request info
	$object->response = curl_exec($ch);
	$object->error_code = curl_errno($ch);
	$object->error = curl_error($ch);
	$object->info = curl_getinfo($ch);
	
	curl_close($ch);
	
	return $object;
}