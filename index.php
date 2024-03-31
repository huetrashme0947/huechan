<?php
	// Exception handler
	// [!!!] IMPORTANT: REMOVE EXCEPTION OUTPUT WHEN OUT OF BETA PHASE, COULD AND WILL OUTPUT SENSITIVE DATA
	function quit_with_error($exception) {
		http_response_code(500);
		exit("Request was either invalid or an internal error occured. Please try again later. $exception");
	}
	set_exception_handler("quit_with_error");

	require "_database.php";
	require "_authentification.php";
	require "_upload_image.php";
	require "_posts.php";

	// Establish database connection
	$database = connect_database();
	if ($database === false) { throw new Exception("", 1); }

	$user_ip = $_SERVER["REMOTE_ADDR"];
	$user_ip_xff = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : null;

	// If X-Forwarded-For header is given, check if it's a valid WAN IP
	if ($user_ip_xff != null && !filter_var($user_ip_xff, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_IPV4)) { http_response_code(403); exit("Proxy error."); }

	// Check for maintenance mode
	$statement = $database->prepare("SELECT `value` FROM `flags` WHERE `name` = 'maintenance_full'");
	$statement->execute();
	if ($statement->fetchAll()[0][0] == 1) {
		include("frontend/maintenance.php");
		exit();
	}

	$maintenance_mode = false;

	// Check if new user
	$is_new_user = checkIfNewUser();
	if (checkIfNewUser()) {
		$uid = null;
		$authkey = null;
	} else if (!checkIfAuthorized($database, $_COOKIE["aph"], $_COOKIE["uu"])) {
		http_response_code(401);
		exit();
	} else {
		$uid = $_COOKIE["uu"];
		$authkey = $_COOKIE["aph"];
	}

	$url = $_SERVER["REQUEST_URI"];
	$user_is_banned = checkIfBanned($database, $uid, $user_ip);
	$user_role = checkRole($database, $uid);

	// Check for limited maintenance mode
	$statement = $database->prepare("SELECT `value` FROM `flags` WHERE `name` = 'maintenance_limited'");
	$statement->execute();
	if ($statement->fetchAll()[0][0] == 1) {
		if ($user_role === null) {
			include("frontend/maintenance.php");
			exit();
		}
		$maintenance_mode = true;
	}

	// Generate UID, should be able to be accessed by everyone
	if ($url == "/ext/generate_aph") {
		include("ext/generate_aph.php");
		exit();
	}

	// /ext/ resources
	if (strstr($url, "/ext/") === $url) {
		include("ext/index.php");
		exit();
	}

	// Essential pages, should be able to be accessed by everyone
	if ($url == "/contact") {
		include("frontend/contact.php");
		exit();
	} else if ($url == "/rules") {
		include("frontend/rules.php");
		exit();
	} else if ($url == "/support") {
		include("frontend/support.php");
		exit();
	} else if ($url == "/help") {
		include("frontend/help.php");
		exit();
	} else if ($url == "/about") {
		include("frontend/about.php");
		exit();
	}

	// Restrict access to only some parts of site, if user is banned
	if ($user_is_banned) {
		if (preg_match("/^\/[a-z0-9]{1,8}\/[0-9a-f]{16}$/", $url)) {
			// Extract Board and Post ID out of URL
			$results = array();
			preg_match("/[a-z0-9]{1,8}/", $url, $results);
			$board_id = $results[0];
			preg_match("/[0-9a-f]{16}$/", $url, $results);
			$post_id = $results[0];

			// User wants to access a Post. Only allow this, if they are the author
			$statement = $database->prepare("SELECT COUNT(*) FROM `posts` WHERE `id` = CONV(?, 16, 10) AND `uid` = CONV(?, 16, 10) AND `board` = ? AND (`v` IS NULL OR NOT `v` = 2);");
			$statement->execute([$post_id, $uid, $board_id]);
			if ($statement->fetchAll()[0][0] == 1) {
				include("frontend/post.php");
				exit();
			}
		} else if (getBanType($database, $uid, $user_ip) != 2 && $url == "/myposts") {
			include("frontend/myposts.php");
			exit();
		} else if ($url == "/") {
			include("frontend/banned.php");
			exit();
		}

		// If user has visited any other URL, redirect to root to show them the Banned notice
		header("Location: /");
		exit();
	}

	// Only to be accessed by users who are not banned permanently
	if ($url == "/myposts") {
		include("frontend/myposts.php");
		exit();
	}

	// If neither banned nor wanted to access essential pages
	if ($url == "/") {
		include("frontend/home.php");
		exit();
	} else if (preg_match("/^\/[a-z0-9]{1,8}\/[0-9a-f]{16}$/", $url)) {
		// Extract Board and Post ID out of URL
		$results = array();
		preg_match("/[a-z0-9]{1,8}/", $url, $results);
		$board_id = $results[0];
		preg_match("/[0-9a-f]{16}$/", $url, $results);
		$post_id = $results[0];

		// Check if Post actually exists and is on selected Board.
		$statement = $database->prepare("SELECT `v`, CONV(`uid`, 10, 16) FROM `posts` WHERE `id` = CONV(?, 16, 10) AND `board` = ? AND (`v` IS NULL OR NOT `v` = 2);");
		$statement->execute([$post_id, $board_id]);
		$results = $statement->fetchAll();
		if ($results != []) {
			include("frontend/post.php");
			exit();
		}
	} else if (preg_match("/^\/[a-z0-9]{1,8}\/$/", $url)) {
		// Extract Board ID out of URL
		$results = array();
		preg_match("/[a-z0-9]{1,8}/", $url, $results);
		$board_id = $results[0];

		// Check if Board actually exists
		$statement = $database->prepare("SELECT COUNT(*) FROM `boards` WHERE `id` = ?;");
		$statement->execute([$board_id]);
		if ($statement->fetchAll()[0][0] == 1) {
			include("frontend/board.php");
			exit();
		}
	} else if ($url == "/boards") {
		include("frontend/boards.php");
		exit();
	} else if ($url == "/search" || strstr($url, "/search?") === $url) {
		if ($url == "/search") { header("Location: /"); exit(); }
		// Global search
		include("frontend/search.php");
		exit();
	} else if (preg_match("/^\/[a-z0-9]{1,8}\/search(\?|$)/", $url)) {
		// Extract Board ID out of URL
		$results = array();
		preg_match("/[a-z0-9]{1,8}/", $url, $results);
		$board_id = $results[0];

		if (preg_match("/^\/[a-z0-9]{1,8}\/search$/", $url)) { header("Location: /".$board_id."/"); exit(); }

		// Check if Board actually exists
		$statement = $database->prepare("SELECT COUNT(*) FROM `boards` WHERE `id` = ?;");
		$statement->execute([$board_id]);
		if ($statement->fetchAll()[0][0] == 1) {
			// Board search
			include("frontend/search.php");
			exit();
		}
	}

	// Mod-only pages
	if ($user_role !== null && $url == "/mod_reports") {
		include("frontend/mod_reports.php");
		exit();
	}

	http_response_code(404);
	include("frontend/404.php");
	exit();
?>
