<?php
	$starred = json_decode($_GET["starred"]);
	$offset_time = isset($_GET["offset_time"])? date("Y-m-d H:i:s", DateTime::createFromFormat("D, d M Y H:i:s e", $_GET["offset_time"])->getTimestamp()) : date("Y-m-d H:i:s");
	
	// Build Query
	$query = "SELECT CONV(`id`, 10, 16) FROM `posts` WHERE `board` IN (?";
	$params = [$starred[0]];
	for ($i=1; $i < sizeof($starred); $i++) { $query .= ", ?"; $params[] = $starred[$i]; }
	$query .= ") AND ";
	if ($offset_time != null) { $query .= "`datetime` < ? AND "; $params[] = $offset_time; }
	$query .= "(`v` IS NULL OR `v` = 0) AND NOT `id` = IFNULL((SELECT `pinned` FROM `boards` WHERE `id` = `posts`.`board`), 0) AND `inreplyto` IS NULL ORDER BY `datetime` DESC LIMIT 20;";

	// Filter Posts from database
	$statement = $database->prepare($query);
	$statement->execute($params);
	$results = $statement->fetchAll();

	if (sizeof($results) < 20) { $ret_last = true; }

	$posts = array();
	foreach ($results as $column) { $posts[] = correctIDOutputFromDB($column[0]); }

	$post_assocs = array();
	foreach ($posts as $post) {
		$post_assoc = getPostAssoc($database, $post, $uid, true);
		if ($post_assoc != null && $post_assoc != 0) { $post_assocs[] = $post_assoc; }
	}
	$posts = $post_assocs;
	if ($ret_last) { $posts[] = "last"; }

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK ".json_encode($posts));
?>