<?php 

final class UserItemManager {
	const PREFERRED_ITEM_RATING_TRESHOLD = 3;
	const MAXIMUM_ITEM_RATING = 5;
	const MINIMUM_ITEM_RATING = 1;
	
	protected static $_instance;
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	public function getUserRatedItemIds($user_id) {
		global $config;
		$result = dbQuery("SELECT * FROM user_item_rating WHERE user_id=$user_id", $user_id);
		
		$output = array();
		foreach ($result as $r) {
			if ($r->rating >= UserItemManager::PREFERRED_ITEM_RATING_TRESHOLD) {
				$output[] = $r->item_id;
				if ($config['print_enabled'] == 1) {
					echo "item_id = " . $r->item_id . " <br>";
				}
			}
		}
		
		return $output;
	}
	
	public function getUserRatedItems($user_id) {
		$result = dbQuery("SELECT * FROM user_item_rating WHERE user_id=$user_id", $user_id);
	
		$output = array();
		foreach ($result as $r) {
			if ($r->rating >= UserItemManager::PREFERRED_ITEM_RATING_TRESHOLD) {
				$output[] = array('item_id' => $r->item_id, 'rating' => $r->rating);
			}
		}
	
		return $output;
	}
	
	public function getUserNotRatedItemIds($user_id) {
		$items = $this->getAllItemIds();
		$user_preferred_items = $this->getUserRatedItemIds($user_id);
		
		$output = array();
		foreach ($items as $item) {
			if (!in_array($item, $user_preferred_items)) {
				$output[] = $item;
			}
		}
		
		return $output;
	}
	
	public function setUserPreferredItems($user_preferences, $user_id) {
		$item_fb_ids = $this->getAllItemFbIds();
	
		foreach ($user_preferences as $user_preference) {
			if (in_array($user_preference['fb_id'], $item_fb_ids)) {
				$item = $this->getItemByFbId($user_preference['fb_id']);
			} else {
				$item = array();
				$item['item_fb_id'] = $user_preference['fb_id'];
				$item['name'] = $user_preference['name'];
				$item['address'] = $user_preference['location'];
				$item['working_hours'] = $user_preference['working_hours'];
				$item['type'] = $user_preference['type'];
	
				$item['item_id'] = $this->addItem($item);
			}
				
			$this->addUserItemRating($user_id, $item['item_id'], UserItemManager::MAXIMUM_ITEM_RATING);
		}
	}
	
	public function addUserItemRating($user_id, $item_id, $rating) {
		dbQuery("INSERT INTO user_item_rating(user_id,item_id,rating) VALUES($user_id, $item_id, $rating);", $user_id);
		$this->updateItemRating($item_id);
	}
	
	public function getUserItemRating($user_id, $item_id) {
		$user_items = dbQuery("SELECT * FROM user_item_rating WHERE user_id=$user_id", $user_id);
		
		foreach ($user_items as $item) {
			if ($item->item_id == $item_id) {
				return $item->rating;
			}
		}
		
		return 0;
	}
	
	public function addItemReview($item_id, $user_id, $rating, $item_type, $review_text) {
		$now = date('Y-m-d H:m:s');
		dbQuery("INSERT INTO item_review(item_id, user_id, rating, item_type, review_text,date) 
						VALUES($item_id, $user_id, $rating, '$item_type', '$review_text', '$now')", $user_id);
	}
	
	public function updateItemRating($item_id) {
		$item_ratings = ItemSimilarity::getInstance()->getItemRatings($item_id);
		
		$count = 0;
		$sum = 0;
		foreach ($item_ratings as $item_rating) {
			$count++;
			$sum += $item_rating['rating'];
		}
		if ($count > 0) {
			$item_rating_avg = round($sum / $count, 1);
		} else {
			$item_rating_avg = 1;
		}
		
		dbQuery("UPDATE item SET rating = $item_rating_avg, rating_count = $count WHERE item_id=$item_id", 0);
	}
	
	public function getAllUserIds() {
		$output = array();
		$result = dbQuery("SELECT user_id FROM user", 0);
		if (isset($result)) {
			foreach ($result as $user_id) {
				$output[] = $user_id->user_id;
			}
		}	
		
		return $output;
	}
	
	public function getAllItemIds() {
		$output = array();
		$result = dbQuery("SELECT item_id FROM item", 0);
		if (isset($result)) {
			foreach ($result as $item_id) {
				$output[] = $item_id->item_id;
			}
		}
		
		return $output;
	}
	
	public function getAllItemFbIds() {
		$output = array();
		$result = dbQuery("SELECT item_fb_id FROM item", 0);
		if (isset($result)) {
			foreach ($result as $item_id) {
				$output[] = $item_id->item_fb_id;
			}
		}
		
		return $output;
	}

	public function getAllItems() {
		$output = array();
		$result = dbQuery("SELECT * FROM item", 0);
		
		$temp = array();
		foreach ($result as $item) {
			foreach ($item as $key => $value) {
				$temp[$key] = $value;
			}
			$output[] = $temp;
		}
		
		return $output;
	}
	
	public function getItemByFbId($item_fb_id) {
		$result = dbQuery("SELECT * FROM item", 0);
		$output = array();
		foreach ($result as $r) {
			if ($r->item_fb_id == $item_fb_id) {
				$output['item_id'] = $r->item_id;
				$output['item_fb_id'] = $r->item_fb_id;
				$output['name'] = $r->name;
				$output['address'] = $r->address;
				$output['working_hours'] = $r->working_hours;
				$output['type'] = $r->type;
				$output['rating'] = $r->rating;
				$output['rating_count'] = $r->rating_count;
				
				break;
			}
		}
		
		return $output;
	}
	
	public function addItem($item) {
		$fields_str = "(";
		$values_str = "(";
		foreach ($item as $key=>$value) {
			$fields_str .= $key . ",";
			$values_str .= "'".$value . "',";
		}
		$fields_str = substr_replace($fields_str, ")", strlen($fields_str) - 1, 1);
		$values_str = substr_replace($values_str, ")", strlen($values_str) - 1, 1);
		
		$query = "INSERT INTO item" . $fields_str . " VALUES" . $values_str .";";
		dbQuery($query, 0); 
		$item_id = mysql_insert_id();
		
		return $item_id;
	}
	
	public function constructItemItemSimilarityMatrix($items) {
		if (!isset($items))
			$items = $this->getAllItemIds();
		$result = dbQuery("SELECT * FROM item_item_similarity", 0);
		
		$ratings = array();
		for ($i = 0; $i < count($items); $i++) {
			for ($j = 0; $j < count($items); $j++) {
				$ratings[$i][$j] = 0;
			}
		}
		
		foreach ($result as $r) {
			$ratings[$r->first_item_id - 1][$r->second_item_id - 1] = $r->similarity;
		}
		echo "<table style='border:1px solid black;'>";
		echo "<tr>";
		echo "<td style='border:1px solid black;width=200px;'>x </td>";
		for ($j = 0; $j < count($items); $j++) {
			echo "<td style='border:1px solid black;width=200px;'>".$items[$j]. " </td>";
		}
		echo "</tr>";
		
		
		for ($i = 0; $i < count($items); $i++) {
			echo "<tr>";
			echo "<td style='border:1px solid black;width=200px;'>".$items[$i]. " </td> ";
			for ($j = 0; $j < count($items); $j++) {
				echo "<td style='border:1px solid black;width=200px;'>".$ratings[$i][$j] . "</td>  ";
			}
			echo "<tr>";
		}
		echo "</table>";
	}
	
	public function constructUserUserSimilarityMatrix($users) {
		if(!isset($users)) 
			$users = $this->getAllUserIds();
		$result = dbQuery("SELECT * FROM user_user_similarity", 0);
	
		$ratings = array();
		for ($i = 0; $i < count($users); $i++) {
			for ($j = 0; $j < count($users); $j++) {
				$ratings[$i][$j] = 0;
			}
		}
	
		foreach ($result as $r) {
			$ratings[$r->first_user_id - 1][$r->second_user_id - 1] = $r->similarity;
		}
		echo "<table style='border:1px solid black;'>";
		echo "<tr>";
		echo "<td style='border:1px solid black;width=200px;'>x </td>";
		for ($j = 0; $j < count($users); $j++) {
			echo "<td style='border:1px solid black;width=200px;'>".$users[$j]. " </td>";
		}
		echo "</tr>";
	
	
		for ($i = 0; $i < count($users); $i++) {
			echo "<tr>";
			echo "<td style='border:1px solid black;width=200px;'>".$users[$i]. " </td> ";
			for ($j = 0; $j < count($users); $j++) {
				echo "<td style='border:1px solid black;width=200px;'>".$ratings[$i][$j] . "</td>  ";
			}
			echo "<tr>";
		}
		echo "</table>";
	}
	
	public function constructUserItemMatrix($users, $items) {
		$result = dbQuery("SELECT * FROM user_item_rating", 0);
		if (!isset($users))
			$users = $this->getAllUserIds();
		if (!isset($items))
			$items = $this->getAllItemIds();
		
		$ratings = array();
		for ($i = 0; $i < count($users); $i++) {
			for ($j = 0; $j < count($items); $j++) {
				$ratings[$i][$j] = 0;
			}
		}
		
		foreach ($result as $r) {
				$ratings[$r->user_id - 1][$r->item_id - 1] = $r->rating;
		}
		
		echo "<table style='border:1px solid black;'>";
		echo "<tr>";
		echo "<td style='border:1px solid black;width=200px;'>x </td>";
		for ($j = 0; $j < count($items); $j++) {
			echo "<td style='border:1px solid black;width=200px;'>".$items[$j] . " </td>";
		}
		echo "</tr>";
		
		for ($i = 0; $i < count($users); $i++) {
			echo "<tr>";
			echo "<td style='border:1px solid black;width=200px;'>".$users[$i]. " </td> ";
			
			for ($j = 0; $j < count($items); $j++) {
				echo "<td style='border:1px solid black;width=200px;'>".$ratings[$i][$j] . "</td>  ";
				
			}
			echo "<tr>";
		}
		echo "</table>";
		return $ratings;
	}
	
	public function getItemReviews($item_id) {
		$result = dbQuery("SELECT * FROM item_review WHERE item_id=$item_id", 0);
		return $result;
	}
	
	public function getRelevantItemIds($item_ids) {
		if (!isset($item_ids))
			$item_ids = $this->getAllItemIds();
		$result = dbQuery("SELECT * FROM user_item_rating", 0);
		$output = array();
		
		foreach ($item_ids as $item_id) {
			$sum = 0;
			$count = 0;
			foreach ($result as $r) {
				if ($item_id == $r->item_id) {
					$sum += $r->rating;
					$count++;
				}
			} 
			if ($count > 0) {
				$average = $sum / $count;
			} else {
				$average = 0;
			}
			
			if ($average > 3.5) {
				$output[] = $item_id;
			}
		}
		
		return $output;
	}
	
	public function generateUserItemRatings($sparsity, $user_count, $item_count) {
		dbQuery("DROP TABLE user_item_rating", 0);
		dbQuery("CREATE TABLE `user_item_rating` (
				  `user_id` int(11) NOT NULL,
				  `item_id` int(11) NOT NULL,
				  `rating` int(1) NOT NULL,
				  PRIMARY KEY (`user_id`,`item_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8", 0);
		
		$query = "INSERT INTO `user_item_rating`(`user_id`, `item_id`, `rating`) VALUES";
		$done = array();
		$i = 0;
		do {
			$found = false;
			$user_id = rand(1, $user_count);
			$item_id = rand(1, $item_count);
			$rating = rand(1, 5);
			foreach ($done as $d) {
				if ($d['item_id'] == $item_id && $d['user_id'] == $user_id) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$done[] = array('item_id' => $item_id, 'user_id' => $user_id);
				$query .= "('$user_id', '$item_id', '$rating'),";
				$i++;
			}
		} while($i != $sparsity);
		
		$query = substr($query, 0, strlen($query) - 1);
		$query .= ";";
		echo $query;
		dbQuery($query, 0);
	}
}
?>