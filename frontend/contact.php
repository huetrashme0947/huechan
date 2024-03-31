<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Contact - Huechan</title>

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

	<div class="container-sm">
		<p>
			<h1>Contact</h1>
		</p>
		<p>
			<span class="text-danger">Please note that this project is still a work-in-progress, so the email adresses and forms listed below may not work as expected or even entirely at this moment. Should you have trouble using one of the listed methods, you can always contact me on <a href="https://twitter.com/huetrashme0947">Twitter</a> for important inquiries.</span>
		</p>
		<p>
			<b>Ban appeals</b><br>
			Use the <a href="/support">Support form</a> and choose "I want to appeal my ban."<br>
			<span class="text-danger">Please do not send any ban appeals to one of the email addresses listed below.</span>
		</p>
		<p>
			<b>Reporting Posts</b><br>
			Please use the integrated Reporting feature.
		</p>
		<p>
			<b>Questions about Huechan</b><br>
			If your question is not listed on <a href="/help">Help</a>, use the <a href="/support">Support form</a> and choose "I have a general question."
		</p>
		<p>
			<b>Feedback & Bug Reports</b><br>
			Use the <a href="/support">Support form</a> and choose "I want to give Feedback or report a Bug."
		</p>
		<p>
			<b>Copyright infringements</b><br>
			<a href="mailto:dmca@huechan.com">dmca@huechan.com</a>
		</p>
		<p>
			<b>Anything not listed</b><br>
			Use the <a href="/support">Support form</a> or <a href="mailto:contact@huechan.com">contact@huechan.com</a>
		</p>
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