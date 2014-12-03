<?php

/**
 * Access Model
 */
namespace PPMFWK\models;

class Access extends \Micro\ORM
{

	public static $table = 'sys_module_profile';

	public static $key = 'id_module_profile';
	
	// public static $foreign_key = 'fk_cat_module_has_cat_profile_cat_module1';
	
	public static $foreign_key = 'id_module';

	public static $belongs_to = array(
		'module' => array(
			'id_module' => '\PPMFWK\models\Module'
		)
	);
}
