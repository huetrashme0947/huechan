<?php
	function getReportAssoc($database, $id, $explicit = false) {
		// Get Report. Fail, if no Report found. Also fail, if Report has already been reviewed and explicit flag is not set
		$statement = $database->prepare("SELECT `reason`, `details`, CONV(`post`, 10, 16), `uid`, `ip`, `datetime`, `status` FROM `reports` WHERE `id` = ?;");
		$statement->execute([$id]);
		$results = $statement->fetchAll();
		if ($results == []) { return null; }
		if ($results[0][6] != null && !$explicit) { return 0; }

		// Create assoc aray for return
		$report = [
			"id" => $id,
			"reason" => $results[0][0],
			"details" => htmlentities($results[0][1]),
			"post" => correctIDOutputFromDB($results[0][2]),
			"datetime" => gmdate("D, d M Y H:i:s", DateTime::createFromFormat("Y-m-d H:i:s", $results[0][5])->getTimestamp())." GMT",
			"status" => $results[0][6]
		];

		// If Post is given, get Board ID and name
		$statement = $database->prepare("SELECT `board`, `boards`.`name` FROM `posts` INNER JOIN `boards` ON `board` = `boards`.`id` WHERE `posts`.`id` = CONV(?, 16, 10);");
		$statement->execute([$results[0][2]]);
		$results_post = $statement->fetchAll();
		$report["board"] = $results_post[0][0];
		$report["boardname"] = $results_post[0][1];

		// Get number of times, user has already abused Reporting system
		$statement = $database->prepare("SELECT COUNT(*) FROM `bans` WHERE `reason` = 10 AND (`uid` = ? OR `ip` = ?)");
		$statement->execute([$results[0][3], $results[0][4]]);
		$report["past_bans"] = $statement->fetchAll()[0][0];

		return $report;
	}

	function getPostAssoc($database, $id, $uid, $root = true, $explicit = false) {
		// Get Post. Fail, if no Post found or deleted
		$statement = $database->prepare("SELECT CONV(`id`, 10, 16), `board`, CONV(`inreplyto`, 10, 16), `content`, `name`, `datetime`, CONV(`uid`, 10, 16), `v` FROM `posts` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$id]);
		$results = $statement->fetchAll();
		if ($results == [] || $results[0][7] == 2) { return null; }

		// Get roles of user asking for post and author of post
		$user_role = checkRole($database, $uid);
		$author_role = checkRole($database, $results[0][6]);
		$author_uid = $results[0][6];

		// If Post is removed, check if User is either staff or the author. Deny access anyway, if !$explicit (only grant access if user is specifically asking for this Post)
		if (($results[0][7] == 1 && !$explicit) || ($results[0][7] == 1 && $user_role === null && correctIDOutputFromDB($results[0][6]) != $uid)) { return 0; }

		// Create assoc array for return
		$post = [
			"id" => $id,
			"board" => $results[0][1],
			"inreplyto" => correctIDOutputFromDB($results[0][2]),
			"contentRaw" => htmlentities($results[0][3]),
			"content" => formatPostContent(htmlentities($results[0][3])),
			"name" => htmlentities($results[0][4]),
			"datetime" => gmdate("D, d M Y H:i:s", DateTime::createFromFormat("Y-m-d H:i:s", $results[0][5])->getTimestamp())." GMT",
			"flags" => [],
			"groups" => []
		];

		// Check if user is either the author or staff. If staff, check if post is not from another user with higher or equal status
		if (correctIDOutputFromDB($results[0][6]) == $uid && $results[0][7] != 1) { $post["flags"][] = "author"; }
		else if (($user_role > $author_role) || ($user_role !== null && $author_role === null)) { $post["flags"][] = "operator"; }

		// Check if staff
		if ($author_role !== null) {
			$post["flags"][] = "staff";
		}

		// Return image path
		$post["image"] = get_image_path($id, $root);

		// If replying is disabled or the Post was removed, add that
		if ($results[0][7] !== null) { $post["flags"][] = "noreplies"; }
		if ($results[0][7] == 1) { $post["flags"][] = "removed"; }

		// Check if pinned and get name of Board
		$statement = $database->prepare("SELECT CONV(`pinned`, 10, 16), `name` FROM `boards` WHERE `id` = ?;");
		$statement->execute([$results[0][1]]);
		$results = $statement->fetchAll();
		if ($results[0][0] == $id) { $post["flags"][] = "pinned"; }
		$post["boardname"] = $results[0][1];

		// Get number of replies
		$statement = $database->prepare("SELECT COUNT(*) FROM `posts` WHERE `inreplyto` = CONV(?, 16, 10) AND (`v` IS NULL OR `v` = 0);");
		$statement->execute([$id]);
		$results = $statement->fetchAll();
		$post["replies"] = $results[0][0];

		// Check if poll attached
		$poll = getPollAssoc($database, $id, (checkIfUserHasVotedInPoll($database, $post["id"], $uid) !== false || checkIfPollHasEnded($database, $post["id"])));
		if ($poll != null) { $post["poll"] = $poll; }

		// Get groups
		$post["groups"] = getGroupDetails($database, getGroups($database, $author_uid));

		return $post;
	}

	function getRemovalAssoc($database, $post_id) {
		// Get removal. Fail, if no removal found
		$statement = $database->prepare("SELECT `id`, `reason`, `details`, `datetime` FROM `removals` WHERE `post` = CONV(?, 16, 10);");
		$statement->execute([$post_id]);
		$results = $statement->fetchAll();
		if ($results == []) { return null; }

		// Create assoc array for return
		$removal = [
			"id" => $results[0][0],
			"reason" => $results[0][1],
			"details" => $results[0][2],
			"datetime" => gmdate("D, d M Y H:i:s", DateTime::createFromFormat("Y-m-d H:i:s", $results[0][3])->getTimestamp())." GMT"
		];

		return $removal;
	}

	function shortenContent($text) {
		if (strlen($text) < 500) { return $text; }
		$word_array = explode(" ", $text);
		$return_str = "";
		for ($i=0; $i < sizeof($word_array); $i++) { 
			if (strlen($return_str.$word_array[$i]." ") > 500) {
				break;
			}
			$return_str .= $word_array[$i]." ";
		}
		return substr($return_str, 0, -1);
	}

	function getTimeString($time) {
		$time_diff = time() - $time;
		if ($time_diff == 0) { return "now"; }
		if ($time_diff < 60) { return $time_diff+" second".($time_diff > 2 ? "s" : "")." ago"; }
		if ($time_diff < 3600) { return (int)($time_diff/60)." minute".($time_diff > 120 ? "s" : "")." ago"; }
		if ($time_diff < 86400) { return (int)($time_diff/3600)." hour".($time_diff > 7200 ? "s" : "")." ago"; }
		if ($time_diff < 604800) { return (int)($time_diff/86400)." day".($time_diff > 172800 ? "s" : "")." ago"; }
		return date("Y/m/d g:i A", $time);
	}

	function getRemainingTimeString($time) {
		$time_diff = $time - time();
		if ($time_diff < 0) { return "ended"; }
		if ($time_diff < 60) { return $time_diff."s remaining"; }
		if ($time_diff < 3600) { return (int)($time_diff/60)."min remaining"; }
		if ($time_diff < 86400) { return (int)($time_diff/3600)."h remaining"; }
		return (int)($time_diff/86400)."d remaining";
	}

	function formatPostContent($text) {
		# Replace all line breaks with <br>
		$text = str_replace("\n", "<br>", $text);
		
		# Format greentext
		$text = preg_replace_callback("/(?<=^|<br>)&gt;.*?(?=$|<br>)/", function ($match) { return "<span class=\"text-success\">".$match[0]."</span>"; }, $text);
		
		# Format links
		$text = preg_replace_callback("/(?<= |<br>|^)(http(?:s)?:\/\/)((?:[a-zA-Z0-9]+\.)+[a-zA-Z]+)(\/+.*?)(?= |<br>|$)/", function ($match) { return "<a href=\"".$match[0]."\">".$match[0]."</a>"; }, $text);
		
		# Format bold-italic
		$text = preg_replace_callback("/(?!\/)\*\*\*(.+?)(?!\/)\*\*\*/", function ($match) { return "<b><i>".$match[1]."</i></b>"; }, $text);

		# Format bold
		$text = preg_replace_callback("/(?!\/)\*\*(.+?)(?!\/)\*\*/", function ($match) { return "<b>".$match[1]."</b>"; }, $text);
		
		# Format italic
		$text = preg_replace_callback("/(?!\/)\*(.+?)(?!\/)\*/", function ($match) { return "<i>".$match[1]."</i>"; }, $text);
		
		return $text;
	}

	function getPollResults($database, $post, $pollOptions = null) {
		// Get pollOptions, if not given as argument
		if ($pollOptions === null) {
			$statement = $database->prepare("SELECT `options` FROM `polls` WHERE `post` = CONV(?, 16, 10);");
			$statement->execute([$post]);
			$pollOptions = json_decode($statement->fetchAll()[0][0], true);
		}

		// Get total number of votes
		$statement = $database->prepare("SELECT COUNT(`vote`) FROM `votes` WHERE `post` = CONV(?, 16, 10);");
		$statement->execute([$post]);
		$pollTurnout = (int)$statement->fetchAll()[0][0];

		// Get poll results
		$pollResults = array();
		foreach ($pollOptions as $optionId => $optionName) {
			$statement = $database->prepare("SELECT COUNT(`vote`) FROM `votes` WHERE `post` = CONV(?, 16, 10) AND `vote` = ?");
			$statement->execute([$post, $optionId]);
			$results = $statement->fetchAll();
			$pollResults[$optionId] = ((int)$results[0][0] == 0 ? 0 : round((int)$results[0][0] / $pollTurnout * 100, 1));
		}

		return $pollResults;
	}

	function getPollAssoc($database, $post, $outputResults = null) {
		// Check if poll exists
		$statement = $database->prepare("SELECT `options`, `end` FROM `polls` WHERE `post` = CONV(?, 16, 10);");
		$statement->execute([$post]);
		$results = $statement->fetchAll();
		if ($results == []) { return null; }

		// Extract pollOptions out of query result
		$pollOptions = json_decode($results[0][0], true);
		$pollEnd = strtotime($results[0][1]);

		// Construct poll assoc
		$poll = ["options" => array()];
		foreach ($pollOptions as $optionId => $optionName) { $poll["options"][$optionId] = $optionName; }
		if ($outputResults) { $poll["results"] = getPollResults($database, $post, $pollOptions); }
		$poll["end"] = $pollEnd;

		return $poll;
	}

	function checkIfUserHasVotedInPoll($database, $post, $uid, $user_ip = null, $user_ip_xff = null) {
		if ($user_ip !== null && $user_ip_xff !== null) {
			$query = "SELECT `vote` FROM `votes` WHERE `post` = CONV(?, 16, 10) AND (`uid` = CONV(?, 16, 10) OR `ip` = INET_ATON(?) OR `xff` = INET_ATON(?));";
			$params = [$post, $uid, $user_ip, $user_ip_xff];
		} else {
			$query = "SELECT `vote` FROM `votes` WHERE `post` = CONV(?, 16, 10) AND `uid` = CONV(?, 16, 10);";
			$params = [$post, $uid];
		}
		$statement = $database->prepare($query);
		$statement->execute($params);
		$results = $statement->fetchAll();
		return ($results != [] ? $results[0][0] : false);
	}

	function checkIfPollHasEnded($database, $post) {
		$statement = $database->prepare("SELECT `end` FROM `polls` WHERE `post` = CONV(?, 16, 10) AND `end` > CURRENT_TIMESTAMP;");
		$statement->execute([$post]);
		$results = $statement->fetchAll();
		return $results == [];
	}

	function sendNotification($database, $to_uids, $contents) {
		foreach ($to_uids as $uid) {
			$statement = $database->prepare("INSERT INTO `notifications` (`uid`, `contents`) VALUES (CONV(?, 16, 10), ?);");
			$statement->execute([$uid, $contents]);
		}
		return true;
	}

	function fetchNotifications($database, $uid, $decode = true) {
		// Fetch notifications and construct array
		$statement = $database->prepare("SELECT `contents`, `uid`, `id` FROM `notifications` WHERE (`uid` = CONV(?, 16, 10) OR `uid` = 0) AND `datetime` <= CURRENT_TIMESTAMP ORDER BY `datetime` DESC;");
		$statement->execute([$uid]);
		$results = $statement->fetchAll();
		$notifications = array();
		foreach ($results as $notification) {
			// If broadcast, check if read receipt already exists
			if ($notification["uid"] == 0) {
				$statement = $database->prepare("SELECT COUNT(*) FROM `notificationreadreceipts` WHERE `uid` = CONV(?, 16, 10) AND `notification` = ?;");
				$statement->execute([$uid, $notification["id"]]);
				$result = boolval($statement->fetchAll()[0][0]);
				if ($result) continue;

				// Send read receipt
				$statement = $database->prepare("INSERT INTO `notificationreadreceipts` (`notification`, `uid`) VALUES (?, CONV(?, 16, 10));");
				$statement->execute([$notification["id"], $uid]);
			}

			array_push($notifications, ($decode ? json_decode($notification["contents"], true) : $notification["contents"]));
		}

		// Remove all notifications
		$statement = $database->prepare("DELETE FROM `notifications` WHERE `uid` = CONV(?, 16, 10);");
		$statement->execute([$uid]);

		return $notifications;
	}































?>