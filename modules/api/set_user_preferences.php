<?php 

$current_user_id = UserManager::getInstance()->getCurrentUserId();

$output = array();
if (isset($_REQUEST['preferences']) && $_REQUEST['preferences'] != '') {
	$user_preferences = json_decode($_REQUEST['preferences'], true);
	
	UserItemManager::getInstance()->setUserPreferredItems($user_preferences, $current_user_id);
	$output['status'] = 1;
} else {
	$output['status'] = 0;
}

echo json_encode($output);
?>