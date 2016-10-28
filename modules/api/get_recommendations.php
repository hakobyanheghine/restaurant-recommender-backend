<?php 
$current_user_id = UserManager::getInstance()->getCurrentUserId();

$output = array();

if (isset($_REQUEST['type'])) {
	$type = $_REQUEST['type'];
	
	if ($type == ItemBasedAlgorithm::RECOMMENDATION_TYPE_TOPN) {
		$recommended_items = UserManager::getInstance()->getRecommendations($current_user_id);
	} elseif ($type == ItemBasedAlgorithm::RECOMMENDATION_TYPE_PREDICTION) {
		$recommended_items = UserManager::getInstance()->getPredictionRecommendations($current_user_id);
	}
	
	if (isset($recommended_items)) {
		$all_items = UserItemManager::getInstance()->getAllItems();
	
		foreach ($all_items as $item) {
			foreach ($recommended_items as $recommended_item) {
				if ($item['item_id'] == $recommended_item['item_id']) {
					$item['predicted_rating'] = $recommended_item['predicted_rating'];
					$output[] = $item;
				}
			}
		}
	}
}

echo json_encode($output);
?>