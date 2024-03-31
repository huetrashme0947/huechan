<?php
	// Required variables
	$id = $_POST["id"];
	
	// Check if Post actually exists, has been posted by requesting User ID and has not been deleted or removed yet
	$statement = $database->prepare("SELECT CONV(`uid`, 10, 16) FROM `posts` WHERE `id` = CONV(?, 16, 10) AND (`v` IS NULL OR NOT `v` = 1 OR `v` = 2);");
	$statement->execute([$id]);
	$results = $statement->fetchAll();
	if ($results == []) { http_response_code(404); exit("Post either does not exist or has already been deleted."); }
	else if (correctIDOutputFromDB($results[0][0]) != $uid) { http_response_code(401); exit("Invalid request."); }

	// Archive image, if there is one
	$image_move = archive_image($id);
	if ($image_move != true && $image_move != null) { http_response_code(500); exit("File could not be deleted. Please try again later."); }

	// Delete Post
	try {
		$statement = $database->prepare("UPDATE `posts` SET `v` = 2 WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$id]);
	} catch (PDOException $exception) { http_response_code(500); exit("Submitting failed. Please try again later."); }

	// Reset User ID and Authkey cookies
	header("Set-Cookie: uu=".$uid."; Max-Age=2592000; Path=/; Secure; HttpOnly; SameSite=Lax");
	header("Set-Cookie: aph=".generateAuthkey($database, $uid)."; Max-Age=2592000; Path=/; Secure; HttpOnly; SameSite=Lax", false);

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK");
?>