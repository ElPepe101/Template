<?php

/**
 * Profile Model
 */
namespace iframework\models;

class Profile extends \Micro\ORM
{

	public static $table = 'sys_profile';

	public static $key = 'id_profile';

	public static $foreign_key = 'id_profile';

	public static $has = array(
		'access' => '\PPMFWK\models\Access',
		'status' => array(
			'id_sys_stat' => '\PPMFWK\models\Status'
		)
	);
}
