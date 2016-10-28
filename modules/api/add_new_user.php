<?php 

$user_id = UserManager::getInstance()->getCurrentUserId();
$output['user_id'] = $user_id;

echo json_encode($output);
?>