<?php

class Functions {

	/**
	* Check if provided string is a SHA1 hash
	*/
	static function is_sha1( $str ) {
		return ( bool ) preg_match( '/^[0-9a-f]{40}$/i', $str );
	}
	
	
	static function getIpAddress() {
	    return (empty($_SERVER['HTTP_CLIENT_IP']) ? (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR']) : $_SERVER['HTTP_CLIENT_IP']);
	}
	
	static function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = ""){
			$theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;
			
			switch ($theType) {
				case "text":
				$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				break;    
				case "long":
				case "int":
				$theValue = ($theValue != "") ? intval($theValue) : "NULL";
				break;
				case "double":
				$theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
				break;
				case "date":
				$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				break;
				case "defined":
				$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
				break;
			}
			return $theValue;
		}
	
	// *** Restrict Access To Page: Grant or deny access to this page
	static function _isAuthorized($strUsers, $strGroups, $MM_restrictGoTo = 'index.php') {
	
		// For security, start by assuming the visitor is NOT authorized. 
		$isValid = False; 
		
		// Parse the strings into arrays. 
		$arrUsers = Explode(",", $strUsers); 
		$arrGroups = Explode(",", $strGroups); 
	
		// By ID	
		if (in_array($_SESSION['MM_IdUser'], $arrUsers)) { 
			$isValid = true; 
		} else
	
		// By Level
		if (in_array($_SESSION['MM_UserGroup'], $arrGroups)) { 
			$isValid = true;
		}
	
		if(!isset($_SESSION['MM_IdUser'])){
			$isValid = false;
		}
	
	//echo $isValid; exit();
	
		if(!$isValid){
			$MM_qsChar = "?";
			$MM_referrer = $_SERVER['PHP_SELF'];
			if (strpos($MM_restrictGoTo, "?")) 
				$MM_qsChar = "&";
			if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
				$MM_referrer .= "?" . $QUERY_STRING;
			$MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
			header("Location: ". $MM_restrictGoTo);
			exit();
		}
	
	}
	
	static function do_logout(){
		if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
		  //to fully log out a visitor we need to clear the session varialbles
		  foreach($_SESSION as $key => $session){
			  $_SESSION[$key] = NULL;
			  unset($_SESSION[$key]);
		  }
		  //$_SESSION['MM_Username'] = NULL;
		  //$_SESSION['MM_UserGroup'] = NULL;
		  //$_SESSION['PrevUrl'] = NULL;
		  //unset($_SESSION['MM_Username']);
		  //unset($_SESSION['MM_UserGroup']);
		  //unset($_SESSION['MM_Userlevel']);
		  //unset($_SESSION['PrevUrl']);
		  //unset($_SESSION['MM_NameUser']);
		  //unset($_SESSION['MM_IdUser']);
			
		  $logoutGoTo = ROOT_URL."/index.php";
		  if ($logoutGoTo) {
		    header("Location: $logoutGoTo");
		    exit;
		  }
		}
	}
	
	static function get_banner($banner){
		$root_url = ROOT_URL;
		$query = 'SELECT id_banners, imagen, size, url, alias FROM jrh_banners WHERE status = 1 and size = ?';
		$res = exec_query($query, 'i', array($banner));
		return "<a id='banner_{$res[0]['id_banners']}' href='{$res[0]['url']}'><img src='{$root_url}/{$res[0]['imagen']}' title='{$res[0]['alias']}'></a>";
	}
	
	static function db_connect(){
		$mysqli = new mysqli(HOSTNAME_CNXJRH, USERNAME_CNXJRH, PASSWORD_CNXJRH, DATABASE_CNXJRH);
		if ($mysqli->connect_errno) {
			echo "Failed to connect to MySQL: ";// . $mysqli->connect_error;
		} else {
			
			$mysqli->set_charset("utf8");
			
			return $mysqli;
		}
	}
	
	static function exec_query($sql, $types = null, $params = null, $nosel = false){
		// create a prepared statement
		$mysqli = db_connect();
		$stmt = $mysqli->prepare($sql);
		
		// bind parameters for markers
		// but this is not dynamic enough...
		//$stmt->bind_param("s", $parameter);
		
		if($mysqli->error){
			echo 'error 1';
			print_r($mysqli->error.' > '.$sql.' params:');
			print_r($params);
			exit();
		}
		
		if($types&&$params){
		    $bind_names[] = $types;
		    for ($i=0; $i<count($params);$i++) {
		        $bind_name = 'bind' . $i;
		        $$bind_name = $params[$i];
		        $bind_names[] = &$$bind_name;
		    }
		    $return = call_user_func_array(array($stmt,'bind_param'),$bind_names);
		}
		
		//debug
		//echo' > '.$sql."<br />\nparams: "; print_r($params); echo '<br /><br />'."\n\n";
		
		# execute query 
		$stmt->execute();
		
		if($mysqli->error){
			echo 'error 2';
			//print_r($mysqli->error);
			exit();
		}
		
		if($nosel){
			$insert = $mysqli->insert_id;
			$rows = $mysqli->affected_rows;
			$stmt->free_result();
			$stmt->close();
			$mysqli->close();
			return array($insert, $rows);
		}
		
		# these lines of code below return one dimentional array, similar to mysqli::fetch_assoc()
		$data = $stmt->result_metadata();
		$fields = array();
		$currentrow = array();
		$results = array();
		
		// Store references to keys in $currentrow
		while ($field = mysqli_fetch_field($data)) {
			$fields[] = &$currentrow[$field->name];
		}
		
		// Bind statement to $currentrow using the array of references
		call_user_func_array(array($stmt,'bind_result'), $fields);
		
		// Iteratively refresh $currentrow array using "fetch", store values from each row in $results array
		$i = 0;
		while ($stmt->fetch()) {
			$results[$i] = array(); //this is supposed to be outside the foreach
			foreach($currentrow as $key => $val) {
				$results[$i][$key] = $val;
			}
			$i++;
		}
		
		$stmt->free_result();
		
		# close statement
		$stmt->close();
		$mysqli->close();
		
		return $results;
	}
	
	static function _getHeader(){
		
	// Some Globals-like trick for the include
	// For the function scope: array( string $var_name [, mixed $var_value] )
	// :: $myBoolVar = array('myBoolVar', TRUE);
	// so the included file will scope $myBoolVar
	// By: ElPepe101 :D
		$arg_list = func_get_args();
		foreach($arg_list as $val){
			$arr_vals = array_values($val);
			$arr_keys = array_keys($val);
			$$arr_keys[0] = $arr_vals[0];
		}
	
		// $MM_authorizedLevel = 'Administrador,Reclutador,Aspirante';
		$template = '';
		$session = isset($_SESSION['MM_UserGroup'])? $_SESSION['MM_UserGroup']: '';
		// By Level
		switch($session){
			case 'Aspirante': $template = 'header.php'; break;
			case 'Reclutador': $template = 'header_admin.php'; break;
			case 'Administrador': $template = 'header_admin.php'; break;
			default: $template = 'header.php';
		}	
		include_once ROOT_DIR.'/template/'.$template;
	}
	
	static function _getFooter(){
	
		$level = '';
	
	// Some Globals-like trick for the include
		$arg_list = func_get_args();
		foreach($arg_list as $val){
			$arr_vals = array_values($val);
			$arr_keys = array_keys($val);
			$$arr_keys[0] = $arr_vals[0];
		}
	
		// $MM_authorizedLevel = 'Administrador,Reclutador,Aspirante';
		$template = '';
		$session = isset($_SESSION['MM_UserGroup'])? $_SESSION['MM_UserGroup']: '';
		// By Level
		switch($session){
			case 'Aspirante': $template = 'footer.php'; break;
			case 'Reclutador': $template = 'footer_admin.php'; break;
			case 'Administrador': $template = 'footer_admin.php'; break;
			default: $template = 'footer.php';
		}	
		include_once ROOT_DIR.'/template/'.$template;
	}
	
	
	//http://stackoverflow.com/questions/4249432/export-to-csv-via-php
	static function array2csv(array &$array) {
	   if (count($array) == 0) {
	     return null;
	   }
	   ob_start();
	   $df = fopen("php://output", 'w');
	   fputcsv($df, array_keys(reset($array)));
	   foreach ($array as $row) {
	      fputcsv($df, $row);
	   }
	   fclose($df);
	   return ob_get_clean();
	}
	
	static function download_send_headers($filename) {
	    // disable caching
	    $now = gmdate("D, d M Y H:i:s");
	    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	    header("Last-Modified: {$now} GMT");
	
	    // force download  
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");
	
	    // disposition / encoding on response body
	    header("Content-Disposition: attachment;filename={$filename}");
	    header("Content-Transfer-Encoding: binary");
	}
	
}