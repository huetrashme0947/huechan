<?php
	// Get all Boards
	$statement = $database->prepare("SELECT `id`, `name`, `description` FROM `boards` ORDER BY `id` ASC");
	$statement->execute();
	$results = $statement->fetchAll();

	// Get description of Board
	$statement = $database->prepare("SELECT `description` FROM `boards` WHERE `id` = ?;");
	$statement->execute([$board_id]);
	$board_description = $statement->fetchAll()[0][0];
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>About - Huechan</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/afd60586d3.js" crossorigin="anonymous"></script>

	<script src="/ext/cookies.js"></script>

	<script>
		window.onload = initPage;
		window.onresize = positionBottomNavbar;

		function initPage(first = false) {
			<?php if ($uid == null): ?>
				if (first) {
					new bootstrap.Modal(document.getElementById("cookie-modal"), {
						backdrop: "static",
						keyboard: false
					}).show();
				} else {
			<?php endif; ?>
				updatecookies();
				positionBottomNavbar();
			<?php if ($uid == null): ?>
				}
			<?php endif; ?>
		}
	</script>

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
			max-width: 15em !important;
		}

		.post-image-big {
			border-radius: 8px;
			max-width: 100%;
		}

		.post-op {
			padding: 16px;
			border-radius: 8px;
		}

		.post-dummy {
			cursor: wait;
		}

		.post-dummy-spacer {
			margin-top: 16px;
		}

		.post-content, .post-navelement {
			cursor: pointer;
			max-width: 90em !important;
		}

		.post-navelement {
			min-height: 56px !important;
		}

		span.post-message {
			font-size: 18pt;
		}

		.board-tn {
			border-radius: 8px;
			margin-right: 16px;
		}

		.offcanvas {
			background-color: #171717;
		}

		.center-h {
			 display: flex !important;
			 justify-content: center !important;
		}

		p.center-h {
			text-align: center;
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
	<?=retrieveNavbar(null, getBanType($database, $uid, $user_ip), $user_role !== null)?>

	<span id="root">
		<br><br><br>

		<?php if ($maintenance_mode): ?>
			<div class="container-sm">
				<div class="alert alert-primary" role="alert">Huechan is currently in maintenance mode. Please remember to disable it after finishing your work.</div>
			</div>
		<?php endif; ?>

		<br>
		<div class="container-sm" style="width: 70%">
			<div class="center-h">
				<img src="/ext/images/huechan.png" style="max-width: 360px;">
			</div>
			<br>
			<p class="center-h">
				Huechan was created by HUE_TrashMe starting in late March 2022 and was finished about three weeks later. The goal of this project is to bring the concept of Imageboards up to date with a modern design and proper moderation. Huechan uses the Bootstrap framework and is entirely written in PHP, JavaScript and obviously HTML and CSS.
			</p>
			<a class="center-h" href="/help">Help</a>
			<a class="center-h" href="/contact">Contact</a>
			<a class="center-h" href="/privacy">Privacy Policy</a>
			<a class="center-h" href="/rules">Rules</a>
			<a class="center-h" href="/support">Support</a>
			<br>
			<div class="center-h"><b>Special Thanks</b></div>
			<div class="center-h"><a href="https://getbootstrap.com">Bootstrap</a></div>
			<div class="center-h"><a href="https://php.net">The PHP Project</a></div>
			<div class="center-h">Everyone who made this project possible!</div>
			<br>
			<div class="center-h">Huechan v1.0.0-beta20</div>
			<div class="center-h">&copy; 2022<?=((int)date("Y") > 2022 ? ("-".date("Y")) : "")?> HUE_TrashMe</div>
		</div>
		<br><br>
	</span>

	<?php
		echo retrieveBottomNavbar(true);
		echo retrieveCookieModal();
	?>

	<div class="position-fixed top-0 end-0 p-3 dark" style="z-index: 11;">
		<div id="toast" class="toast bg-primary text-light border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
			<div class="toast-header">
				<strong class="me-auto" id="toast-title">&nbsp;</strong>
				<small id="toast-small"></small>
			</div>
			<div class="toast-body" id="toast-body"></div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>