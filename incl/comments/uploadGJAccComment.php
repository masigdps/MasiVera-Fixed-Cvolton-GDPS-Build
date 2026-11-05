<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
require_once "../lib/mainLib.php";
require_once "../lib/commands.php";
$mainLib = new mainLib();

// MasiVera: Secure authentication
$accountID = GJPCheck::getAccountIDOrDie();
$userName = ExploitPatch::remove($_POST["userName"]);
$comment = ExploitPatch::remove($_POST["comment"]);
$userID = $mainLib->getUserID($accountID, $userName);
$uploadDate = time();

if($accountID != "" AND $comment != ""){
    
    // MasiVera: Anti-Spam (Rate limit: 1 comment per 30 seconds)
    $checkSpam = $db->prepare("SELECT timeStamp FROM acccomments WHERE userID = :userID ORDER BY timeStamp DESC LIMIT 1");
    $checkSpam->execute([':userID' => $userID]);
    $lastComment = $checkSpam->fetchColumn();
    
    if ($lastComment && (time() - $lastComment) < 30) {
        error_log("[MasiVera] SPAM: $userName tried to post profile comments too fast.");
        exit("-1");
    }

    $decodecomment = base64_decode($comment);
    
    // MasiVera: Execute commands (only for registered users)
    if(Commands::doProfileCommands($accountID, $decodecomment)){
        exit("-1");
    }
    
    // MasiVera: Insert comment
    $query = $db->prepare("INSERT INTO acccomments (userName, comment, userID, timeStamp) VALUES (:userName, :comment, :userID, :uploadDate)");
    $query->execute([':userName' => $userName, ':comment' => $comment, ':userID' => $userID, ':uploadDate' => $uploadDate]);
    echo 1;
} else {
    echo -1;
}
?>