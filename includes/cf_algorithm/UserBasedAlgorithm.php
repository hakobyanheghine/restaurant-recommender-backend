<?php 

final class UserBasedAlgorithm {
	protected static $_instance;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}

	public function getSimilarUsers($user_id) {
		$result = dbQuery("SELECT * FROM user_user_similarity WHERE first_user_id = $user_id OR second_user_id = $user_id", 0);
		
		$output = array();
		foreach ($result as $user) {
			if ($user->first_user_id == $user_id) {
				$output[] = array('user_id' => $user->second_user_id, 'similarity' => $user->similarity);
			} elseif ($user->second_user_id == $user_id) {
				$output[] = array('user_id' => $user->first_user_id, 'similarity' => $user->similarity);
			}
		}
		
		return $output;
	}
	
	/**
	 *
	 * @param $user_id
	 * @param array of user preffered items $user_item_ids
	 * @return array of similar items
	 */
	public function getSimilarUsersForUser($user_id) {
		global $config;
		$result = dbQuery("SELECT * FROM user_user_similarity", $user_id);
	
		$output = array();
		$temp = array();
		foreach ($result as $user) {
			if ($user->first_user_id == $user_id) {
				if (!in_array($user->second_user_id, $temp) && $user->similarity > 0) {
					$output[] = array('item_id' => $user->second_user_id, 'similarity' => $user->similarity);
					$temp[] = $user->second_user_id;
					if ($config['print_enabled'] == 1) {
						echo "sid = ". $user->second_user_id. "; fid = ". $user->first_user_id ." ; sim = " .$user->similarity."<br>";
					}
				}
			} elseif ($user->second_user_id == $user_id) {
				if (!in_array($user->first_user_id, $temp) && $user->similarity > 0) {
					$output[] = array('item_id' => $user->first_user_id, 'similarity' => $user->similarity);
					$temp[] = $user->first_user_id;
					if ($config['print_enabled'] == 1) {
						echo "fid = ". $user->first_user_id ."; sid = ". $user->second_user_id . " ; sim = " . $user->similarity."<br>";
					}
				}
			}
		}
		return $output;
	}
	
	public function getUserAverageRatingOnAllItemsExceptOne($user_id, $item_id) {
		$result = dbQuery("SELECT * FROM user_item_rating WHERE user_id=$user_id", $user_id);
		
		$sum_of_ratings = 0;
		$count = 0;
		foreach ($result as $r) {
			if ($r->item_id != $item_id) {
				$sum_of_ratings += $r->rating;
				$count++;
			}
		} 
		if ($count > 0) 
			return $sum_of_ratings / $count;
		else 
			return 0;
	}
	
	public function getUsersRatingsOnItem($item_id) {
		$result = dbQuery("SELECT * FROM user_item_rating WHERE item_id=$item_id", 0);
		
		$output = array();
		foreach ($result as $r) {
			$output[] = array('user_id' => $r->user_id, 'rating' => $r->rating);
		}
		
		return $output;
	} 
	
	public function getItemRatingPrediction($item_id, $user_id) {
		global $config;
		$user_average_rating = $this->getUserAverageRatingOnAllItemsExceptOne($user_id, $item_id);
		$result = $this->getUsersRatingsOnItem($item_id);
		
		if ($config['print_enabled'] == 1) {
			echo "active_user_average_rating = " . $user_average_rating . " ; user_id = " . $user_id . " ; item_id = " . $item_id ."<br>";
		}
		
		$numerator = 0;
		$denominator = 0;
		foreach ($result as $r) {
			$average_rating = $this->getUserAverageRatingOnAllItemsExceptOne($r['user_id'], $item_id);
			$similarity = UserSimilarity::getInstance()->calculate($user_id, $r['user_id']);
			
			if ($config['print_enabled'] == 1) {
				echo "active_user_id = " . $user_id . " ; user_id = " . $r['user_id'] .
			 		" ; rating = " . $r['rating'] . " ; average_rating = " . $average_rating . " ; sim = " . $similarity . "<br>";
			}
			$numerator += ($r['rating'] - $average_rating) * $similarity;
			$denominator += abs($similarity);
		}
	
		if ($denominator != 0) {
			$rating = $user_average_rating + $numerator / $denominator;
		} else {
			$rating = $user_average_rating;
		}
		
		if ($config['print_enabled'] == 1) {
			echo "<br>num = ".$numerator." ---- den = ".$denominator . " ; r = " .$rating."<br><br>";
		}
		
		return round($rating, 1);
	}
	
	public function calculateEvaluateMetrics() {
		$result = dbQuery("SELECT * FROM user_item_rating", 0);
		$sum_mae = 0; 
		$count = 0;
		
		foreach ($result as $r) {
			$predicted_rating = $this->getItemRatingPrediction($r->item_id, $r->user_id);
			$sum_mae += abs($predicted_rating - $r->rating);
			$count++;
		}
		$mae = $sum_mae/$count;
		
		return $mae;
	}
}

?>