<?php
	$by_board = isset($_GET["by_board"]) ? $_GET["by_board"] : null;
	$by_text = isset($_GET["by_text"]) ? trim($_GET["by_text"]) : null;
	$by_inreplyto = isset($_GET["by_inreplyto"]) ? $_GET["by_inreplyto"] : null;
	$noreplies = isset($_GET["noreplies"]) ? (bool)$_GET["noreplies"] : false;
	$offset_time = isset($_GET["offset_time"]) ? date("Y-m-d H:i:s", time($_GET["offset_time"])) : date("Y-m-d H:i:s");
	$return_full = (bool)$_GET["return_full"];

	// At least one of the search factors have to be given. If none is, reject
	if ($by_board == null && ($by_text == null || $by_text == "") && $by_inreplyto == null) { exit("Invalid request."); }

	// Connect to database
	$database = connect_database();
	if ($database === false) { exit("Submitting failed. Please try again later."); }

	// Check if UID is taken and abort if not
	if (!checkIfUIDIsTaken($database, $uid)) { exit("Invalid request."); }

	// Check if user is actually authorized to Post with provided UID
	if (!checkIfAuthorized($database, $authkey, $uid)) { exit("Invalid request."); }

	// Check if user is banned
	if (checkIfBanned($database, $uid, $user_ip)) { exit("You are currently banned from using HueChan."); }

	// Build Query
	$query = "SELECT CONV(`id`, 10, 16) FROM `posts` WHERE ";
	$params = array();
	if ($by_board != null) {
		$query .= "`board` = ? AND ";
		$params[] = $by_board;

		// Get pinned Post from Board
		$statement = $database->prepare("SELECT `pinned` FROM `boards` WHERE `id` = ?;");
		$statement->execute([$by_board]);
		$board_pinned = $statement->fetchAll()[0][0];
		if ($board_pinned != null) {
			$query .= "NOT `id` = ? AND ";
			$params[] = $board_pinned;
		}
	} if ($by_text != null) {
		$query .= "(`content` LIKE CONCAT('%', ?, '%') OR `name` LIKE CONCAT('%', ?, '%')) AND ";
		$params[] = $by_text;
		$params[] = $by_text;
	} if ($by_inreplyto != null) {
		$query .= "`inreplyto` = CONV(?, 16, 10) AND ";
		$params[] = $by_inreplyto;
	}

	if ($offset_time != null) {
		$query .= "`datetime` < ? AND ";
		$params[] = $offset_time;
	}

	if ($noreplies) {
		$query .= "`inreplyto` IS NULL AND ";
	}

	$query .= "(`v` IS NULL OR `v` = 0) ORDER BY `datetime` DESC LIMIT 20;";

	// Filter Posts from database
	$statement = $database->prepare($query);
	$statement->execute($params);
	$results = $statement->fetchAll();

	if (sizeof($results) < 20) { $ret_last = true; }

	$posts = array();
	foreach ($results as $column) { $posts[] = correctIDOutputFromDB($column[0]); }

	if ($return_full) {
		$post_assocs = array();
		foreach ($posts as $post) {
			$post_assoc = getPostAssoc($database, $post, $uid, true);
			if ($post_assoc != null && $post_assoc != 0) {
				$post_assocs[] = $post_assoc;
			}
		}
		$posts = $post_assocs;
	}
	if ($ret_last) { $posts[] = "last"; }

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK ".json_encode($posts));
?>