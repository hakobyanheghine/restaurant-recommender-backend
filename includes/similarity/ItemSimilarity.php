<?php 

final class ItemSimilarity {
	protected static $_instance;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	public function getItemRatings($item_id) {
		$result = dbQuery("SELECT * FROM user_item_rating WHERE item_id = $item_id", 0);
		
		$output = array();
		if (isset($result)) {
			foreach ($result as $user_item_rating) {
				$item_rating['user_id'] = $user_item_rating->user_id;
				$item_rating['item_id'] = $user_item_rating->item_id;
				$item_rating['rating'] = $user_item_rating->rating;
				
				$this->printObject($item_rating);
				$output[] = $item_rating;
			}
		}
		return $output;
	}
	
	public function getUsersRatingsOfItems($first_item_ratings, $second_item_ratings) {
		$output = array();
		foreach ($first_item_ratings as $first_item_rating) {
			foreach ($second_item_ratings as $second_item_rating) {
				if ($first_item_rating['user_id'] == $second_item_rating['user_id']) {
					$item_rating['user_id'] = $first_item_rating['user_id']; 
					$item_rating['first_item_id'] = $first_item_rating['item_id'];
					$item_rating['second_item_id'] = $second_item_rating['item_id'];
					$item_rating['first_item_rating'] = $first_item_rating['rating'];
					$item_rating['second_item_rating'] = $second_item_rating['rating'];
					
					$this->printObject($item_rating);
					
					
					$output[] = $item_rating;
				}
			}
		}
		return $output;
	}
	
	public function getItemsAverageRating($user_ratings) {
		$first_item_rating_sum = 0;
		$second_item_rating_sum = 0;
		$first_item_rating_avg = 0;
		$second_item_rating_avg = 0;
		$count = 0;
		foreach ($user_ratings as $user_rating) {
			$first_item_rating_sum += $user_rating['first_item_rating'];
			$second_item_rating_sum += $user_rating['second_item_rating'];
			$count++;
		}
		if ($count != 0) {
			$first_item_rating_avg = $first_item_rating_sum/$count;
			$second_item_rating_avg = $second_item_rating_sum/$count;
		}
		
 		$output = array('first_item_average' => $first_item_rating_avg, 'second_item_average' => $second_item_rating_avg);
 		$this->printObject($output);
		return $output;
	}
	
	public function calculate($first_item_id, $second_item_id) {
		if ($first_item_id == $second_item_id) {
			$similarity = 1;
		} else {
			$first_item_ratings = $this->getItemRatings($first_item_id);
			$second_item_ratings = $this->getItemRatings($second_item_id);
			
			$user_ratings = $this->getUsersRatingsOfItems($first_item_ratings, $second_item_ratings);
			$item_averages = $this->getItemsAverageRating($user_ratings);
			
			$similarity = 0;
			$numerator = 0;
			$first_item_sum = 0;
			$second_item_sum = 0;
			foreach ($user_ratings as $user_rating) {
				$first_item_value = $user_rating['first_item_rating'] - $item_averages['first_item_average'];
				$second_item_value = $user_rating['second_item_rating'] - $item_averages['second_item_average']; 
				$numerator += $first_item_value * $second_item_value; 
				
				$first_item_sum += $first_item_value * $first_item_value;
				$second_item_sum += $second_item_value * $second_item_value;
			}
			
			$denominator = sqrt($first_item_sum) * sqrt($second_item_sum);
			if ($denominator != 0) {
				$similarity = $numerator / $denominator;
			}
		}
		
		return $similarity;
	}
	
	public function printObject($obj) {
		global $config;
		if ($config['print_enabled'] == 1) {
	 		foreach ($obj as $key => $value) {
				echo $key . " = " . $value . " ; ";
			}
			echo "<br>";
		}
	}
}
?>