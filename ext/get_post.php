<?php
	$id = $_GET["id"];

	// Check if Post actually exists
	$statement = $database->prepare("SELECT COUNT(*) FROM `posts` WHERE `id` = CONV(?, 16, 10);");
	$statement->execute([$id]);
	if ($statement->fetchAll()[0][0] != 1) { http_response_code(404); exit("Post could not be found."); }

	$post = getPostAssoc($database, $id, $uid, true);

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK ".json_encode($post));
?>