<?php
	$code = (int)$_POST["code"];

	// Check if code is between 0 and 999999
	if ($code < 0 || $code > 999999) { http_response_code(400); exit("Invalid request."); }

	// Get code of ticket and check if equal
	$statement = $database->prepare("SELECT `verify`, `status` FROM `tickets` WHERE `uid` = CONV(?, 16, 10) AND TIMESTAMPDIFF(SECOND, `datetime`, CURRENT_TIMESTAMP()) < 900 ORDER BY `datetime` DESC;");
	$statement->execute([$uid]);
	$results = $statement->fetchAll();
	if ($results == []) {
		http_response_code(404);
		exit("The corresponding ticket either does not exist or is older than 15 minutes. Please try opening a new one.");
	} else if ($results[0][1] !== NULL) {
		http_response_code(404);
		exit("The corresponding ticket either does not exist or is older than 15 minutes. Please try opening a new one.");
	} else if ($results[0][0] != $code) {
		http_response_code(403);
		exit("Wrong code.");
	}

	// Get case details
	$statement = $database->prepare("SELECT `id`, `subject`, `details`, `email`, `datetime` FROM `tickets` WHERE `uid` = CONV(?, 16, 10) ORDER BY `datetime` DESC LIMIT 1;");
	$statement->execute([$uid]);
	$results = $statement->fetchAll();
	$details = wordwrap($results[0][2], 68, "\r\n", true);
	switch ($results[0][1]) {
		case 0: $subject = "I want to give Feedback or report a Bug."; break;
		case 1: $subject = "I have a suggestion for a Board or feature."; break;
		case 2: $subject = "I want to appeal my ban."; break;
		case 3: $subject = "I want to Report a Post."; break;
		case 4: $subject = "I'm having concerns about a staff decision."; break;
		case 5: $subject = "I have a general question."; break;
		case 6: $subject = "Other"; break;
	}

	// Send case details to staff (support@huechan.com)
	$mail_subject = "Case #".$results[0][0];
	$mail_body = "Case #".$results[0][0]."\r\nSubject: ".$subject."\r\nEmail address: ".$results[0][3]."\r\nTimestamp: ".$results[0][4]."\r\n\r\n".$details."\r\n\r\nIMPORTANT: As this email contains user-generated content from\r\noutside of the team, DO NOT open any links or attachments in this\r\nemail.";
	$mail_headers = "From: Huechan <support@huechan.com>\nReply-To: <".$results[0][3].">";
	mail("support@huechan.com", $mail_subject, $mail_body, $mail_headers);

	// Set ticket as 'verified' in database
	$statement = $database->prepare("UPDATE `tickets` SET `status` = 0 WHERE `uid` = CONV(?, 16, 10) ORDER BY `datetime` DESC LIMIT 1;");
	$statement->execute([$uid]);

	// Disconnect database and exit
	$database = null;
	exit("OK");
?>