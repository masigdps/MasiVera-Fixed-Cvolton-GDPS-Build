<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/mainLib.php";
$mainLib = new mainLib();
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
require_once "../lib/commands.php";

// MasiVera: Get account ID securely (GJPCheck validation)
$accountID = GJPCheck::getAccountIDOrDie();

$userName = !empty($_POST['userName']) ? ExploitPatch::remove($_POST['userName']) : "";
$gameVersion = !empty($_POST['gameVersion']) ? ExploitPatch::number($_POST['gameVersion']) : 0;
$comment = ExploitPatch::remove($_POST['comment']);
$comment = ($gameVersion < 20) ? base64_encode($comment) : $comment;
$levelID = ($_POST['levelID'] < 0 ? '-' : '').ExploitPatch::number($_POST["levelID"]);
$percent = !empty($_POST["percent"]) ? ExploitPatch::remove($_POST["percent"]) : 0;

$id = $mainLib->getIDFromPost();
$register = is_numeric($id);
$userID = $mainLib->getUserID($id, $userName);
$uploadDate = time();
$decodecomment = base64_decode($comment);

// MasiVera: Anti-Spam (Rate limit: 1 comment per 30 seconds)
$checkSpam = $db->prepare("SELECT timeStamp FROM comments WHERE userID = :userID ORDER BY timeStamp DESC LIMIT 1");
$checkSpam->execute([':userID' => $userID]);
$lastComment = $checkSpam->fetchColumn();
if ($lastComment && (time() - $lastComment) < 30) {
    error_log("[MasiVera] SPAM: $userName tried to comment too fast.");
    exit("-1");
}

// MasiVera: Commands handling (only for registered users)
if($register && Commands::doCommands($id, $decodecomment, $levelID)){
    exit($gameVersion > 20 ? "temp_0_Command executed successfully!" : "-1");
}

if($id != "" AND $comment != ""){
    // MasiVera: Insert comment
    $query = $db->prepare("INSERT INTO comments (userName, comment, levelID, userID, timeStamp, percent) VALUES (:userName, :comment, :levelID, :userID, :uploadDate, :percent)");
    $query->execute([':userName' => $userName, ':comment' => $comment, ':levelID' => $levelID, ':userID' => $userID, ':uploadDate' => $uploadDate, ':percent' => $percent]);
    echo 1;
    
    // MasiVera: Update levelscores securely
    if($register && $percent > 0 && $percent <= 100){
        $query2 = $db->prepare("SELECT percent FROM levelscores WHERE accountID = :accountID AND levelID = :levelID");
        $query2->execute([':accountID' => $id, ':levelID' => $levelID]);
        $result = $query2->fetchColumn();
        
        if ($query2->rowCount() == 0) {
            $query = $db->prepare("INSERT INTO levelscores (accountID, levelID, percent, uploadDate) VALUES (:accountID, :levelID, :percent, :uploadDate)");
            $query->execute([':accountID' => $id, ':levelID' => $levelID, ':percent' => $percent, ':uploadDate' => $uploadDate]);
        } else {
            if($result < $percent){
                $query = $db->prepare("UPDATE levelscores SET percent=:percent, uploadDate=:uploadDate WHERE accountID=:accountID AND levelID=:levelID");
                $query->execute([':accountID' => $id, ':levelID' => $levelID, ':percent' => $percent, ':uploadDate' => $uploadDate]);
            }
        }
    }
} else {
    echo -1;
}
?>