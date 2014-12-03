<?php
namespace iframework\lib;

class Directories
{

	public static $structure;

	public static $_file;

	private static $_copy;

	private static $_i;
	
	// Contenedor Instancia de la Clase
	private static $instance;
	
	// EL metodo singleton
	public static function singleton($file = null)
	{
		if (is_dir($file))
		{
			self::$_file = $file;
		}
		elseif (! self::$_file)
		{
			echo $file . ' not found.';
			return;
		}
		
		if (! isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		
		self::$_i = 0;
		self::$structure = array();
		self::$_copy = self::$_file;
		
		return self::$instance;
	}
	
	// Clone no permitido
	function __clone()
	{
		trigger_error('Clone no se permite.', E_USER_ERROR);
	}

	private function __construct()
	{
	}

	function __destruct()
	{
		unset($this);
	}

	public static function listDir($drilldown = false)
	{
		$ignore = array(
			'cgi-bin',
			'.',
			'..',
			'._'
		); // ignore hidden files
		if (false !== ($handle = opendir(self::$_file)))
		{
			while (false !== ($e = readdir($handle)))
			{
				if (! in_array($e, $ignore) and substr($e, 0, 1) != '.')
				{
					isset($e) ? self::$structure[self::$_i]['folder'] = $e : false;
					if ($drilldown)
					{
						self::$_file = self::$_copy . $e;
						self::listFile();
					}
					self::$_i ++;
				}
			}
			closedir($handle);
		}
	}

	public static function listFile()
	{
		$x = array();
		$d = dir(self::$_file);
		$ignore = array(
			'cgi-bin',
			'.',
			'..',
			'._'
		); // ignore hidden files
		while (false !== ($e = $d->read()))
		{
			if (! in_array($e, $ignore) and substr($e, 0, 1) != '.')
			{
				$file = explode('.', $e);
				isset($file[1]) ? self::$structure[self::$_i][] = $e : false;
			}
		}
		$d->close();
	}

	public function folderXml($nd = false, $xmls = true)
	{
		ob_end_clean();
		header("Content-type: application/xml");
		$folder = array_reverse(explode('/', self::$_file));
		
		$xml = '<root type="root" value="Site Root" url="/">';
		$xml .= '<folder type="folder" value="' . $folder[0] . '" url="' . self::$_file . '/">';
		foreach (self::$structure as $aryResult)
		{
			$xml .= '<document type="document" value="' . $aryResult . '" url="' . self::$_file . '/' . $aryResult . '" />';
		}
		if ($nd == true)
		{
			$xml .= '<' . $folder[0] . ' ';
			foreach (self::$structure as $field)
			{
				$xml .= ' ' . $field . '="ND" ';
			}
			$xml .= '/>';
		}
		
		if ($xmls == true)
		{
			$xml .= '</folder>';
			$xml .= '</root>';
		}
		// $this->xml = $xml;
		return $xml;
	}
}