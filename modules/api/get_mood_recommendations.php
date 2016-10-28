<?php 

$current_user_id = UserManager::getInstance()->getCurrentUserId();

$output = array();
if (isset($_REQUEST['type'])) {
	$type = $_REQUEST['type'];

	if ($type == UserManager::MOOT_TYPE_COFFEE) {
		$output["recommendations"] = UserManager::getInstance()->getCoffeeMoodRecommendations();
	} elseif ($type == UserManager::MOOT_TYPE_SAD) {
		$output["recommendations"] = UserManager::getInstance()->getSadMoodRecommendations();
	} elseif ($type == UserManager::MOOT_TYPE_DANCE) {
		$output["recommendations"] = UserManager::getInstance()->getDanceMoodRecommendations();
	} elseif ($type == UserManager::MOOT_TYPE_MUSIC) {
		$output["recommendations"] = UserManager::getInstance()->getMusicMoodRecommendations();
	} elseif ($type == UserManager::MOOT_TYPE_FOOD) {
		$output["recommendations"] = UserManager::getInstance()->getFoodMoodRecommendations();
	}
}

echo json_encode($output);

?>