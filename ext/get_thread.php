<?php
	$id = $_GET["id"];
	
	$posts = array();

	while (true) {
		$statement = $database->prepare("SELECT CONV(`inreplyto`, 10, 16) FROM `posts` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([sizeof($posts) == 0 ? $id : $posts[sizeof($posts)-1]]);
		$results = $statement->fetchAll();
		if ($results[0][0] == null) { break; }
		$posts[] = correctIDOutputFromDB($results[0][0]);
	}

	$posts = array_reverse($posts);

	$post_assocs = array();
	foreach ($posts as $post) { $post_assocs[] = getPostAssoc($database, $post, $uid, true); }
	$posts = $post_assocs;

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK ".json_encode($posts));
?>