<?php
	// Required values
	$id = $_POST["id"];
	$reason = (int)$_POST["reason"];
	$details = $_POST["details"] != "" ? $_POST["details"] : null;
	$ban = $_POST["ban"] != "" ? (int)$_POST["ban"] : null;

	// Basic user input validation (just check length)
	if (strlen($id) != 16 || ($reason < 0 || $reason > 9) || ($details != null && strlen($details) > 2000) || ($ban != null && ($ban < 0 || $ban > 2))) { http_response_code(400); exit("Invalid request."); }

	// Check ban ($ban is either null (no ban), 0 (48 hours), 1 (14 days) or 2 (permanently))
	if ($ban != null && $ban != 0 && $ban != 1 && $ban != 2) { http_response_code(400); exit("Invalid request."); }

	// Check if Post actually exists and has not been removed or deleted yet
	$statement = $database->prepare("SELECT COUNT(*) FROM `posts` WHERE `id` = CONV(?, 16, 10) AND (`v` IS NULL OR (NOT `v` = 1 AND NOT `v` = 2))");
	$statement->execute([$id]);
	if ($statement->fetchAll() == 0) { http_response_code(404); exit("Post either does not exist or has already been removed or deleted."); }

	// Move image, if there is one
	$image_move = archive_image($id, false);
	if ($image_move != true && $image_move != null) { http_response_code(500); exit("Submitting failed. Please try again later."); }

	try {
		// Remove specified Post
		$statement = $database->prepare("UPDATE `posts` SET `v` = 1 WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$id]);

		// Log removal
		$statement = $database->prepare("INSERT INTO `removals` (`post`, `reason`, `details`, `removedby`) VALUES (CONV(?, 16, 10), ?, ?, CONV(?, 16, 10));");
		$statement->execute([$id, $reason, $details, $uid]);

		// Ban if ban duration specified
		if ($ban !== null) {
			// Get ID of Removal
			$statement = $database->prepare("SELECT `id` FROM `removals` WHERE `post` = CONV(?, 16, 10) ORDER BY `datetime` DESC;");
			$statement->execute([$id]);
			$removal_id = $statement->fetchAll()[0][0];

			// Get UID and IP of Post author
			$statement = $database->prepare("SELECT `uid`, `ip` FROM `posts` WHERE `id` = CONV(?, 16, 10);");
			$statement->execute([$id]);
			$results = $statement->fetchAll();

			// Remove every post, if ban is permanent
			if ($ban == 2) {
				// Remove all Posts by author
				$statement = $database->prepare("UPDATE `posts` SET `v` = 1 WHERE `uid` = ?;");
				$statement->execute([$results[0][0]]);
			}

			// Create ban
			$statement = $database->prepare("INSERT INTO `bans` (`uid`, `ip`, `type`, `bannedby`, `reason`, `removal`) VALUES (?, ?, ?, CONV(?, 16, 10), ?, ?);");
			$statement->execute([$results[0][0], $results[0][1], $ban, $uid, $reason, $removal_id]);
		}

		// Get Board of Post and user ID of author
		$statement = $database->prepare("SELECT `board`, CONV(`uid`, 10, 16) FROM `posts` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$id]);
		$results = $statement->fetchAll();

		// Send notification to author of Post
		$notification = [
			"type" => "removed",
			"attachment" => [
				"board" => $results[0][0],
				"post" => $id
			]
		];
		sendNotification($database, [$results[0][1]], json_encode($notification));
	} catch (PDOException $exception) { http_response_code(500); exit("Submitting failed. Please try again later. ".$exception); }

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK");
?>