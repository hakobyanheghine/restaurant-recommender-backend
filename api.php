<?php 

ini_set('display_errors', 1);
require_once 'includes/globals.php';

if (isset($_GET['t'])) {
	$t = $_GET['t'];
	$module = preg_replace("[/|\\|\\\\.]",'', $t);
	
	$user_id = UserManager::getInstance()->getCurrentUserId();
	
	$start_time_of_api = microtime(true);
	require_once ("modules/api/$module.php");
	$end_time_of_api = microtime(true);
	$api_duration = ($end_time_of_api - $start_time_of_api) * 1000;
}

?>