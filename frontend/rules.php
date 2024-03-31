<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Rules - Huechan</title>

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
			<h1>Huechan Rules</h1>
			Last updated: Apr 11, 2022
		</p>
		<p>As any other platform, where users are to share content and upload files, Huechan has rules that users have to stick to. Depending on the significance, violating the rules will result in some kind of punishment for the user. On Huechan, this normally ranges from one of your posts getting removed to you getting banned permanently from using the platform. Keep the following rules in mind when Posting, as we <i>will not</i> shy away from enforcing them with harsh punishments for the Poster, if necessary.</p>
		<p>
			<h4>1 Posting hate speech</h4>
			One of the main reasons Huechan was started in the first place were the toxic communities and the basically non-existent moderation on other Imageboards. We want Huechan to stay a safe place for every user, so any form of hate speech against specific people or groups <b>will not be tolerated on this platform under any circumstances.</b>
		</p>
		<p>
			<h4>2 Posting content not fitting into its Board</h4>
			Huechan and similar sites are not organized into Boards, because the creators felt like it. Boards have the purpose of showing the users the content they are actually looking for. Posts that do not fit into its Board are not welcome there and will be removed. Period. For anything there is no dedicated Board on Huechan, use /x/.
		</p>
		<p>
			<h5>2.1 Memes</h5>
			Memes are often a special case. There is /r/, a Board dedicated to just Memes, however please keep in mind that, as said, Boards exist to show users the content they are looking for. Users just looking for "memes" are most likely not interested in a meme about a specific historical topic, which does require at least some background knowledge to understand it. Memes that are too niche to be actually interesting for most users generally do not fit into /r/ and should not be posted there. Memes that are posted on any other Board and do fit into the Board's topic, are generally allowed on most Boards, unless the Board rules state otherwise. However, if they do or if there is no Board the meme would fit in, consider just using /x/.<br>
			<div style="margin-top: 8px;"></div>
			<b>TLDR:</b> For memes about general things, use /r/. For memes about specific topics, use a related Board. If memes are not allowed on the related Board or there is no related Board, use /x/.
		</p>
		<p>
			<h4>3 Posting NSFW content</h4>
			Content not suitable for minors is generally not allowed on any Board, including /x/.
		</p>
		<p>
			<h4>4 Posting private information</h4>
			Posting private information of others who did <b>not</b> gave you the explicit permission to share them with others is not allowed on Huechan and will result in at least your Post getting removed. Posting private information of yourself is not necessarily forbidden, but we highly recommend not doing so. <b>We are not responsible for any conseqences caused by ignoring this advice. Do not say we didn't warn you.</b>
		</p>
		<p>
			<h4>5 Posting illegal content</h4>
			Uploading or Posting content illegal under United States Federal Law is strictly forbidden and will not only result in a permanent ban, but also in legal consequences for the author of the Post.
		</p>
		<p>
			<h4>6 Spamming</h4>
			Spamming or just Posting gibberish is not allowed, unless the Board Rules state otherwise.
		</p>
		<p>
			<h4>7 Posting non-English content</h4>
			Huechan is an English speaking community. When contributing, you are to Post in English, unless the Board rules state otherwise. (this is the case on /y/ for example)
		</p>
		<p>
			<h4>8 Bypassing bans</h4>
			Violating a Global or Board rule will result at least in your Post being removed by staff. Depending on which rule you violated, what exactly was the content of your Post and if this was your first violation, the punishment can be harsher than that and may even come in form of a permanent ban. Bypassing a temporary ban received for violating the rules will result in a permanent one.
		</p>
		<p>
			<h4>9 Violating Board Rules</h4>
			Boards have their own rules, that you have to keep in mind when Posting there. Generally, Global Rules break Board Rules, unless a Global rule states otherwise. So if a Board rule states that uploading NSFW content is allowed, this rule would be void, because Global rule 3 exists.
		</p>
		<p>
			<h4>10 Abusing the Reporting feature</h4>
			Huechan has a Reporting feature, so that users finding a Post which they think violates the Rules can report it to us. We will review the Report and decide if the reported Post should be removed and/or if harsher measures against the user are necessary. We spend a significant amount of time on checking the reports, so spam or knowingly wrongly-submitted reports are the last thing we need. Abuse of the feature will result in at least a temporary ban and a permanently ban in recurring cases.
		</p>
		<p>
			<h4>11 Disrespecting Staff</h4>
			The Huechan staff is responsible for enforcing the rules and keeping the platform and the Boards clean. They and their decisions are to respect and not doing so will result in a punishment. If you are having serious concerns about the work or decisions of a Staff member, please use the Support form.
		</p>
		<p>
			<h4>12 Asking for staff status</h4>
			Staff does not get picked by the criteria of who is the most kind or active user. Also it does not get picked by who wants to become staff the most, rather the opposite. Asking for staff status is not only pointless, but also just annoying for everyone.
		</p>
		<p>
			<h4>13 Changes of Rules</h4>
			This document or the Rules of a Board can change anytime. If we plan to change something, we will notify the community at least 7 days before the changes take effect. If the changes affect the Global Rules, we will announce them on /z/. If they affect a Board's Rules, we will announce them on the pinned Post of the Board and also on /z/, if we think, the change is of higher significance for the whole platform.
		</p>
	</div>

	<br>

	<?=retrieveBottomNavbar(false)?>

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