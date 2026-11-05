<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
require_once "../lib/mainLib.php";
$mainLib = new mainLib();

$levelID = ExploitPatch::remove($_POST["levelID"]);
$accountID = GJPCheck::getAccountIDOrDie();

// MasiVera: Validate levelID
if(!is_numeric($levelID)){
	exit("-1");
}

$userID = $mainLib->getUserID($accountID);

// MasiVera: Fixed starStars check (allow NULL values) and check deletion success
$query = $db->prepare("DELETE FROM levels WHERE levelID = :levelID AND userID = :userID AND (starStars = 0 OR starStars IS NULL) LIMIT 1");
$query->execute([':levelID' => $levelID, ':userID' => $userID]);

// MasiVera: Only log action if level was actually deleted
if($query->rowCount() > 0) {
    $query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES (:type, :itemID, :time, :ip)");
    $query6->execute([':type' => 8, ':itemID' => $levelID, ':time' => time(), ':ip' => $userID]);
    
    if(file_exists("../../data/levels/$levelID")){
        if(!is_dir("../../data/levels/deleted")) {
            mkdir("../../data/levels/deleted", 0777, true);
        }
        rename("../../data/levels/$levelID", "../../data/levels/deleted/$levelID");
    }
    echo "1";
} else {
    echo "-1"; // Return error if level not found or not owned by user
}
?>