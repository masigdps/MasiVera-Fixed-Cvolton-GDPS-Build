<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
require_once "../incl/lib/exploitPatch.php";
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
$ip = $gs->getIP();
$udid = ExploitPatch::remove($_POST["udid"]);
$userName = ExploitPatch::remove($_POST["userName"]);

// MasiVera: Get account info including isActive
$query = $db->prepare("SELECT accountID, isActive FROM accounts WHERE userName LIKE :userName");
$query->execute([':userName' => $userName]);
if($query->rowCount() == 0){
    exit("-1");
}
$accountData = $query->fetch();
$id = $accountData['accountID'];

// MasiVera: Check if account is activated
if($accountData['isActive'] == 0){
    error_log("[MasiVera] LOGIN FAILED: $userName tried to login but account is not activated.");
    exit("-1"); // Or specific error code for "not activated"
}

$pass = 0;
if(!empty($_POST["password"])) $pass = GeneratePass::isValidUsrname($userName, $_POST["password"]);
elseif(!empty($_POST["gjp2"])) $pass = GeneratePass::isGJP2ValidUsrname($userName, $_POST["gjp2"]);

if ($pass == 1) { // success
    // MasiVera: Get userID
    $query2 = $db->prepare("SELECT userID FROM users WHERE extID = :id");
    $query2->execute([':id' => $id]);
    
    if ($query2->rowCount() > 0) {
        $userID = $query2->fetchColumn();
    } else {
        $query = $db->prepare("INSERT INTO users (isRegistered, extID, userName) VALUES (1, :id, :userName)");
        $query->execute([':id' => $id, ':userName' => $userName]);
        $userID = $db->lastInsertId();
    }
    
    // MasiVera: Log the action
    $query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES ('2', :username, :time, :ip)");
    $query6->execute([':username' => $userName, ':time' => time(), ':ip' => $ip]);
    
    echo $id.",".$userID;
    
    // MasiVera: Migration logic
    if(!is_numeric($udid)){
        $query2 = $db->prepare("SELECT userID FROM users WHERE extID = :udid");
        $query2->execute([':udid' => $udid]);
        $usrid2 = $query2->fetchColumn();
        $query2 = $db->prepare("UPDATE levels SET userID = :userID, extID = :extID WHERE userID = :usrid2");
        $query2->execute([':userID' => $userID, ':extID' => $id, ':usrid2' => $usrid2]);    
    }
} elseif ($pass == -1) { // failure
    echo -12;
} else {
    echo -1;
}
?>