<?php 
require_once 'config.php';
require_once 'db.php';

require_once 'includes/classes/AndroidConnector.php';
require_once 'includes/classes/UserManager.php';
require_once 'includes/classes/UserItemManager.php';

require_once 'includes/similarity/ItemSimilarity.php';
require_once 'includes/similarity/UserSimilarity.php';
require_once 'includes/similarity/ItemSimilarityManager.php';

require_once 'includes/cf_algorithm/ItemBasedAlgorithm.php';
require_once 'includes/cf_algorithm/UserBasedAlgorithm.php';

$connector = new AndroidConnector();

if (isset($_REQUEST['t']) && !in_array($_REQUEST['t'], $config['tests'])) {
	$current_user_id = $connector->authorize();
} else {
	$current_user_id = 0;
}

if ($current_user_id) {
	UserManager::getInstance()->setCurrentUserId($current_user_id);
}

?>