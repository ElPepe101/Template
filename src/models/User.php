<?php

/**
 * User Model
 */
namespace PPMFWK\models;

class User extends \Micro\ORM
{

	public static $table = 'usr';

	public static $key = 'id_usr';

	//public static $foreign_key = 'id';

	public static $belongs_to = array(
		'level_one_data' => array(
			'id_usr' => '\PPMFWK\models\User\Data'
		),
		'level_two_data' => array(
			'id_usr' => '\PPMFWK\models\User\Extend'
		),
		'profile' => array(
			'id_profile' => '\PPMFWK\models\Profile'
		),
		'status' => array (
		 	'id_sys_stat' => '\PPMFWK\models\Status'
		),
		'clinical' => array(
			'id_usr' => '\PPMFWK\models\User\Clinical'
		)
	);

	public static $has = array(
		'membership' => array(
			'id_usr' => '\PPMFWK\models\Membership'
		),
		'consults' => array(
			'id_usr' => '\PPMFWK\models\Service\Consultant'
		),
		'bundles' => array(
			'id_usr' => '\PPMFWK\models\Bundle\User'
		)
	);

	public static $has_many_through = array(
		'office' => array(
			'id_usr' => '\PPMFWK\models\Membership',
			'id_office' => '\PPMFWK\models\Office'
		),
		'dates' => array(
			'id_usr' => '\PPMFWK\models\Membership',
			'id_membership' => '\PPMFWK\models\Membership\Date'
		),
		'products' => array(
			'id_usr' => '\PPMFWK\models\Vending',
			'id_inventory' => '\PPMFWK\models\Office\Inventory',
			'id_product' => '\PPMFWK\models\Product'
		)
	);
	
	/*
	public static $has_many_through = array(
		'dates' => array(
			'id_profile' => '\PPMFWK\models\User',
			'id_usr' => '\PPMFWK\models\Membership',
			'id_membership' => '\PPMFWK\models\Membership\Date'
		)
	);*/
}