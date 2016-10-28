<?php 

final class UserManager {
	const MOOT_TYPE_COFFEE = 'coffee';
	const MOOT_TYPE_DANCE = 'dance';
	const MOOT_TYPE_SAD = 'sad';
	const MOOT_TYPE_MUSIC = 'music';
	const MOOT_TYPE_FOOD = 'food';
	
	protected static $_instance;
	
	private $current_user_id;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function getCurrentUserId() {
		return $this->current_user_id;
	}
	
	public function setCurrentUserId($user_id) {
		$this->current_user_id = $user_id;
	}
	
	public function getUserById($user_id) {
		$output = array();
		
		$result = dbQuery("SELECT * FROM user WHERE user_id=$user_id", $user_id);
		$result = $result[0];
		
		$output['user_id'] = $result->user_id;
		$output['user_fb_id'] = $result->user_fb_id;
		$output['first_name'] = $result->first_name;
		$output['last_name'] = $result->last_name;
		$output['gender'] = $result->gender;
		$output['location'] = $result->location;
		
		return $output;
	}
	
	public function getUserIdByFbId($user_fb_id) {
		$user_id = 0;
		$result = dbQuery("SELECT * FROM user WHERE user_fb_id=$user_fb_id", 0);
		$result = $result[0];
		
		if (isset($result)) {
			$user_id = $result->user_id;	
		}
		
		return $user_id;
	}
	
	public function getRecommendations($user_id) {
		global $config;
		$user_item_ids = UserItemManager::getInstance()->getUserRatedItemIds($user_id);
		
		$result = ItemBasedAlgorithm::getInstance()->getTopNRecommendations($user_id, $user_item_ids);
		
		return $result;
	}
	
	public function getPredictionRecommendations($user_id) {
		$items = UserItemManager::getInstance()->getUserNotRatedItemIds($user_id);
		
		$item_predictions = array();
		foreach ($items as $item) {
			$predicted_rating = ItemBasedAlgorithm::getInstance()->getItemRatingPrediction($item, $user_id);
			if ($predicted_rating > 0) {
				$item_predictions[] =  array('item_id' => $item, 'predicted_rating' => $predicted_rating);
			}
		}
		
		return $item_predictions;
	}
	
	public function getCoffeeMoodRecommendations() {
		$items = UserItemManager::getInstance()->getAllItems();
	
		$output = array();
		foreach ($items as $item) {
			$item_type = strtolower($item['type']);
			$position = strpos($item_type, 'cafe');
			if (gettype($position) == 'integer') {
				$position = true;
			} else {
				$position = false;
			}
			if ($item_type == 'cafe' || $position) {
				$output[] = $item;
			}
		}
	
		return $output;
	}
	
	public function getSadMoodRecommendations() {
		$items = UserItemManager::getInstance()->getAllItems();
		
		$output = array();
		foreach ($items as $item) {
			$item_type = strtolower($item['type']);
			$item_name = strtolower($item['name']);
			if ($item_type == 'pub' || strpos($item_type, 'pub') || strpos($item_name, 'pub')) {
				$output[] = $item;
			}
		}
		
		return $output;
	}
	
	public function getDanceMoodRecommendations() {
		$items = UserItemManager::getInstance()->getAllItems();
	
		$output = array();
		foreach ($items as $item) {
			$item_type = strtolower($item['type']);
			$item_name = strtolower($item['name']);
			if ($item_type == 'club' || strpos($item_type, 'club') || strpos($item_name, 'club')) {
				$output[] = $item;
			}
		}
	
		return $output;
	}
	
	public function getFoodMoodRecommendations() {
		$items = UserItemManager::getInstance()->getAllItems();
	
		$output = array();
		foreach ($items as $item) {
			$item_type = strtolower($item['type']);
			$restaurant_position = strpos($item_type, 'restaurant');
			if (gettype($restaurant_position) == 'integer') {
				$restaurant_position = true;
			} else {
				$restaurant_position = false;
			}
				
			$food_position = strpos($item_type, 'food');
			if (gettype($food_position) == 'integer') {
				$food_position = true;
			} else {
				$food_position = false;
			}
				
			if ($item_type == 'food' || $food_position || $restaurant_position) {
				$output[] = $item;
			}
		}
	
		return $output;
	}
	
	public function getMusicMoodRecommendations() {
		$items = UserItemManager::getInstance()->getAllItems();
	
		$output = array();
		foreach ($items as $item) {
			$item_type = strtolower($item['type']);
			$club_position = strpos($item_type, 'club');
			if (gettype($club_position) == 'integer') {
				$club_position = true;
			} else {
				$club_position = false;
			}
			if ($item_type == 'club' || $club_position ) {
				$output[] = $item;
			}
		}
	
		return $output;
	}
}

?>