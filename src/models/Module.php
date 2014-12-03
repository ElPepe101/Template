<?php

/**
 * Module Model
 */
namespace PPMFWK\models;

class Module extends \Micro\ORM
{

	public static $table = 'sys_module';

	public static $key = 'id_module';
	
	// public static $foreign_key = 'id_module';
	
	/*
	public static $belongs_to = array(
		'access' => array(
			'id_module' => '\PPMFWK\models\Access'
		)
	);

	public static $has = array(
		'status' => array(
			'id_sys_stat' => '\PPMFWK\models\Status'
		)
	);
	*/
}
