<?php
include "../config/security.php";
include "../incl/lib/connection.php";
require_once "../incl/lib/exploitPatch.php";
require "../incl/lib/generatePass.php";

// ==========================================
// MasiVera: Account Registration
// ==========================================

// Set to false if you have SMTP configured and want email activation.
// Set to true if you want instant login (no email required).
if(!isset($preactivateAccounts)){
	$preactivateAccounts = true;
}

define('MV_MAX_ACCOUNTS_PER_IP_PER_DAY', 3);
define('MV_MIN_SECONDS_BETWEEN_REGISTRATIONS', 600); // 10 minutes

function mv_log($msg) {
	error_log('[MasiVera] registerGJAccount: ' . $msg);
}

function mv_client_ip() {
	// Cloudflare Support
	if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
		return $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

if(isset($_POST["userName"]) && $_POST["userName"] != ""){
	$userName = ExploitPatch::remove($_POST["userName"]);
	$password = ExploitPatch::remove($_POST["password"]);
	$email    = ExploitPatch::remove($_POST["email"]);
	$ip       = mv_client_ip();

	// 1. Validate Input
	if(strlen($userName) > 20 || strlen($userName) < 3) {
		exit("-4");
	}
	if(strlen($password) < 6) {
		exit("-8");
	}

	// 2. IP Rate Limiting
	try {
		$q = $db->prepare("SELECT COUNT(*) FROM registration_ips WHERE ip = :ip AND timestamp > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))");
		$q->execute([':ip' => $ip]);
		if((int)$q->fetchColumn() >= MV_MAX_ACCOUNTS_PER_IP_PER_DAY) {
			mv_log("Daily IP limit reached: $ip");
			exit("-1");
		}

		$q = $db->prepare("SELECT timestamp FROM registration_ips WHERE ip = :ip ORDER BY timestamp DESC LIMIT 1");
		$q->execute([':ip' => $ip]);
		$last = $q->fetchColumn();
		if($last !== false && (time() - (int)$last) < MV_MIN_SECONDS_BETWEEN_REGISTRATIONS) {
			mv_log("IP cooldown active: $ip");
			exit("-1");
		}
	} catch (PDOException $e) {
		mv_log("IP check failed: " . $e->getMessage());
		exit("-1");
	}

	// 3. Check if Username exists
	$query2 = $db->prepare("SELECT count(*) FROM accounts WHERE userName = :userName");
	$query2->execute([':userName' => $userName]);
	if ($query2->fetchColumn() > 0) {
		echo "-2"; // Username taken
	} else {
		// 4. Create Account
		$hashpass = password_hash($password, PASSWORD_DEFAULT);
		$gjp2 = GeneratePass::GJP2hash($password);

		try {
			$query = $db->prepare("INSERT INTO accounts (userName, password, email, registerDate, isActive, gjp2, ip) VALUES (:userName, :password, :email, :time, :isActive, :gjp, :ip)");
			$query->execute([
				':userName' => $userName, 
				':password' => $hashpass, 
				':email'    => $email, 
				':time'     => time(), 
				':isActive' => $preactivateAccounts ? 1 : 0, 
				':gjp'      => $gjp2,
				':ip'       => $ip
			]);

			// 5. Log IP for Rate Limiting (Don't break registration if this fails)
			try {
				$q = $db->prepare("INSERT INTO registration_ips (ip, timestamp) VALUES (:ip, :ts)");
				$q->execute([':ip' => $ip, ':ts' => time()]);
			} catch (PDOException $e) {
				mv_log("Failed to log IP: " . $e->getMessage());
			}

			echo "1"; // Success
		} catch (PDOException $e) {
			mv_log("INSERT failed: " . $e->getMessage());
			exit("-1");
		}
	}
}
?>