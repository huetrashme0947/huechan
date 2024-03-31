<?php
	// Required variables
	$image = isset($_FILES["image"]) ? $_FILES["image"] : null;
	$name = $_POST["name"] != "" ? $_POST["name"] : null;
	$content = $_POST["content"] != "" ? $_POST["content"] : null;
	$board = $_POST["board"];
	$in_reply_to = $_POST["inreplyto"] != "" ? $_POST["inreplyto"] : null;
	$visibility = $board == "z" ? 0 : null;

	/*
		Visibility (only choosable by staff Posting something):
		null = default 			(replying possible, viewable by everyone)
		0 = replying disabled 	(replying disabled, viewable by everyone)
		1 = removed 			(replying disabled, viewable by staff and author only)
		2 = deleted 			(replying disabled, not viewable by anyone, however still present in database)
	*/

	// Either content or image has to be given. If neither is, reject
	if ($content == "" && $image == null) { http_response_code(400); exit("Invalid request."); }

	// Basic User Input Validation (just check length)
	if (strlen($name) > 25 || strlen($content) > 2000) { http_response_code(400); exit("Invalid request."); }

	// Throw error, if post is not a reply, has no image attached and user is not staff (only staff can open new Thread without image)
	if ($in_reply_to == null && $image == null && $user_role == null) { http_response_code(400); exit("Invalid request."); }

	// If Board is /z/, check if Admin
	if ($board == "z" && $user_role != 1) { http_response_code(401); exit("Invalid request."); }

	// Check if Board actually exists
	$statement = $database->prepare("SELECT count(*) FROM `boards` WHERE `id` = ?;");
	$statement->execute([$board]);
	if ($statement->fetchAll()[0][0] == 0) { http_response_code(400); exit("Invalid request."); }

	// If reply, check if source thread exists, if source thread is actually on selected board and if replying is enabled
	if ($in_reply_to != null) {
		$statement = $database->prepare("SELECT `v`, `board`, CONV(`uid`, 10, 16) FROM `posts` WHERE `id` = CONV(?, 16, 10) AND (`v` IS NULL OR NOT `v` = 2);");
		$statement->execute([$in_reply_to]);
		$results = $statement->fetchAll();
		if ($results == []) { http_response_code(404); exit("Post does not exist."); }
		if ($results[0][0] === 0 || $results[0][0] === 1) { http_response_code(403); exit("Replying has been disabled on this Post."); }
		if ($results[0][1] != $board) { http_response_code(400); exit("Invalid request."); }
		$in_reply_to_user = correctIDOutputFromDB($results[0][2]);
	}

	// Generate post ID (generate 8 bytes randomly, convert to hex, so you get 16 byte hex number) (an ID of 0 would interfere with the database, so regenerate if 0)
	while (true) {
		$id = bin2hex(random_bytes(8));
		if ($id == "0000000000000000") { continue; }
		$statement = $database->prepare("SELECT COUNT(*) FROM `posts` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$id]);
		// If the generated ID is not taken yet, exit loop. Else, regenerate
		if ($statement->fetchAll()[0][0] == 0) { break; }
	}

	// Check if an image was attached. If yes, upload
	if ($image != null) { $image_upload = upload_image($image, $id); if ($image_upload !== true) { http_response_code($image_upload[0]); exit($image_upload[1]); } }

	// Submit post
	try {
		$statement = $database->prepare("INSERT INTO `posts` (`id`, `board`, `inreplyto`, `content`, `name`, `uid`, `ip`, `xff`, `v`) VALUES (CONV(?, 16, 10), ?, CONV(?, 16, 10), ?, ?, CONV(?, 16, 10), INET_ATON(?), INET_ATON(?), ?);");
		$statement->execute([$id, $board, $in_reply_to, $content, $name, $uid, $user_ip, $user_ip_xff, $visibility]);
	} catch (PDOException $exception) { http_response_code(500); exit("Submitting failed. Please try again later."); }

	// If is reply, send notification
	if (isset($in_reply_to_user)) {
		$notification = [
			"type" => "reply",
			"attachment" => [
				"board" => $board,
				"post" => $id
			]
		];
		sendNotification($database, [$in_reply_to_user], json_encode($notification));
	}

	// Reset User ID and Authkey cookies
	header("Set-Cookie: uu=".$uid."; Max-Age=2592000; Path=/; Secure; HttpOnly; SameSite=Lax");
	header("Set-Cookie: aph=".generateAuthkey($database, $uid)."; Max-Age=2592000; Path=/; Secure; HttpOnly; SameSite=Lax", false);

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK ".$id);
?>