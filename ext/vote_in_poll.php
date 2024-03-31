<?php
	// Required variables
	$post = $_POST["post"];
	$vote = (int)$_POST["vote"];

	// Basic input validation (maximum 5 options)
	if ($vote < 0 || $vote > 4) { http_response_code(400); exit("Invalid request."); }

	// Check if post exists and has a poll attached which hasn't ended yet
	$statement = $database->prepare("SELECT `options`, `end` FROM `polls` WHERE `post` = CONV(?, 16, 10) AND `end` > CURRENT_TIMESTAMP;");
	$statement->execute([$post]);
	$results = $statement->fetchAll();
	if ($results == []) { http_response_code(403); exit("Poll either does not exist or voting has ended."); }

	// Check if specified vote is allowed
	$pollOptions = json_decode($results[0][0], true);
	if (!isset($pollOptions[(string)$vote])) { http_response_code(400); exit("Invalid request."); }

	// Check if user has already voted in poll
	if (checkIfUserHasVotedInPoll($database, $post, $uid, $user_ip, $user_ip_xff) === true) { http_response_code(409); exit("You have already voted in this poll. ".json_encode(getPollResults($database, $post, $pollOptions))); }

	// Cast vote
	try {
		$statement = $database->prepare("INSERT INTO `votes` (`post`, `vote`, `uid`, `ip`, `xff`) VALUES (CONV(?, 16, 10), ?, CONV(?, 16, 10), INET_ATON(?), INET_ATON(?));");
		$statement->execute([$post, $vote, $uid, $user_ip, $user_ip_xff]);
	} catch (PDOException $exception) { http_response_code(500); exit("Submitting failed. Please try again later."); }

	$pollResults = getPollResults($database, $post, $pollOptions);

	// Return poll results and exit
	$database = null;
	exit("OK ".json_encode($pollResults));
?>