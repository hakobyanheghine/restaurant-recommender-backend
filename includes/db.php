<?php 
require_once 'libs/adodb5/adodb.inc.php';

function dbConnect() {
	global $config;
	global $conns;
	
	$conf = $config['db'];
	
	$conns = NewADOConnection('mysql');
	$conns->PConnect($conf['host'], $conf['user'], $conf['password'], $conf['name']);
	
	if(!$conns->IsConnected()){
		$conns->ErrorMsg();
	}
	
	if(mysql_error()) {
		error_log(mysql_error());
	}
}

function dbQuery($sql, $user_id) {
	global $config;
	global $conns;
	
	if (!isset($user_id)) {
		// send to log about this
	}
	if (!isset($conns)) {
		dbConnect();
	} 
	
	$result = $conns->Execute($sql);
	
	if ($result && $result->RecordCount()) {
		$arr = array();
		
		while ($row = $result->FetchNextObj()) {
			array_push($arr, $row);
		}
		return $arr;
	} else {
		return null;
	}
}

function dbClose() {
	global $conns;
	
	if (isset($conns)) {
		$conns->Close();
	}
}

?>