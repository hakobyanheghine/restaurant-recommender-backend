<?php

$output = array();
if (isset($_REQUEST['item_id'])) {
	$item_id = $_REQUEST['item_id'];
	
	$result = UserItemManager::getInstance()->getItemReviews($item_id);
	if (isset($result)) {
		foreach ($result as $r) {
			$user = UserManager::getInstance()->getUserById($r->user_id);
			$r->user_name = $user['first_name'] . " " . $user['last_name'];
			$r->user_fb_id = $user['user_fb_id']; 
		}
	} else {
		$result = array();
	}
	$output['reviews'] = $result;
}

echo json_encode($output);
?>