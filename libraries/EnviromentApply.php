<?php 

class EnviromentApply_Library {

	public function getIpAddress() {
	    return (empty($_SERVER['HTTP_CLIENT_IP']) ? (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR']) : $_SERVER['HTTP_CLIENT_IP']);
	}
	
	// *** Restrict Access To Page: Grant or deny access to this page
	public function _isAuthorized($strUsers, $strGroups, $MM_restrictGoTo = 'index.php') {
	
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
	
	public function do_logout(){
		if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
		  //to fully log out a visitor we need to clear the session varialbles
		  foreach($_SESSION as $key => $session){
			  $_SESSION[$key] = NULL;
			  unset($_SESSION[$key]);
		  }
			
		  $logoutGoTo = ROOT_URL."/index.php";
		  if ($logoutGoTo) {
		    header("Location: $logoutGoTo");
		    exit;
		  }
		}
	}
	
	public function exec_query($sql, $types = null, $params = null, $nosel = false){
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
	
	//http://stackoverflow.com/questions/4249432/export-to-csv-via-php
	public function array2csv(array &$array) {
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
	
	public function download_send_headers($filename) {
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