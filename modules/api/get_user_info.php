<?php 

$current_user_id = UserManager::getInstance()->getCurrentUserId();

$user = UserManager::getInstance()->getUserById($current_user_id);

echo json_encode($user);
?>