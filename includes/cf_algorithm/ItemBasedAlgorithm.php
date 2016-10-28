<?php 

final class ItemBasedAlgorithm {
	const RECOMMENDATION_TYPE_TOPN = 1;
	const RECOMMENDATION_TYPE_PREDICTION = 2;
	
	protected static $_instance;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	
	/**
	 * 
	 * @param $item_id
	 * @return array of similar items of $item_id
	 */
	public function getSimilarItems($item_id) {
		$result = dbQuery("SELECT * FROM item_item_similarity WHERE first_item_id = $item_id OR second_item_id = $item_id", 0);
		
		$output = array();
		foreach ($result as $item) {
			if ($item->first_item_id == $item_id) {
				$output[] = array('item_id' => $item->second_item_id, 'similarity' => $item->similarity);
			} elseif ($item->second_item_id == $item_id) {
				$output[] = array('item_id' => $item->first_item_id, 'similarity' => $item->similarity);
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
	public function getSimilarItemsForUserItems($user_id, $user_item_ids) {
		global $config;
		$result = dbQuery("SELECT * FROM item_item_similarity", $user_id);
		
		$output = array();
		$temp = array();
		foreach ($result as $item) {
			if (in_array($item->first_item_id, $user_item_ids)) {
				if (!in_array($item->second_item_id, $temp) && !in_array($item->second_item_id, $user_item_ids) && $item->similarity > 0) {
					$output[] = array('item_id' => $item->second_item_id, 'similarity' => $item->similarity);
					$temp[] = $item->second_item_id;
					if ($config['print_enabled'] == 1) {
						echo "sid = ". $item->second_item_id. "; fid = ". $item->first_item_id ." ; sim = " .$item->similarity."<br>";
					}
				}
			} elseif (in_array($item->second_item_id, $user_item_ids)) {
				if (!in_array($item->first_item_id, $temp) && !in_array($item->first_item_id, $user_item_ids) && $item->similarity > 0) {
					$output[] = array('item_id' => $item->first_item_id, 'similarity' => $item->similarity);
					$temp[] = $item->first_item_id;
					if ($config['print_enabled'] == 1) {
						echo "fid = ". $item->first_item_id ."; sid = ". $item->second_item_id . " ; sim = " . $item->similarity."<br>";
					}
				}
			}
		}
		return $output;
	}
	
	public function computeSetSimilarityToUserItems($user_id, $user_item_ids, $similar_items) {
		$output = array();
		foreach ($similar_items as $similar_item) {
			$sum = 0;
			foreach ($user_item_ids as $user_item) {
				$sum += $similar_item['similarity'];
			}
			$output[] = array('item_id' => $similar_item['item_id'], 'set_similarity' => $sum);
		}
		
		return $output;
	}
	
	public function getTopNRecommendations($user_id, $user_item_ids) {
		global $config;
		$user_simimlar_items = $this->getSimilarItemsForUserItems($user_id, $user_item_ids);
		$similar_item_set_similarity = $this->computeSetSimilarityToUserItems($user_id, $user_item_ids, $user_simimlar_items);
		
		if ($config['print_enabled'] == 1) {
			echo "recommended items <br>";
			foreach ($similar_item_set_similarity as $item) {
				echo "item_id = " . $item['item_id'] . " ; sim = ".$item['set_similarity']."<br>";
			}
		}
		
		return $similar_item_set_similarity;
	}
	
	public function getItemRatingPrediction($item_id, $user_id) {
		global $config;
		$user_items = UserItemManager::getInstance()->getUserRatedItems($user_id);
		
		if ($config['print_enabled'] == 1) {
			echo "<br>item_id = " . $item_id . " ; user_rated_items { ";
			foreach ($user_items as $i) {
				echo $i['item_id'] . " , ";
			}
			echo "} <br>";
		}
		
		$numerator = 0;
		$denominator = 0;
		foreach ($user_items as $user_item) {
			$similarity = ItemSimilarityManager::getInstance()->getItemItemSimilarity($item_id, $user_item['item_id']); 
			if ($user_item['item_id'] != $item_id && $similarity >= 0) {
				$numerator += $similarity * $user_item['rating'];
				$denominator += abs($similarity);
			}
		}
		
		if ($denominator != 0) {
			$rating = abs($numerator / $denominator);
		} else {
			$rating = 0;
		}
		return round($rating, 1);
	}
}
?>