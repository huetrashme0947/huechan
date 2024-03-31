<?php
	$id = $_POST["id"];
	$status = $_POST["status"] == "true";
	$keep_details = $_POST["keep_details"] == "true";
	$ban = $_POST["ban"] != "" ? (int)$_POST["ban"] : null;

	// Check if ban is actually valid
	if ($ban !== null && ($ban < 0 || $ban > 2)) { http_response_code(400); exit("Invalid request."); }

	// Check if Report exists and has not been reviewed yet
	$statement = $database->prepare("SELECT `reason`, `details`, `post`, `uid`, `ip` FROM `reports` WHERE `id` = ? AND `status` IS NULL;");
	$statement->execute([$id]);
	$results_report = $statement->fetchAll();
	if ($results_report == []) { http_response_code(404); exit("Report does not exist or has already been reviewed."); }

	// Check if Post exists and has not been removed or deleted yet
	$statement = $database->prepare("SELECT `uid`, `ip` FROM `posts` WHERE `id` = ? AND (`v` IS NULL OR `v` = 0)");
	$statement->execute([$results_report[0][2]]);
	$results_post = $statement->fetchAll();
	if ($results_post == []) {
		// If Post already deleted or removed, reject report
		$statement = $database->prepare("UPDATE `reports` SET `status` = 0 WHERE `id` = ?;");
		$statement->execute([$id]);
		http_response_code(409);
		exit("The reported Post has already been removed by staff or deleted by the author itself. The Report has now been automatically rejected and no further action is required.");
		// todo: remove all corresponding reports automatically when deleting or removing a post (modification of delete_post.php)
	}

	// Set Report status
	$statement = $database->prepare("UPDATE `reports` SET `status` = ? WHERE `id` = ?;");
	$statement->execute([(int)$status, $id]);

	// Remove Post and log removal, if Report was approved
	if ($status) {
		$statement = $database->prepare("UPDATE `posts` SET `v` = 1 WHERE `id` = ?; INSERT INTO `removals` (`post`, `reason`, `details`, `removedby`) VALUES (?, ?, ?, CONV(?, 16, 10)); SELECT `id` FROM `removals` WHERE `post` = ? ORDER BY `datetime` DESC;");
		$statement->execute([$results_report[0][2], $results_report[0][2], $results_report[0][0], ($keep_details ? $results_report[0][1] : null), $uid, $results_report[0][2]]);
		$removal_id = $statement->fetchAll()[0][0];
	}

	// Ban Poster or reporting user, if ban is provided
	if ($ban !== null) {
		$statement = $database->prepare("INSERT INTO `bans` (`uid`, `ip`, `type`, `bannedby`, `reason`, `removal`) VALUES (?, ?, ?, CONV(?, 16, 10), ?, ?);");
		$statement->execute([($status ? $results_post[0][0] : $results_report[0][3]), ($status ? $results_post[0][1] : $results_report[0][4]), $ban, $uid, ($status ? $results_report[0][0] : 10), ($status ? $removal_id : null)]);
	}

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK");
?>