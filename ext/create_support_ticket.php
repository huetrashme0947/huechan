<?php
	$subject = (int)$_POST["subject"];
	$details = $_POST["details"];
	$email = $_POST["email"];
	$captcha = $_POST["captcha"];

	// Check if subject is valid, details does not exceed 2000 chars and email is valid
	if (($subject < 0 || $subject > 6) || strlen($details) > 2000 || !preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/", $email)) { http_response_code(400); exit("Invalid request."); }

	// Check captcha
	$captcha_result = json_decode(file_get_contents("https://api.friendlycaptcha.com/api/v1/siteverify", false, stream_context_create(["http" => [
			"header" => "Content-Type: application/x-www-form-urlencoded\r\n",
			"method" => "POST",
			"content" => http_build_query([
				"solution" => $captcha,
				"secret" => "",
				"sitekey" => ""
			])
		]])), true);
	if ($captcha_result["success"] == false) { http_response_code(401); exit("Captcha verification failed. Please try again later."); }

	// Send email verification code
	$code = strval(random_int(0, 999999));
	while (strlen($code) < 6) { $code = "0" + $code; }
	mail($email, "Huechan Email Verification", "Hi,\r\n\r\nSomeone recently opened a new support ticket with your email address. If this wasn't you, just ignore this message.\r\nIf this was you, please enter the following code into the form. It will expire in 15 minutes.\r\n\r\n".$code."\r\n\r\nYour Huechan team\r\n\r\n(This message was sent automatically. Please don't reply to it.)", "From: Huechan <noreply@huechan.com>");

	// Create ticket and get ticket ID
	$statement = $database->prepare("INSERT INTO `tickets` (`subject`, `details`, `email`, `uid`, `ip`, `xff`, `verify`, `status`) VALUES (?, ?, ?, CONV(?, 16, 10), INET_ATON(?), INET_ATON(?), ?, NULL);");
	$statement->execute([$subject, $details, $email, $uid, $user_ip, $user_ip_xff, $code]);

	// Disconnect database and exit
	$database = null;
	exit("OK");
?>