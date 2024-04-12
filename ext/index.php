<?php
	// General files
	if (strstr($url, "/ext/cookies.js") === $url) {
		header("Content-Type: text/javascript");
		readfile("ext/cookies.js");
		exit();
	} else if (strstr($url, "/ext/huechan.png") === $url) {
		header("Content-Type: image/png");
		readfile("ext/huechan.png");
		exit();
	}

	// User needs to be authorized at this point, so check if they are
	if ($uid == null || $authkey == null || !checkIfUIDIsTaken($database, $uid) || !checkIfAuthorized($database, $authkey, $uid)) { http_response_code(401); exit(); }

	// Create support tickets, should be accessed by everyone (ban appeals, etc.)
	if ($url == "/ext/create_support_ticket") {
		include("ext/create_support_ticket.php");
		exit();
	} else if ($url == "/ext/verify_support_ticket") {
		include("ext/verify_support_ticket.php");
		exit();
	}

	if ($user_is_banned && getBanType($database, $uid, $user_ip) == 2) { http_response_code(403); exit("You are currently banned from using Huechan."); }

	// List and delete own posts, should be accessed by everyone but permanently banned users as all their posts get removed anyway when banning
	if (strstr($url, "/ext/get_my_posts") === $url) {
		include("ext/get_my_posts.php");
		exit();
	} else if ($url == "/ext/delete_post") {
		include("ext/delete_post.php");
		exit();
	}

	if ($user_is_banned) { http_response_code(403); exit("You are currently banned from using Huechan."); }

	// Images of removed Posts. Check if user has access before transmitting file
	if (strstr($url, "/ext/images_archived/") === $url) {
		if ($url != "/ext/images_archived/" && preg_match("", substr($url, 21)) && $user_role == null) {
			$statement = $database->prepare("SELECT COUNT(`id`) FROM `posts` WHERE `id` = CONV(?, 16, 10) AND `v` = 1");
		}
	}

	// AJAX actions
	if ($url == "/ext/create_post") {
		include("ext/create_post.php");
		exit();
	} else if (strstr($url, "/ext/get_post?") === $url) {
		include("ext/get_post.php");
		exit();
	} else if (strstr($url, "/ext/get_thread?") === $url) {
		include("ext/get_thread.php");
		exit();
	} else if (strstr($url, "/ext/get_timeline?") === $url) {
		include("ext/get_timeline.php");
		exit();
	} else if (strstr($url, "/ext/filter_posts?") === $url) {
		include("ext/filter_posts.php");
		exit();
	} else if ($url == "/ext/create_report") {
		include("ext/create_report.php");
		exit();
	} else if ($url == "/ext/vote_in_poll") {
		include("ext/vote_in_poll.php");
		exit();
	} else if ($url == "/ext/fetch_notifications") {
		include("ext/fetch_notifications.php");
		exit();
	}

	// User needs to be staff at this point, so check if they are
	if ($user_role === null) {
		http_response_code(404);
		exit();
	}

	// Script for staff actions. Check if user is staff before transmitting file
	if ($url == "/ext/modactions.js") {
		header("Content-Type: text/javascript");
		readfile("ext/modactions.js");
		exit();
	}

	// Staff AJAX actions
	if ($url == "/ext/mod_remove_post") {
		include("ext/mod_remove_post.php");
		exit();
	} else if ($url == "/ext/mod_review_report") {
		include("ext/mod_review_report.php");
		exit();
	} else if (strstr($url, "/ext/mod_get_reports?") === $url) {
		include("ext/mod_get_reports.php");
		exit();
	}

	http_response_code(404);
	exit();
?>
