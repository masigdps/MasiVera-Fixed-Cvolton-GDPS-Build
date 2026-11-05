<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
require_once "../lib/GJPCheck.php";

$stars = 0;
$count = 0;
$xi = 0;
$lbstring = "";
$date = date("d-m");

if(empty($_POST["gameVersion"])){
	$sign = "< 20 AND gameVersion <> 0";
}else{
	$sign = "> 19";
}

// MasiVera: Fixed UDID logic
if(!empty($_POST["accountID"])){
	$accountID = GJPCheck::getAccountIDOrDie();
} else {
	$accountID = ExploitPatch::remove($_POST["udid"]);
	if(!is_numeric($accountID)) {
		exit("-1");
	}
}

$type = ExploitPatch::remove($_POST["type"]);

if($type == "top" OR $type == "creators" OR $type == "relative"){
	if($type == "top"){
		$query = $db->prepare("SELECT * FROM users WHERE isBanned = '0' AND gameVersion $sign AND stars > 0 ORDER BY stars DESC LIMIT 100");
		$query->execute();
	}
	if($type == "creators"){
		$query = $db->prepare("SELECT * FROM users WHERE isCreatorBanned = '0' AND creatorPoints > 0 ORDER BY creatorPoints DESC LIMIT 100");
		$query->execute();
	}
	if($type == "relative"){
		$query = "SELECT * FROM users WHERE extID = :accountID";
		$query = $db->prepare($query);
		$query->execute([':accountID' => $accountID]);
		$result = $query->fetchAll();
		
		// MasiVera: Handle empty result
		if(count($result) == 0) {
			exit("-1"); 
		}
		
		$user = $result[0];
		$stars = $user["stars"];
		if(!empty($_POST["count"])){
			$count = ExploitPatch::remove($_POST["count"]);
		}else{
			$count = 50;
		}
		$count = floor($count / 2);
		$query = $db->prepare("SELECT	A.* FROM	(
			(
				SELECT	*	FROM users
				WHERE stars <= :stars
				AND isBanned = 0
				AND gameVersion $sign
				ORDER BY stars DESC
				LIMIT $count
			)
			UNION
			(
				SELECT * FROM users
				WHERE stars >= :stars
				AND isBanned = 0
				AND gameVersion $sign
				ORDER BY stars ASC
				LIMIT $count
			)
		) as A
		ORDER BY A.stars DESC");
		$query->execute([':stars' => $stars]);
	}
	$result = $query->fetchAll();
	if($type == "relative"){
		$user = $result[0];
		$extid = $user["extID"];
		$e = "SET @rownum := 0;";
		$query = $db->prepare($e);
		$query->execute();
		$f = "SELECT rank, stars FROM (
							SELECT @rownum := @rownum + 1 AS rank, stars, extID, isBanned
							FROM users WHERE isBanned = '0' AND gameVersion $sign ORDER BY stars DESC
							) as result WHERE extID=:extid";
		$query = $db->prepare($f);
		$query->execute([':extid' => $extid]);
		$leaderboard = $query->fetchAll();
		$leaderboard = $leaderboard[0];
		$xi = $leaderboard["rank"] - 1;
	}
	foreach($result as &$user) {
		$xi++;
		$extid = is_numeric($user['extID']) ? $user['extID'] : 0;
		$lbstring .= "1:".$user["userName"].":2:".$user["userID"].":13:".$user["coins"].":17:".$user["userCoins"].":6:".$xi.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":51:".$user["color3"].":14:".$user["iconType"].":15:".$user["special"].":16:".$extid.":3:".$user["stars"].":8:".round($user["creatorPoints"],0,PHP_ROUND_HALF_DOWN).":4:".$user["demons"].":7:".$extid.":46:".$user["diamonds"].":52:".$user["moons"]."|";
	}
}

if($type == "friends"){
	$query = "SELECT * FROM friendships WHERE person1 = :accountID OR person2 = :accountID";
	$query = $db->prepare($query);
	$query->execute([':accountID' => $accountID]);
	$result = $query->fetchAll();
	$people = "";
	foreach ($result as &$friendship) {
		$person = $friendship["person1"];
		if($friendship["person1"] == $accountID){
			$person = $friendship["person2"];
		}
		$people .= ",".$person;
	}
	
	// MasiVera: Safe friend list query with empty check
	$allIds = $accountID . $people;
	if($allIds != "") {
		$placeholders = implode(',', array_fill(0, count(explode(',', $allIds)), '?'));
		$query = $db->prepare("SELECT * FROM users WHERE extID IN ($placeholders) ORDER BY stars DESC");
		$query->execute(explode(',', $allIds));
		$result = $query->fetchAll();
		
		foreach($result as &$user){
			if(is_numeric($user["extID"])){
				$extid = $user["extID"];
			}else{
				$extid = 0;
			}
			$xi++;
			$lbstring .= "1:".$user["userName"].":2:".$user["userID"].":13:".$user["coins"].":17:".$user["userCoins"].":6:".$xi.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":14:".$user["iconType"].":15:".$user["special"].":16:".$extid.":3:".$user["stars"].":8:".round($user["creatorPoints"],0,PHP_ROUND_HALF_DOWN).":4:".$user["demons"].":7:".$extid.":46:".$user["diamonds"]."|";
		}
	}
}

if($lbstring == ""){
	exit("-1");
}
$lbstring = substr($lbstring, 0, -1);
echo $lbstring;
?>