<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Help - Huechan</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/afd60586d3.js" crossorigin="anonymous"></script>

	<script src="/ext/cookies.js"></script>
	<?php if ($user_role !== null): ?><script src="/ext/modactions.js"></script><?php endif; ?>

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
			<h1>What is Huechan?</h1>
			Last updated: Apr 13, 2022
		</p>
		<p>Huechan is a platform where anyone can anonymously share images and reply to other user's Posts. This is also called an Imageboard and Huechan isn't the first site with such a concept. Having questions on how to use Huechan? Below are answers to the most common ones. If your question is not listed, consider using our <a href="/support">Support form</a> for more help.</p>
		<p>
			<h4>What is an Imageboard?</h4>
			Imageboards are websites where users are able to anonymously Post images to so called 'Boards'. The concept started in Japan in the late 90s. First, you could just Post text-only messages, but later on some sites, the ability to upload images was added. Eventually, the concept came over to the rest of the world and non-Japanese Imageboards were created. They gained massive popularity in the following years and became very influential regarding the birth of early Meme and Internet culture.</b>
		</p>
		<p>
			<h4>Why is this site organized into Boards? What are they?</h4>
			Boards are an essential part of Huechan. Think of them as Post categories. They are all bound to a specific topic and only contain Posts about that topic. When you want to Post something, please make sure to choose the right Board, as not doing so will result in the removal of your Post. We made an effort to maintain as most Boards as possible, so every topic that we think needs its own Board, gets one. For everything there is no Board, there is /x/, where any topic is allowed.
		</p>
		<p>
			<h4>Why does topic X does not have its own Board? I think it should!</h4>
			If you think, a certain topic needs to have its own Board, you can request a new Board with our <a href="/support">Support form</a>.
		</p>
		<p>
			<h4>I do not want to Post as 'Anonymous'. Is it possible to change my name?</h4>
			Yes. By default you will post as 'Anonymous'. If that is your preferred way, just leave the name field empty when posting. If you want to post under a name other than the default, just fill in your desired name. However, we highly recommend choosing a pseudonym, as posting under your real name is not necessarily safe online. If that surprises you, you should likely not use any online platform.
		</p>
		<p>
			<h4>Can anyone choose any name?</h4>
			Yes. There is currently no method of verifying, if a Poster is actually the Person they are Posting as, and we are not planning to implement such a feature in the near future, <b>so please do not trust the shown name unconditionally.</b>
		</p>
		<p>
			<h4>If anyone is allowed to Post anonymously, surely the Boards will be flooded with garbage Posts.</h4>
			This thought is not necessarily far-fetched. We saw that happen on most other popular Imageboards, and do not want Huechan to also end up like this. We have a set of strict <a href="/rules">Rules</a>, that anyone posting on any Board has to stick to. If you haven't done yet, please take a few minutes and read them, to make sure your Posts do comply with them. In addition, every Board has its own set of rules, in which are stated, which contents are desirable on the specific Board and which are not.
		</p>
		<p>
			<h4>Well, on other Imageboards I am free to Post anything I want. I do not want to use this!</h4>
			If you, for any reason are not satisfied with Huechan and are not planning to use it, we understand that. But we want Huechan to stay clean and safe for everyone and would rather take it down completely than watch it become such a hellhole like other popular Imageboards. If you do not like the Rules and are not willing to comply with them for whatever reason, please do not use this site. Anyone else is welcome.
		</p>
		<p>
			<h4>This does not look like the Imageboards I'm used to.</h4>
			Huechan was created with the motivation to bring the concept of Imageboards up to date. The Internet has aged a lot in the last 10-15 years, but the user interface of Imageboards have been stuck in the early 2000s still to this day. Huechan has a more modern design and we like it. If you don't, we respect that, but we won't change it to the kind of classic interface derived from other Imageboards.
		</p>
		<p>
			<h4>How are the rules enforced?</h4>
			Posts that do not comply with the Global Rules or the rules of the Board they are Posted on will get removed. If they significantly violated the rules, the Poster will also be banned from using Huechan for a limited time period. For repated or very serious violations, the Poster can be banned permanently.
		</p>
		<p>
			<h4>Do you collect any information of me when I'm using the platform?</h4>
			When first visiting the site, you are assigend a random ID internally called your 'user ID'. When you are Posting something, this ID together with your IP address is stored in our database. Your ID is essential to identifying you, if you want to delete one of your Posts for example. <b>This data is only visible to the Huechan Staff and is never shown anywhere on the site.</b>
		</p>
		<p>
			<h4>I saw a Post which I think violates the Rules. How do I report it to you?</h4>
			Use our integrated Reporting feature to report Posts you think are not complying with the rules. Just click on the top of the Post (the name and timestamp) and choose 'Report'. Reporting content posted by Staff is not possible. This is intentional and not a bug.
		</p>
		<p>
			<h4>What software does Huechan use behind the scenes?</h4>
			Huechan uses some open-source frameworks and tools but the main part was developed solely by us and does not use any public Imageboard software. We are not planning to release our source code in the near future.
		</p>
		<p>
			<h4>Can I get staff status?</h4>
			We are currently not looking for voluntary staff members.
		</p>
		<p>
			<h4>Am I allowed to Post non-English content?</h4>
			Yes and no. On most Boards, you are to Post in English and Posts in other languages (or generally just undecipherable Posts) will be removed. One exception is /y/, where any language is allowed.
		</p>
		<p>With that said, welcome to Huechan! Enjoy your stay.</p>
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