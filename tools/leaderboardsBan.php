<?php
session_start();
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
require_once "../incl/lib/exploitPatch.php";
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();

// MasiVera: Check if user is logged in
if(!isset($_SESSION["accountID"])) {
    exit("You must be logged in to use this tool.");
}

$myAccountID = $_SESSION["accountID"];

// MasiVera: Check permission first
if(!$gs->checkPermission($myAccountID, "toolLeaderboardsban")) {
    exit("You do not have the permission to do this action.");
}

if(!empty($_POST["userID"])){
    $userID = ExploitPatch::remove($_POST["userID"]);
    
    if(!is_numeric($userID)){
        exit("Invalid userID");
    }
    
    // MasiVera: Ban user
    $query = $db->prepare("UPDATE users SET isBanned = 1 WHERE userID = :id");
    $query->execute([':id' => $userID]);
    
    if($query->rowCount() != 0){
        echo "Banned successfully.";
        
        // MasiVera: Log action
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, timestamp, account) VALUES ('15', :userID, '1', :timestamp, :account)");
        $query->execute([':userID' => $userID, ':timestamp' => time(), ':account' => $myAccountID]);
    }else{
        echo "Ban failed. User not found or already banned.";
    }
}else{
    echo '<form action="leaderboardsBan.php" method="post">
        Target UserID: <input type="text" name="userID">
        <br><input type="submit" value="Ban"></form>';
}
?>