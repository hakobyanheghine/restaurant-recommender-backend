<?php 
$start = microtime(true);

// $users = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
// $items = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
$ratings = UserItemManager::getInstance()->constructUserItemMatrix(null, null);
echo "<br><br><br>";
UserItemManager::getInstance()->constructUserUserSimilarityMatrix(null);

$items = UserItemManager::getInstance()->getAllItemIds();
$users = UserItemManager::getInstance()->getAllUserIds();


$predicted_ratings = array();
$i = 0; $j = 0;
$sum = 0;
$sum2 = 0;
$n = 0;
foreach ($users as $u) {
	$j = 0;
	foreach ($items as $it) {

		$rating = UserBasedAlgorithm::getInstance()->getItemRatingPrediction($it, $u);
		$predicted_ratings[$i][$j] = $rating;
		if ($ratings[$i][$j] != 0) {
			$sum += abs($rating - $ratings[$i][$j]);
			$sum2 += ($rating - $ratings[$i][$j]) * ($rating - $ratings[$i][$j]);
			$n++;
		}
		$j++;
	}
	$i++;
}

$relevant_items =UserItemManager::getInstance()->getRelevantItemIds(null);
$counts = array();

echo "<br><br><br>";
echo "<table>";
for ($i = 0; $i < count($users); $i++) {
	$count = 0;
	echo "<tr><td style='border:1px solid black;width=200px;'>user_id = " . $users[$i] . "</td>";
	for ($j = 0; $j < count($items); $j++) {
		echo "<td style='border:1px solid black;width=200px;'>" . $ratings[$i][$j] . " / " . $predicted_ratings[$i][$j] . " </td> ";
		if ($predicted_ratings[$i][$j] > 3.5 && in_array(($j+1), $relevant_items)) {
			$count++;
		}
	}
	$counts[] = $count;
	echo "<td style='border:1px solid black;width=200px;'>".$count."</td>";
	echo "</tr>";
}
echo "</table>";
echo "MAE = " . $sum / $n . "<br>";
echo "RMSE = " . sqrt($sum2 / $n) . "<br>";

$sum_roc = 0;
foreach ($counts as $c) {
	$sum_roc += $c/count($relevant_items);
}
echo "ROC = " . $sum_roc / count($users) . "<br>";

$total = microtime(true) - $start;
echo "Running time = " . $total;

?>