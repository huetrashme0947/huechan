<?php
	$notifications = fetchNotifications($database, $uid);
	$database = null;
	exit("OK ".json_encode($notifications));
?>