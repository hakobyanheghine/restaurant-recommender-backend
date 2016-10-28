<?php 

final class UserSimilarity {
	protected static $_instance;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	public function getUserRatings($user_id) {
		$result = dbQuery("SELECT * FROM user_item_rating WHERE user_id = $user_id", 0);
	
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
	
	public function getItemsRatingsOfUsers($first_user_ratings, $second_user_ratings) {
		$output = array();
		foreach ($first_user_ratings as $first_item_rating) {
			foreach ($second_user_ratings as $second_item_rating) {
				if ($first_item_rating['item_id'] == $second_item_rating['item_id']) {
					$item_rating['item_id'] = $first_item_rating['item_id'];
					$item_rating['first_user_id'] = $first_item_rating['user_id'];
					$item_rating['second_user_id'] = $second_item_rating['user_id'];
					$item_rating['first_user_rating'] = $first_item_rating['rating'];
					$item_rating['second_user_rating'] = $second_item_rating['rating'];
						
					$this->printObject($item_rating);
						
					$output[] = $item_rating;
				}
			}
		}
		return $output;
	}
	
	public function getUsersAverageRating($item_ratings) {
		$first_user_rating_sum = 0;
		$second_user_rating_sum = 0;
		$first_item_rating_avg = 0;
		$second_item_rating_avg = 0;
		$count = 0;
		foreach ($item_ratings as $item_rating) {
			$first_user_rating_sum += $item_rating['first_user_rating'];
			$second_user_rating_sum += $item_rating['second_user_rating'];
			$count++;
		}
		if ($count != 0) {
			$first_item_rating_avg = $first_user_rating_sum/$count;
			$second_item_rating_avg = $second_user_rating_sum/$count;
		}
	
		$output = array('first_user_average' => $first_item_rating_avg, 'second_user_average' => $second_item_rating_avg);
		$this->printObject($output);
		return $output;
	}
	
	public function calculate($first_user_id, $second_user_id) {
		if ($first_user_id == $second_user_id) {
			$similarity = 1;
		} else {
			$first_user_ratings = $this->getUserRatings($first_user_id);
			$second_user_ratings = $this->getUserRatings($second_user_id);
				
			$user_ratings = $this->getItemsRatingsOfUsers($first_user_ratings, $second_user_ratings);
			$item_averages = $this->getUsersAverageRating($user_ratings);
				
			$similarity = 0;
			$numerator = 0;
			$first_item_sum = 0;
			$second_item_sum = 0;
			foreach ($user_ratings as $user_rating) {
				$first_user_value = $user_rating['first_user_rating'] - $item_averages['first_user_average'];
				$second_user_value = $user_rating['second_user_rating'] - $item_averages['second_user_average'];
				$numerator += $first_user_value * $second_user_value;
	
				$first_item_sum += $first_user_value * $first_user_value;
				$second_item_sum += $second_user_value * $second_user_value;
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