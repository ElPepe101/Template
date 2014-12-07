<?php

class Role extends \iframework\Model 
{

	var $init_cols = array(
		'name' => array(
			'member',
			'admin',
			'superadmin'
		),
		'hook' => array(
				
		)
	);
	
	var $has_many = array(
		'module' => array( 
			1 => array( 1 ), 
			3 => array( 2, 3 )
		)
	);
	
	var $chilled = false;
}