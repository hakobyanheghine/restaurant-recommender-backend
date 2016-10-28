<?php 

class AndroidConnector {

	public function __construct() {}
	
	public function authorize() {
		$user_id = 0;
				
		if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != 0) {
			$user_id = $_REQUEST['user_id'];
		} else if (isset($_REQUEST['user_fb_id'])) {
			$user_fb_id = $_REQUEST['user_fb_id'];
			$user_id = UserManager::getInstance()->getUserIdByFbId($user_fb_id);
		
			if ($user_id == 0) {
				$user_id = $this->addNewUser();
			}
		}
		
		UserManager::getInstance()->setCurrentUserId($user_id);
		
		return $user_id;
	}
	
	public function addNewUser() {
		$now = date('Y-m-d H:i:s');
		
		$fb_id = $_REQUEST['user_fb_id'];
		$first_name = $_REQUEST['first_name'];
		$last_name = $_REQUEST['last_name'];
		$gender = $_REQUEST['g'];
		$location = $_REQUEST['location'];
		
		$query = "INSERT INTO user(user_fb_id, first_name, last_name, gender, location, register_date) 
				VALUES('$fb_id', '$first_name', '$last_name', $gender, '$location', '$now');";
		dbQuery($query, 0);
		$user_id = mysql_insert_id();
		
		return $user_id;
	}
}

?>