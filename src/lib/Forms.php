<?php

namespace iframework\lib;

class Forms 
{
	
	private $Form_db;
	
	private $Form_table;
	
	private $From_fields = 'TABLE_NAME, COLUMN_KEY, COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT, COLUMN_DEFAULT, DATA_TYPE, IS_NULLABLE';
	
	private $Form_constrains;
		
	private $Form_inputs = array();
	
	private $Form_referenced = array();
	
	private function db()
	{ 
		$this->Form_db = new \Micro\Database(\PPMFWK\PPMFWK::$micro_config['database']);
		$this->Form_db->connect();
	}
	
	private function setConstrains($table = '')
	{
		$dns = explode('dbname=',\PPMFWK\PPMFWK::$micro_config['database']['dns']);
		//print_r($dns);
		$this->Form_constrains = $this->Form_db->fetch('SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_COLUMN_NAME != ? AND TABLE_SCHEMA = ? AND TABLE_NAME = ?', array('', $dns[1], $table));
		
		/*
		[12] => stdClass Object
        (
            [COLUMN_NAME] => glucosa
            [CONSTRAINT_NAME] => fk_usr_clinical_analysis1
            [REFERENCED_TABLE_NAME] => analysis
            [REFERENCED_COLUMN_NAME] => id_analysis
        )
		*/
		//echo sizeof($this->Form_table), sizeof($this->Form_constrains);
		
		foreach($this->Form_table as $key => $fields) 
		{
			foreach($this->Form_constrains as $constrains)
			{
				if($fields->COLUMN_NAME == $constrains->COLUMN_NAME)
				{
					$this->Form_table[$key] = (object) array_merge((array) $fields, (array) $constrains);
					break;
				}
			}
		}
	}
	
	private function getTable($table = '')
	{
		$this->db();
		//
		$this->Form_table = $this->Form_db->fetch('SELECT '.$this->From_fields.' FROM information_schema.columns WHERE table_name = ?', array($table));
		
		$this->setConstrains($table);
	}
	
	private function getReferencedTable($table = '')
	{
		
		return $this->Form_db->fetch('SELECT '.$this->From_fields.' FROM information_schema.columns WHERE table_name = ?', array($table));
	}
	
	private function getReferencedValues($table = '')
	{
		if(isset($this->Form_referenced->{$table}))
		{
			//echo 'exists';
			return;
		}
		
		$this->Form_referenced = new stdClass();
		
		foreach($this->getReferencedTable($table) as $key => $column)
		{
			//if($column->COLUMN_KEY == 'PRI') {}
			$referenced_data = $this->Form_db->fetch("SELECT {$column->COLUMN_NAME} FROM {$table}");
			
			
			foreach($referenced_data as $ord => $data)
			{
				if($key == 0)
				{
					$this->Form_referenced->{$table}[$ord]['name'.$key] = $column->COLUMN_NAME;
					$this->Form_referenced->{$table}[$ord]['id'] = $data->{$column->COLUMN_NAME};
				}
				elseif($column->COLUMN_NAME == str_replace('id_', '', $this->Form_referenced->{$table}[$ord]['name0']))
				{
					$this->Form_referenced->{$table}[$ord]['name'.$key] = $column->COLUMN_NAME;
					$this->Form_referenced->{$table}[$ord]['data'] = $data->{$column->COLUMN_NAME};
				}
			}
			
		}
	}
	
	/**
	 * 
	 * [5] => stdClass Object
	 * (
	 * 		[COLUMN_NAME] => cardiovascular
	 * 		[COLUMN_TYPE] => int(11)
	 * 		[COLUMN_COMMENT] => multiple::Cardiovascular
	 * 		[DATA_TYPE] => int
	 * 		[IS_NULLABLE] => NO
	 * 		[CONSTRAINT_NAME] => fk_usr_clinical_heir4
	 * 		[REFERENCED_TABLE_NAME] => heir
	 * 		[REFERENCED_COLUMN_NAME] => id_heir
	 * )
	 * 
	 * 
	 * @param string $table
	 * @param string $id
	 */
	private function setInputs($table = '', $model = '', array $id = NULL)
	{
		$this->getTable((String) $table);
		
		$data = new stdClass();
		if($id)
		{
			$tbl = "\\PPMFWK\\models\\".$model;
			$data = $tbl::row($id);
		}
		
		// Save last column for future use in loop
		$last_column;
		
		foreach($this->Form_table as $fields)
		{
			// Init table
			!isset($this->Form_inputs[$fields->COLUMN_NAME]) ? $this->Form_inputs[$fields->COLUMN_NAME] = array() : null;
			// To filter type of input
			$method = explode('::', $fields->COLUMN_COMMENT);
			$basic_method = explode('-', $method[0]);
			// Get the comments
			$comment = isset($method[1]) ? $method[1] : $method[0];
			// Length of data ()
			preg_match('#\((.*?)\)#', $fields->COLUMN_TYPE, $match);
			$length = isset($match[1]) ? $match[1]: $fields->COLUMN_TYPE;
			
			$value = isset($data->{$fields->COLUMN_NAME}) ? $data->{$fields->COLUMN_NAME} : '';
			
			// for validate js
			$required = $fields->IS_NULLABLE == 'NO' ? 'required' : '';
			$max_lenght = "maxlength='{$length}'";
			
			
			if(isset($method[1]))
			{
				switch($basic_method[0])
				{
					case 'optional' :
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'checkbox';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<input type='checkbox' data-type='{$fields->DATA_TYPE}' {$max_lenght} class='check_{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' value='1'/> \n";
						break;
					case 'tooltip' 	: 
						$label .= "<input type='checkbox' class='check_{$fields->COLUMN_NAME}' />\n";
						$label .= ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'normal';
						break;
					case 'inline' 	: 
						$inline = explode('|', $comment);
						$value = $value ? $value : $inline[0]; 
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = isset($inline[1]) ? 'inline optional' : 'inline';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = isset($inline[1]) ? "\t<input type='checkbox' /><label for='{$fields->COLUMN_NAME}'>{$inline[1]}</label>\n" : '';
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<input type='text' data-type='{$fields->DATA_TYPE}' value='{$value}' data-value='{$inline[0]}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' {$max_lenght} {$required} /> \n";
						break;
					case 'multiple'	:
						// let's do recursive
						//$this->getReferencedValues($fields->REFERENCED_TABLE_NAME);
						$options = explode('|', $comment);
						$values = explode(',', $comment);
						
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'multiple';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($options[0]) ? "\t<label for='{$fields->COLUMN_NAME}'>{$options[0]}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = '';
						
						//foreach($this->Form_referenced->{$fields->REFERENCED_TABLE_NAME} as $referenced_value)
						foreach(explode(',', $options[1]) as $referenced_value)
						{
							$checked = $value == $referenced_value ? "checked='checked'" : '';
							$this->Form_inputs[$fields->COLUMN_NAME]['input'] .= "\t<input type='checkbox' data-type='{$fields->DATA_TYPE}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}[]' {$max_lenght} value='{$referenced_value}' $checked /><span>{$referenced_value}</span>\n";
						}
						break;
					case 'select'	:
						//print_r($fields);
						$multiple = isset($basic_method[1]) ? 'multiple="multiple"' : '';
						$multiple_class = isset($basic_method[1]) ? '_multiple' : '';
						
						$value_arr = array();
						if($id && $multiple != '')
						{
							//print_r($id);
							$data_arr = $tbl::fetch($id);
							//print_r($data_arr);
							foreach($data_arr as $d)
							{
								$value_arr[] = isset($d->{$fields->COLUMN_NAME}) ? $d->{$fields->COLUMN_NAME} : '';
							}
						}
						
						$name = isset($basic_method[1]) ? $fields->COLUMN_NAME.'[]' : $fields->COLUMN_NAME;
						$this->getReferencedValues($fields->REFERENCED_TABLE_NAME);
						
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'select'.$multiple_class;
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<select {$multiple} data-type='{$fields->DATA_TYPE}' name='{$name}' class='{$fields->COLUMN_NAME}' {$max_lenght} {$required} > \n";

						$this->Form_inputs[$fields->COLUMN_NAME]['input'] .= ! isset($basic_method[1]) ? "\t<option value='0' >Seleccione uno...</option>\n" : '';
						foreach($this->Form_referenced->{$fields->REFERENCED_TABLE_NAME} as $k => $referenced_value)
						{
							if(! empty($value_arr))
							{
								foreach($value_arr as $v)
								{
									$selected = '';
									if($v == $referenced_value['id'])
									{
										$selected = "selected='selected'"; 
										break;
									}
								}	
							}
							else
							{
								$selected = $value == $referenced_value['id'] ? "selected='selected'" : '';
							}
							
							$this->Form_inputs[$fields->COLUMN_NAME]['input'] .= "\t<option value='{$referenced_value['id']}' $selected>{$referenced_value['data']}</option>\n";
						}
						
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] .= "\t</select> \n";
						break;
					case 'hidden'	:
						$value = $value ? $value : $comment;
						//echo $fields->COLUMN_NAME.'<br>';
						
						if( (isset($this->Form_inputs[$fields->COLUMN_NAME]['value']) && $this->Form_inputs[$fields->COLUMN_NAME]['value'] == 'ID') || ! isset($this->Form_inputs[$fields->COLUMN_NAME]['input']) )
						{
							$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<input type='hidden' data-type='{$fields->DATA_TYPE}' value='{$value}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' {$max_lenght} {$required} /> \n";
						}
						
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'hidden';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = '';
						$this->Form_inputs[$fields->COLUMN_NAME]['value'] = $value;
						break;
					case 'password'	:
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'password';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<input type='password' data-type='{$fields->DATA_TYPE}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' {$max_lenght} {$required} /> \n";
						break;
					case 'text'	:
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'text';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<textarea data-type='{$fields->DATA_TYPE}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' {$max_lenght} {$required} >{$value}</textarea>\n";
						break;
					case 'radio' :
						
						$this->getReferencedValues($fields->REFERENCED_TABLE_NAME);
						
						$this->Form_inputs[$fields->COLUMN_NAME]['type'] = 'radiobutton';
						$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
						$this->Form_inputs[$fields->COLUMN_NAME]['input'] = '';
						
						foreach($this->Form_referenced->{$fields->REFERENCED_TABLE_NAME} as $referenced_value)
						{
							$checked = $value == $referenced_value['id'] ? "checked='checked'" : '';
							$this->Form_inputs[$fields->COLUMN_NAME]['input'] .= "\t<input type='radio' data-type='{$fields->DATA_TYPE}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' value='{$referenced_value['id']}' {$checked}/>{$referenced_value['data']}\n";
						}
						
						break;
				}
			} 
			else 
			{
				$this->Form_inputs[$fields->COLUMN_NAME]['type'] = $fields->DATA_TYPE=='int' ? 'number' : 'text';
				$this->Form_inputs[$fields->COLUMN_NAME]['label'] = ! empty($comment) ? "\t<label for='{$fields->COLUMN_NAME}'>{$comment}</label>\n" : '' ;
				$this->Form_inputs[$fields->COLUMN_NAME]['input'] = "\t<input type='{$this->Form_inputs[$fields->COLUMN_NAME]['type']}' data-type='{$fields->DATA_TYPE}' class='{$fields->COLUMN_NAME}' name='{$fields->COLUMN_NAME}' {$max_lenght} {$required} value='{$value}' /> \n";
			}

			// Default fields
			$this->Form_inputs[$fields->COLUMN_NAME]['_currentvalue'] = $value;
			$this->Form_inputs[$fields->COLUMN_NAME]['_mandatory'] = $fields->IS_NULLABLE == 'NO' ? 1 : 0;
			$this->Form_inputs[$fields->COLUMN_NAME]['_phpdatatype'] = $this->mysqlDataTypeToPHP($fields->DATA_TYPE);
			$this->Form_inputs[$fields->COLUMN_NAME]['_realdatatype'] = $fields->DATA_TYPE;
			$this->Form_inputs[$fields->COLUMN_NAME]['_defaultvalue'] = $fields->COLUMN_DEFAULT;
			$this->Form_inputs[$fields->COLUMN_NAME]['_parent'][] = $fields->TABLE_NAME;
			$this->Form_inputs[$fields->COLUMN_NAME]['_model'][$fields->TABLE_NAME] = $model;
			
			// Save current column
			$last_column = $fields->COLUMN_NAME;
		}
	}
	
	
	/**
	 * 
	 * php mysql_field_type() returns:
	 * 
	 * STRING, VAR_STRING: string
	 * TINY, SHORT, LONG, LONGLONG, INT24: int
	 * FLOAT, DOUBLE, DECIMAL: real
	 * TIMESTAMP: timestamp
	 * YEAR: year
	 * DATE: date
	 * TIME: time
	 * DATETIME: datetime
	 * TINY_BLOB, MEDIUM_BLOB, LONG_BLOB, BLOB: blob
	 * NULL: null
	 * Any other: unknown
	 * 
	 * 
	 * php gettype() returns:
	 * 
	 * "boolean"
	 * "integer"
	 * "double" (for historical reasons "double" is returned in case of a float, and not simply "float")
	 * "string"
	 * "array"
	 * "object"
	 * "resource"
	 * "NULL"
	 * "unknown type"
	 */
	private function mysqlDataTypeToPHP($data) 
	{
		switch($data)
		{
			case 'int':
			case 'tiny':
			case 'short':
			case 'long':
			case 'int24':
			case 'longlong':
			case 'bool':
				return 'integer';
				break; // in case of no return :S
			case 'float':
			case 'double':
			case 'decimal':
				return 'double';
				break; // in case of no return :S
			// time string
			case 'timestamp':
			case 'datetime':
			case 'date':
			case 'time':
			// mysql function 
			case 'string':
			case 'var_string':
			// most used 
			case 'varchar':
			case 'char':
			case 'text':
			// blobs 
			case 'tiny_blob':
			case 'medium_blob':
			case 'long_blob':
			case 'blob':
				return 'string';
				break; // in case of no return :S
			default:
				return $data;
		}	
	}
	
	private function clearInputs()
	{
		$this->Form_table = NULL;
		$this->Form_constrains = NULL;
		$this->Form_inputs = array();
		$this->Form_referenced = array();
	}
	
	/**
	 * HOOK: 3 Step Verification and a statement preparation
	 * 
	 * Step 1. Verify fields consitency, no more or less fields than required for the table 
	 * 
	 * define dependants as argument for the function on a bidimentional array
	 * 
	 * @param array $not_required Columns
	 * @param array $dependants bidimentional array
	 * @return boolean
	 */
	public function Form_save(array $not_required = array(), array $dependants = array(), $echo = true)
	{
		// Remove unwanted fields
		foreach($not_required as $needle)
		{
			unset($this->Form_inputs[$needle]);
		}
		
		//print_r($_POST);
		
		// Verify sent data is at least in the allowed fields
		$diff_is = array_diff_key($this->Form_inputs, $_POST);
		
		// Verify sent data is within max allowed
		$diff_has = array_diff_key($_POST, $this->Form_inputs);
		
		// Verify fields consistency
		if(	empty($_POST) || sizeof($this->Form_inputs) != sizeof($_POST) )
		{
			if(! empty($diff_has))
			{
				echo json_encode(array('surplus' => htmlentities(mb_convert_encoding(serialize(array_keys($diff_has)), 'UTF-8', 'UTF-8'), ENT_QUOTES, 'UTF-8')));
			}
				
			if(! empty($diff_is))
			{
				echo $str = json_encode(array('missing' => array_keys($diff_is)));
			}
				
			return false;
		}
		
		// Data validation
		$arranged = array();
		$empty_mandatory = array();
		
		foreach($_POST as $field => $value)
		{
			// Typecast to required type
			// this will remove malformed data
			settype($_POST[$field], $this->Form_inputs[$field]['_phpdatatype']);
				
			// Check any mandatory fields with
			// empty or zero value and without
			// default value in table
			if( $this->Form_inputs[$field]['_mandatory']
			&& ($_POST[$field] === 0 || $_POST[$field] === '')
			&& $this->Form_inputs[$field]['_defaultvalue'] == '')
			{
				// Save the fields for feedback/output
				$empty_mandatory[] = $field;
			}
			elseif($this->Form_inputs[$field]['_defaultvalue'] != '' 
				&& ($_POST[$field] === 0 || $_POST[$field] === ''))
			{
				// Remove incorrect data with default value
				unset($_POST[$field]);
			}
			else
			{
				// Rearrange into tables
				foreach($this->Form_inputs[$field]['_parent'] as $table)
				{
					$arranged[$table][$field] = $_POST[$field];
				}
				
			}
		}
		
		// If the data doesn't works
		// output for feedback
		if(! empty($empty_mandatory))
		{
			echo json_encode(array('mandatory'=>$empty_mandatory));
			return false;
		}
		
		// Prepare querys
		$id_value = 0;
		$q = new stdClass();
		foreach($arranged as $table => $fields)
		{
			$model = '\\PPMFWK\\models\\'.$this->Form_inputs[array_keys($fields)[0]]['_model'][$table];
			$q = new $model();
			
			foreach($fields as $field => $value)
			{
				$q->{$field} = $value;
			}
				
			// Set table dependants fields
			// from bidimentional array
			foreach ($dependants as $id => $tables)
			{
				if(in_array($table, $tables))
				{
					$q->{$id} = $id_value;
				}
			}
			
			// execute query
			$q->save();
			// save key dependant value
			$id_value = isset($id) ? $q->{$id} : 1;
		}
		
		// An error ocurred
		if($id_value == 0 || $id_value == '' || is_null($id_value) )
		{
			echo json_encode(array('status' => 'error'));
			return false;
		}
		
		if($echo)
		{
			echo json_encode(array('status' => 'ok'));
		}
		return $id_value;
	}
}