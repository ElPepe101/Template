<?php

class User extends \iframework\Model 
{

	var $init_cols = array(
		'email' => array(
			'present'
		),
		'login' => array(
			'grammar'
		),
		'pass' => array(
			'8d2c9024fb85af7214cbccf0f4c317196b3bf6b9dd855e9008835f50d74d157b904eb03564e60fbbbbbd97c79eccf627089e90c9fe056d970edb71cbcc902047' 
		),
		'_salt' => array(
			'dba9cd7a'
		)
	);
	
	//var $init_cols = array( 'remedy' => 'a' );
	
	/*var $belongs_to = array(
		'product' => array( 1, 2, 3 )
	);
	
	/*var $has_many_through = array(
		'role' => array( 1, 2, 3 )
	);*/
	
	var $chilled = false;
}