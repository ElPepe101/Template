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