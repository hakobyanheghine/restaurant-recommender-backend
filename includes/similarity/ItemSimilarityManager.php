<?php 

final class ItemSimilarityManager {
	protected static $_instance;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	public function calculetItemsSimilarities($item_ids) {
		global $config;
		$output = array();
		
		for ($i = 0; $i < count($item_ids); $i++) {
			for ($j = $i + 1; $j < count($item_ids); $j++) {
				$similarity = ItemSimilarity::getInstance()->calculate($item_ids[$i], $item_ids[$j]);
				if ($config['print_enabled'] == 1) {
					echo "item_ids[".$i."] = " . $item_ids[$i]." ; ";
					echo "item_ids[".$j."] = " . $item_ids[$j].  " ; s = ". $similarity ." <br>";
				}
				if ($similarity != 0) {
					$similarity = round($similarity, 2);
					$output[] = array('first_item_id' => $item_ids[$i], 'second_item_id' => $item_ids[$j], 'similarity' => $similarity);
				}
			}
		}
		
		return $output;
	}
	
	public function calculetUsersSimilarities($user_ids) {
		global $config;
		$output = array();
	
		for ($i = 0; $i < count($user_ids); $i++) {
			for ($j = $i + 1; $j < count($user_ids); $j++) {
				$similarity = UserSimilarity::getInstance()->calculate($user_ids[$i], $user_ids[$j]);
				if ($config['print_enabled'] == 1) {
					echo "user_ids[".$i."] = " . $user_ids[$i]." ; ";
					echo "user_ids[".$j."] = " . $user_ids[$j].  " ; s = ". $similarity ." <br>";
				}
				if ($similarity != 0) {
					$similarity = round($similarity, 2);
					$output[] = array('first_user_id' => $user_ids[$i], 'second_user_id' => $user_ids[$j], 'similarity' => $similarity);
				}
			}
		}
	
		return $output;
	}
	
	public function updateItemItemSimilarity($item_count) {
		if ($item_count == 0) {
			$item_ids = UserItemManager::getInstance()->getAllItemIds();
		} else {
			$item_ids = array();
			for ($i = 0; $i < $item_count; $i++) {
				$item_ids[$i] = $i + 1;
			}
		}
		$result = $this->calculetItemsSimilarities($item_ids);
		
		dbQuery("DROP TABLE item_item_similarity", 0);
		dbQuery("CREATE TABLE `item_item_similarity` (
				  `first_item_id` int(11) NOT NULL,
				  `second_item_id` int(11) NOT NULL,
				  `similarity` float NOT NULL,
				  PRIMARY KEY (`first_item_id`,`second_item_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8", 0);
		
		$query = "INSERT INTO item_item_similarity";
		
		$fields_str = "(";
		$values_str = "";
		$is_fields_str_ready = false;
		foreach ($result as $o) {
			$values_str .= "(";
			
			foreach ($o as $key => $value) {
				if (!$is_fields_str_ready) {
					$fields_str .= $key . ",";
				}
				$values_str .= $value . ",";
			}
			$values_str = substr_replace($values_str, "),", strlen($values_str) - 1, 1);
			
			if (!$is_fields_str_ready) {
				$is_fields_str_ready = true;
			}
		}
		$fields_str = substr_replace($fields_str, ")", strlen($fields_str) - 1, 1);
		$values_str = substr($values_str, 0, strlen($values_str) - 1);
		$query .= $fields_str . " VALUES" . $values_str . ";";
		
		echo "<br>".$query ."<br>";
		dbQuery($query, 0);
	}
	
	public function updateUserUserSimilarity($users_count) {
		if ($users_count == 0) {
			$user_ids = UserItemManager::getInstance()->getAllUserIds();
		} else {
			$user_ids = array();
			for ($i = 0; $i < $users_count; $i++) {
				$user_ids[$i] = $i + 1;
			}
		}
		$result = $this->calculetUsersSimilarities($user_ids);
		
		dbQuery("DROP TABLE user_user_similarity", 0);
		dbQuery("CREATE TABLE `user_user_similarity` (
				  `first_user_id` int(11) NOT NULL,
				  `second_user_id` int(11) NOT NULL,
				  `similarity` float NOT NULL,
				  PRIMARY KEY (`first_user_id`,`second_user_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8", 0);
		
		$query = "INSERT INTO user_user_similarity";
	
		$fields_str = "(";
		$values_str = "";
		$is_fields_str_ready = false;
		foreach ($result as $o) {
			$values_str .= "(";
				
			foreach ($o as $key => $value) {
				if (!$is_fields_str_ready) {
					$fields_str .= $key . ",";
				}
				$values_str .= $value . ",";
			}
			$values_str = substr_replace($values_str, "),", strlen($values_str) - 1, 1);
				
			if (!$is_fields_str_ready) {
				$is_fields_str_ready = true;
			}
		}
		$fields_str = substr_replace($fields_str, ")", strlen($fields_str) - 1, 1);
		$values_str = substr($values_str, 0, strlen($values_str) - 1);
		$query .= $fields_str . " VALUES" . $values_str . ";";
	
		echo "<br>".$query ."<br>";
		dbQuery($query, 0);
	}
	
	public function getItemItemSimilarity($first_item_id, $second_item_id) {
		if ($first_item_id == $second_item_id) {
			$similarity = 1;
		} else {
			$result = dbQuery("SELECT * FROM item_item_similarity", 0);
			
			$similarity = 0;
			foreach ($result as $r) {
				if (($r->first_item_id == $first_item_id && $r->second_item_id == $second_item_id) ||
					($r->first_item_id == $second_item_id && $r->second_item_id == $first_item_id)) {
					$similarity = $r->similarity;
					break;
				}
			}
		}
		
		return $similarity;
	}
	
	public function getUserUserSimilarity($first_user_id, $second_user_id) {
		$result = dbQuery("SELECT * FROM user_user_similarity", 0);
	
		$similarity = 0;
		foreach ($result as $r) {
			if (($r->first_user_id == $first_user_id && $r->second_user_id == $second_user_id) ||
			($r->first_user_id == $second_user_id && $r->second_user_id == $first_user_id)) {
				$similarity = $r->similarity;
				break;
			}
		}
	
		return $similarity;
	}
}
?>