<?php

class Module extends \iframework\Model 
{

	var $init_cols = array(
		'name' => array(
			'Inicio',
			'Usuarios',
			'Perfil'
		),
		'slug' => array(
			'inicio',
			'usuarios',
			'perfil'
		),
		'status' => array(
			1,
			1,
			1
		)
	);
	
	var $chilled = false;
}