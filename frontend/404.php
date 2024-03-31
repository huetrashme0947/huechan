<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Not Found - Huechan</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/afd60586d3.js" crossorigin="anonymous"></script>

	<script src="/ext/cookies.js"></script>

	<script>
		window.onload = initPage;

		function initPage(first = true) {
			<?php if ($uid == null): ?>
				if (first) {
					new bootstrap.Modal(document.getElementById("cookie-modal"), {
						backdrop: "static",
						keyboard: false
					}).show();
				} else {
			<?php endif; ?>
				updatecookies();
			<?php if ($uid == null): ?>
				}
			<?php endif; ?>
		};
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
	<?=retrieveNavbar(null, getBanType($database, $uid, $user_ip), $user_role !== null)?>

	<br><br><br>

	<?php if ($maintenance_mode): ?>
		<div class="container-sm">
			<div class="alert alert-primary" role="alert">Huechan is currently in maintenance mode. Please remember to disable it after finishing your work.</div>
		</div>
	<?php endif; ?>

	<br>

	<div class="container-sm center-h">
		<div class="post-op bg-dark" style="width: 50%; min-width: 300px;">
			<span>
				<h1 class="center-h" style="font-size: 48px">404</h1>
				<p class="center-h">Well, this seems to be a dead end...</p>
				<p class="center-h"><a href="/">Return to Home</a></p>
			</span>
		</div>
	</div>

	<br>

	<?=retrieveBottomNavbar(true)?>

	<div class="modal fade" id="cookie-modal">
		<div class="modal-dialog">
			<div class="modal-content bg-dark text-light">
				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">We Use Cookies</h4>
				</div>
				<!-- Modal body -->
				<div class="modal-body">
					<p>Please note, that this page uses cookies to perform right. However, we only use cookies which are necessary for the functionality of this site.</p>
					<p class="text-danger" id="cookie-error"></p>
				</div>
				<!-- Modal footer -->
				<div class="modal-footer">
					<button type="button" id="cookie-agreebtn" onclick="generateCookies();" class="btn btn-success">Agree</button>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>