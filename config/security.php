<?php
// MasiVera: Security Configuration

$sessionGrants = false; // false = GJP check is done every time; true = GJP check is done once per hour; significantly improves performance, slightly decreases security
$unregisteredSubmissions = false; // false = green accounts can't upload levels, appear on the leaderboards etc; true = green accounts can do everything
$preactivateAccounts = true; // false = accounts need to be activated at tools/account/activateAccount.php; true = accounts can log in immediately

// MasiVera: Reupload Tool Switch (0 = Off, 1 = On)
// WARNING: Enable this only if you trust your moderators.
define('MV_ALLOW_REUPLOAD', 0);

/*
	Captcha settings
	Currently the only supported provider is hCaptcha
	https://www.hcaptcha.com/
*/
$enableCaptcha = false;
$hCaptchaKey = "";
$hCaptchaSecret = "";