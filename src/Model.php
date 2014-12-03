<?php

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
	
	/**
	 * 
	 * @param ! bool $create
	 */
	public function __construct( $create = 0 )
	{	
		// Get the table name
		$this->table = strtolower(get_called_class());
		
		// Put this table into chill state
		if($this->chilled)
		{
			self::$chill[] = $this->table;
		}
		
		// freeze all tables in array
		// this will prevent the recreation
		// of the table in DB
		R::freeze( self::$chill );
		
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
			$this->model = R::dispense($this->table, $length);
	
			return;
		}
		
		// Instance the only RedBean model
		$this->model = R::dispense($this->table);
		
		return;
	}
	
	/**
	 * 
	 */
	public function preset()
	{
		if($this->chilled) throw new Exception('Table: "' . $this->table . '" is chilled. Cannot be preset');
		
		// Set the amount of rows to begin with
		// http://stackoverflow.com/questions/5237640/php-array-length-for-a-two-dimensional-array-y-axis
		$this->create( max(array_map('count', $this->init_cols)) );
		
		// Set the default table columns
		// and values if ain't freezed
		foreach($this->init_cols as $col => $def)
		{
			// Cannot use numeric columns
			if(is_numeric($col)) throw new Exception('Cannot use numeric columns in "' . $col . '" => "' . $def .'"');
			
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
		if($this->chilled) throw new Exception('Table: "' . $this->table . '" is chilled. Cannot be reset');
		
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
				catch (Exception $e) {}	
				
				$this->_belongs_to[$own]->load($ndx);
			}
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
				catch (Exception $e) {}
				
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
	 */
	public function load(array $ndx = array())
	{
		if( empty($ndx) )
		{
			$this->model = R::findAll($this->table);
			
			return;
		}
		
		$this->model = R::batch($this->table, $ndx);
		
		return;
	}
	
	/**
	 * 
	 * @param number $key
	 */
	public function store( $array = false )
	{
		// Store the table in DB
		if($array) 
			R::storeAll($this->model);
		
		else 
			R::store($this->model);
		
		return;
	}
	
	/**
	 *
	 * @param string $table
	 */
	private function drop( $table = '' )
	{
		R::exec("DROP TABLE IF EXISTS {$table};");
	
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
				$this->load($nstnc);
				foreach($this->model as $model)
					$model->{$own} = is_array($instance->model) ? $instance->model : [$instance->model];
			}
			
			$this->store(true);
		}
				
			//if( !! $this->has_many) {}
				
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
}