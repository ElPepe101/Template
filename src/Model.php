<?php

namespace iframework;

/**
 * Driver for RedBeanPHP
 * 
 * @author ElPepe
 *
 * @todo Commenting and Documenting
 * @todo Change name from $belongs_to to $owns
 * @todo Remove issue with $belongs_to key pairing
 * 
 */
class Model //extends RedBean_SimpleModel 
{

	public $model;
	
	protected $init_cols = array();
	
	protected $belongs_to = array();
	
	//public $has_many = array();
	
	protected $has_many = array();
	
	protected $chilled = true;
	
	private $table = '';
	
	private $_belongs_to = array();
	
	//private $_has_many = array();
	
	private $_has_many = array();
	
	private static $chill = array();
	
	private static $_db = false;
	
	/**
	 * 
	 * @param ! bool $create
	 */
	public function __construct()
	{	
		// Get the table name
		$this->table = strtolower(get_called_class());
		
		// Put this table into chill state
		if($this->chilled)
		{
			self::$chill[] = $this->table;
		}
		
		return;
	}
	
	/**
	 * 
	 * @param number $create
	 */
	public function start( $create = 0 )
	{
		// Start
		$this->database();
		
		// freeze all tables in array
		// this will prevent the recreation
		// of the table in DB
		\RedBeanPHP\Facade::freeze( self::$chill );
		
		if( !! $create)
		{
			$this->create( $create );
		}
		
		return;
	}
	
	/**
	 * 
	 * @param number $length
	 */
	public function create( $length = 0 )
	{
		if( !! $length)
		{
			// Get the beans
			$this->model = \RedBeanPHP\Facade::dispense($this->table, $length);
	
			return;
		}
		
		// Instance the only RedBean model
		$this->model = \RedBeanPHP\Facade::dispense($this->table);
		
		return;
	}
	
	/**
	 * 
	 */
	public function preset()
	{
		if($this->chilled) throw new \Exception('Table: "' . $this->table . '" is chilled. Cannot be preset');
		
		// Set the amount of rows to begin with
		// http://stackoverflow.com/questions/5237640/php-array-length-for-a-two-dimensional-array-y-axis
		$this->create( max(array_map('count', $this->init_cols)) );
		
		// Set the default table columns
		// and values if ain't freezed
		foreach($this->init_cols as $col => $def)
		{
			// Cannot use numeric columns
			if(is_numeric($col)) throw new \Exception('Cannot use numeric columns in "' . $col . '" => "' . $def .'"');
			
			if(is_array($this->model))
			{
				foreach($def as $key => $val)
					$this->model[$key]->{$col} = $val;
				
				$this->store(true);
			}
			else
			{
				if(is_array($def))
				{
					foreach($def as $val)
						$this->model->{$col} = $val;
				}
				else
				{
					$this->model->{$col} = $def;
				}
				
				$this->store();
			}
		}
		
		$this->setForeignKeys();
		
		return;
	}
	
	/**
	 * 
	 */
	public function reset()
	{
		if($this->chilled) throw new \Exception('Table: "' . $this->table . '" is chilled. Cannot be reset');
		
		if( !! $this->belongs_to)
		{
			foreach($this->belongs_to as $fk => $ndx)
			{
				$class = ucfirst(strtolower($fk));
				$own = 'own' . $class;
				$this->_belongs_to[$own] = new $class();
				try 
				{
					$this->_belongs_to[$own]->reset();
				}
				catch (\Exception $e) {
					die($e);
				}
				
				$this->_belongs_to[$own]->load(array_keys($ndx));
			}
			//print_r($this->_belongs_to);
		}
		
		if( !! $this->has_many)
		{
			foreach($this->has_many as $fk => $ndx)
			{
				// First remove the n:n table
				$this->drop( strtolower($fk) . '_' . $this->table );
				
				$class = ucfirst(strtolower($fk));
				$shared = 'shared' . $class;
				$this->_has_many[$shared] = new $class();
				try 
				{
					$this->_has_many[$shared]->reset();
				} 
				catch (\Exception $e) {
					die($e);
				}
				
				$this->_has_many[$shared]->load(array_keys($ndx));
			}
		}
				
		// Delete table
		$this->drop( $this->table );
		
		// Start anew 
		$this->preset();
		
		return;
	}
	
	/**
	 * 
	 * @todo: Simplify method for easy update, or maybe a new method?
	 * 		  For now it will be nice to have it understand if is an array,
	 * 		  an int or is null/empty, that way will reduce params to one. 
	 */
	public function load(array $ndx = array(), $mode = 'batch')
	{
		if( empty($ndx) )
		{
			$this->model = \RedBeanPHP\Facade::findAll($this->table);
			
			return;
		}
		
		switch ($mode)
		{
			case 'batch':
				$this->model = \RedBeanPHP\Facade::$mode($this->table, $ndx);
				break;
			case 'load':
				$this->model = \RedBeanPHP\Facade::$mode($this->table, $ndx[0]);
				return $this->model;
				break;
		}
		
		return;
	}
	
	public function find($needles, array $data, $many = false)
	{
		if($many)
			$this->model = \RedBeanPHP\Facade::find( $this->table, $needles, $data);
		else
			$this->model = \RedBeanPHP\Facade::findOne( $this->table, $needles, $data);
		
		return $this->model; 
	}
	
	/**
	 * 
	 * @param Boolean $is_array 
	 * @return unknown
	 */
	public function store( $is_array = false )
	{
		// Store the table in DB
		if($is_array)
			$id = \RedBeanPHP\Facade::storeAll($this->model);
		
		else
			$id = \RedBeanPHP\Facade::store($this->model);
		
		return $id;
	}
	
	/**
	 *
	 * @param string $table
	 */
	private function drop( $table = '' )
	{
		\RedBeanPHP\Facade::exec("DROP TABLE IF EXISTS {$table};");
	
		return;
	}
	
	/**
	 *
	 */
	private function setForeignKeys()
	{
		if( !! $this->_belongs_to)
		{
			foreach($this->_belongs_to as $own => $instance)
			{
				$index = $this->belongs_to[str_replace('own', '', strtolower($own))];
				
				$this->load($index);
				foreach($this->model as $model)
					$model->{$own} = is_array($instance->model) ? $instance->model : [$instance->model];
				
			}
			
			$this->store(true);
		}
				
		if( !! $this->_has_many)
		{
			foreach($this->_has_many as $shared => $instance)
			{
				$index = $this->has_many[str_replace('shared', '', strtolower($shared))];
				foreach($index as $key => $reference)
				{
					$this->load($reference);
					foreach($this->model as $n => $model)
						$model->{$shared} = is_array($instance->model) ? [$instance->model[$key]] : [$instance->model];
					
					$this->store(true);
				}
			}
		}
	
		return;
	}
	
	private function database()
	{
		if( ! self::$_db)
		{
			\RedBeanPHP\Facade::setup(\iframework\Router::$config['database']['dns'],\iframework\Router::$config['database']['username'],\iframework\Router::$config['database']['password']);
			self::$_db = true;
		}
	}
	
	public function __destruct()
	{
		\RedBeanPHP\Facade::close();
		self::$_db = false;
	}
}