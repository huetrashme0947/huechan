<?php
	if ($_SERVER["REQUEST_METHOD"] != "POST") { http_response_code(405); header("Allow: POST"); exit(); }
	if ($_COOKIE["uu"] != "" && $_COOKIE["aph"] != "") { http_response_code(401); exit(); }

	// Generate user ID (generate 8 bytes randomly, translate to hex, so you get 16 byte hex number)
	while (true) {
		$id = bin2hex(random_bytes(8));

		$statement = $database->prepare("SELECT count(*) FROM `uids` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$id]);

		// If the generated ID is not taken yet, assign. Else, regenerate
		if ($statement->fetchAll()[0][0] == 0) {
			$statement = $database->prepare("INSERT INTO `uids` (`id`, `ip`, `xff`) VALUES (CONV(?, 16, 10), INET_ATON(?), INET_ATON(?));");
			$statement->execute([$id, $user_ip, $user_ip_xff]);
			break;
		}
	}

	// Set User ID and Authkey cookies
	header("Set-Cookie: uu=".$id."; Max-Age=2592000; Path=/; Secure; HttpOnly; SameSite=Lax");
	header("Set-Cookie: aph=".hash("sha256", $id)."; Max-Age=2592000; Path=/; Secure; HttpOnly; SameSite=Lax", false);

	// Disconnect database and exit
	$database = null;
	exit("OK");
?>