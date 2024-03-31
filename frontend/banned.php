<?php
	// Get current ban of user
	$statement = $database->prepare("SELECT `start`, `type`, `reason`, CONV((SELECT `post` FROM `removals` WHERE `id` = `bans`.`removal`), 10, 16), (SELECT `details` FROM `removals` WHERE `id` = `bans`.`removal`) FROM `bans` WHERE `uid` = CONV(?, 16, 10) OR `ip` = INET_ATON(?) ORDER BY `start` DESC;");
	$statement->execute([$uid, $user_ip]);
	$results = $statement->fetchAll();

	$ban_start = $results[0][0];
	$ban_start_formatted = date("Y/m/d g:i A", DateTime::createFromFormat("Y-m-d H:i:s", $ban_start)->getTimestamp());
	$ban_type = $results[0][1];
	$ban_reason = $results[0][2];
	$ban_post = $results[0][3];
	$ban_details = $results[0][4];

	// Get end of ban
	if ($ban_type == 0) {
		// 48 hours
		$ban_end = date("Y/m/d g:i A", DateTime::createFromFormat("Y-m-d H:i:s", $ban_start)->getTimestamp() + 172800);
	} else if ($ban_type == 1) {
		// 14 days
		$ban_end = date("Y/m/d g:i A", DateTime::createFromFormat("Y-m-d H:i:s", $ban_start)->getTimestamp() + 1209600);
	}

	// Get Board of Post and fill Post ID with zeroes, if there is one
	if ($ban_post != null) {
		$ban_post = correctIDOutputFromDB($ban_post);
		$statement = $database->prepare("SELECT `board` FROM `posts` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$ban_post]);
		$ban_post_board = $statement->fetchAll()[0][0];
	}

	$ban_post_href = "/".$ban_post_board."/".$ban_post;

	// Get reason of ban;
	switch ($ban_reason) {
		case 0:
			$ban_reason_text = "Posting hate speech (Global rule 1)";
			break;

		case 1:
			$ban_reason_text = "Posting content not fitting into its Board (Global rule 2)";
			break;

		case 2:
			$ban_reason_text = "Posting NSFW content (Global rule 3)";
			break;

		case 3:
			$ban_reason_text = "Posting private information (Global rule 4)";
			break;

		case 4:
			$ban_reason_text = "Posting illegal content (Global rule 5)";
			break;

		case 5:
			$ban_reason_text = "Spamming (Global rule 6)";
			break;

		case 6:
			$ban_reason_text = "Posting non-English content (Global rule 7)";
			break;

		case 7:
			$ban_reason_text = "Bypassing bans (Global rule 8)";
			break;

		case 8:
			$ban_reason_text = "Violating Board Rules (Global rule 9)";
			break;

		case 9:
			$ban_reason_text = "Other";
			break;

		case 10:
			$ban_reason_text = "Abusing the Reporting feature (Global rule 10)";
			break;
		
		default:
			$ban_reason_text = "<i>No reason provided.</i>";
			break;
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Huechan</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/afd60586d3.js" crossorigin="anonymous"></script>

	<style>
		html, body {
			height: 100%;
		}

		a {
			text-decoration: none;
			cursor: pointer;
		}

		.post-image {
			border-radius: 8px;
		}

		.post-op {
			padding: 16px;
			border-radius: 8px;
		}

		.post-content {
			cursor: pointer;
		}

		.offcanvas {
			background-color: #171717;
		}

		.center-h {
			 display: flex !important;
			 justify-content: center !important;
		}

		.list-group-item-action {
			cursor: pointer;
		}

		.vspacer-sm {
			margin-bottom: 8px;
		}

		.vspacer-lg {
			margin-bottom: 24px;
		}

		.hspacer-sm {
			margin-left: 8px;
		}

		.popover {
			background-color: #212529 !important;
		}

		.popover-arrow {
			display: none !important;
		}

		.popover-header {
			background-color: #212529 !important;
		}
	</style>
</head>
<body class="text-light" style="background-color: #171717">
	<?=retrieveNavbar(null, $ban_type, $user_role !== null)?>

	<br><br><br>

	<?php if ($maintenance_mode): ?>
		<div class="container-sm">
			<div class="alert alert-primary" role="alert">Huechan is currently in maintenance mode. Please remember to disable it after finishing your work.</div>
		</div>
	<?php else: ?><br><?php endif; ?>

	<div class="container-sm">
		<h1>You are currently banned from using Huechan.</h1>
		<p>We decided that one of your Posts did violate our Rules<?=$ban_type != 2 ? "" : " significantly"?>, so we <?=$ban_type != 2 ? "temporarily" : "permanently"?> banned you from using Huechan.</p>
		<p>
			<b>Details</b><br>
			Post:&nbsp;<?=$ban_post === null ? "<i>No Post attached.</i>" : '<a href="'.$ban_post_href.'">https://huechan.com'.$ban_post_href.'</a>'?><br>
			Reason: <?=$ban_reason_text?><br>
			<?php if ($ban_details !== null): ?>Details: <?=$ban_details?><br><?php endif; ?>
			Duration: <?=$ban_type == 0 ? "48 hours" : ($ban_type == 1 ? "14 days" : "Permanent")?><br>
			Start of ban: <?=$ban_start_formatted?>
			<?=$ban_type == 2 ? "" : "<br>End of ban: ".$ban_end?>
		</p>
		<?=$ban_type != 2 ? "<p>Trying to bypass this temporary ban will lead to a permanent one.</p>" : ""?>
		<p>If you think we made a mistake, <a href="/support">you can appeal here</a>.</p>
	</div>

	<br>

	<?=retrieveBottomNavbar()?>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>