<?php
	$id = $_POST["id"];
	$reason = (int)$_POST["reason"];
	$details = isset($_POST["details"]) ? $_POST["details"] : null;

	// Check if input meet requirements
	if ($reason < 0 || $reason > 9 || strlen($details) > 2000) { http_response_code(400); exit("Invalid request."); }

	// Check if Post actually exists and if Post is from Staff (Posts by Staff cannot be reported)
	$statement = $database->prepare("SELECT `uid` FROM `posts` WHERE `id` = CONV(?, 16, 10) AND (`v` IS NULL OR `v` = 0);");
	$statement->execute([$id]);
	$results = $statement->fetchAll();
	if ($results == [] || checkRole($database, $results[0][0]) != null) { http_response_code(404); exit("Invalid request."); }

	// Create report
	$statement = $database->prepare("INSERT INTO `reports` (`reason`, `details`, `post`, `uid`, `ip`, `xff`) VALUES (?, ?, CONV(?, 16, 10), CONV(?, 16, 10), INET_ATON(?), INET_ATON(?));");
	$statement->execute([$reason, $details, $id, $uid, $user_ip, $user_ip_xff]);

	// Exit
	$database = null;
	exit("OK");
?>