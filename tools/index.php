<?php
session_start();
include "../incl/lib/connection.php";
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();

// MasiVera: Check if user is admin
if(!isset($_SESSION["accountID"]) || !$gs->checkPermission($_SESSION["accountID"], "dashboardModTools")) {
    // If not admin, show a 404 or redirect
    header("Location: /");
    exit();
}

function listdir($dir){
    $dirstring = "";
    $files = scandir($dir);
    foreach($files as $file) {
        if(pathinfo($file, PATHINFO_EXTENSION) == "php" AND $file != "index.php"){
            $safeFile = htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); // MasiVera: XSS protection
            $dirstring .= "<li><a href='$dir/$safeFile'>$safeFile</a></li>";
        }
    }
    return $dirstring;
}

echo '<h1>MasiVera Tools</h1>';
echo '<a href="../dashboard">Check out the dashboard beta here</a>';
echo '<h1>Account management tools:</h1><ul>';
echo listdir("account");
echo '</ul><h1>Upload related tools:</h1><ul>';
echo listdir(".");
echo "</ul><h1>The cron job (fixing CPs, autoban, etc.)</h1><ul>";
echo "<li><a href='cron/cron.php'>cron.php</a></li>";
echo "</ul><h1>Stats related tools</h1><ul>";
echo listdir("stats");
echo "</ul>";
?>
// fix