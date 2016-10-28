<?php 
$current_user_id = UserManager::getInstance()->getCurrentUserId();

$output = array();
if (isset($_REQUEST['item_id']) && isset($_REQUEST['rating']) && isset($_REQUEST['review_text']) && isset($_REQUEST['item_type'])) {
	$item_id = $_REQUEST['item_id'];
	$item_rating = $_REQUEST['rating'];
	$item_review_text = $_REQUEST['review_text'];
	$item_type = $_REQUEST['item_type'];
	
	UserItemManager::getInstance()->addUserItemRating($current_user_id, $item_id, $item_rating);
	UserItemManager::getInstance()->updateItemRating($item_id);
	UserItemManager::getInstance()->addItemReview($item_id, $current_user_id, $item_rating, $item_type, $item_review_text);
	
	$output['status'] = 1;
} else {
	$output['status'] = 0;
}

echo json_encode($output);
?>