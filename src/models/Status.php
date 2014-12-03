<?php

/**
 * Status Model
 */
namespace iframework\models;

class Status extends \Micro\ORM
{

	public static $table = 'sys_stat';

	public static $key = 'id_sys_stat';

	public static $foreign_key = 'id_sys_stat';

	public static $cascade_delete = TRUE;
}
