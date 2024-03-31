<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Home - Huechan</title>

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
				loadContent(false);
			<?php if ($uid == null): ?>
				}
			<?php endif; ?>
		};

		var lastPostTime = 0;
		var postlistPosts = new Object();

		function loadContent(append = true) {
			if (JSON.parse(localStorage.hc_data).starred.length == 0) {
				document.getElementById("postlist-error").style = "";
				var postDummys = document.getElementsByClassName("post-dummy");
				for (var i = 0; i < postDummys.length; i++) { postDummys[i].style = "cursor: default"; }
				var placeholders = document.getElementsByClassName("placeholder");
				for (var i = 0; i < placeholders.length; i++) { placeholders[i].style = "cursor: default"; }
				return null;
			}
			// Submit data
			var http = new XMLHttpRequest();
			http.open("GET", "/ext/get_timeline?starred="+JSON.stringify(JSON.parse(localStorage.hc_data).starred)+(append ? "&offset_time="+lastPostTime : ""));
			http.onreadystatechange = function() {
				if (this.readyState == 4) {
					if (this.responseText.slice(0, 2) == "OK") {
						const posts = JSON.parse(this.responseText.slice(3));
						if (posts[0] == "last" && !append) {
							document.getElementById("postlist-error").innerHTML = "There are currently no Posts available.";
							document.getElementById("postlist-error").style = "";
							var postDummys = document.getElementsByClassName("post-dummy");
							for (var i = 0; i < postDummys.length; i++) { postDummys[i].style = "cursor: default"; }
							var placeholders = document.getElementsByClassName("placeholder");
							for (var i = 0; i < placeholders.length; i++) { placeholders[i].style = "cursor: default"; }
							return;
						}
						postlistPosts = {};
						posts.forEach((currentValue, index, arr) => {
							if (currentValue !== "last") {
								lastPostTime = currentValue["datetime"];
								// Preload image, if there is one
								if (currentValue["image"] != null) {
									const image = new Image();
									image.addEventListener("load", () => {
										var postUI = getPostUI(currentValue);
										postlistPosts[index] = postUI;
										if (Object.keys(postlistPosts).length == (arr.length-(arr[arr.length-1]=="last"?1:0))) {
											if (!append) { document.getElementById("postlist").innerHTML = ""; }
											Object.entries(Object.keys(postlistPosts).sort().reduce((result, key) => {
												result[key] = postlistPosts[key];
												return result;
											}, {})).forEach((currentValue, index, arr) => {
												document.getElementById("postlist").innerHTML += currentValue[1];
											}, true);
											positionBottomNavbar();
											enableTooltips();
											setupPostPopovers();
										}
									}, true);
									image.src = currentValue["image"];
								} else {
									var postUI = getPostUI(currentValue);
									postlistPosts[index] = postUI;
									if (Object.keys(postlistPosts).length == (arr.length-(arr[arr.length-1]=="last"?1:0))) {
										if (!append) { document.getElementById("postlist").innerHTML = ""; }
										Object.entries(Object.keys(postlistPosts).sort().reduce((result, key) => {
											result[key] = postlistPosts[key];
											return result;
										}, {})).forEach((currentValue, index, arr) => {
											document.getElementById("postlist").innerHTML += currentValue[1];
										}, true);
										positionBottomNavbar();
										enableTooltips();
										setupPostPopovers();
									}
								}
							} else { document.getElementById("post-dummy-last").hidden = true; }
						});
						updatecookies();
						observeLastDummyPost();
					} else {
						document.getElementById("postlist-error").innerHTML = "An error has occured. Please try again later.";
						document.getElementById("postlist-error").style = "";
						var postDummys = document.getElementsByClassName("post-dummy");
						for (var i = 0; i < postDummys.length; i++) { postDummys[i].style = "cursor: default"; }
						var placeholders = document.getElementsByClassName("placeholder");
						for (var i = 0; i < placeholders.length; i++) { placeholders[i].style = "cursor: default"; }
					}
				}
			}
			http.send();
		}

		function observeLastDummyPost() {
			if (document.getElementById("postlist-error").style == "") { return null; }
			let options = {
				root: null,
				rootMargin: '0px',
				threshold: 0.1
			}

			let observer = new IntersectionObserver((entries, observer) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.classList.add('in-viewport');
						loadContent(true);
						entry.target.classList.remove('in-viewport');
					} else {
						entry.target.classList.remove('in-viewport');
					}
				});
			}, options);
			observer.observe(document.querySelector("#post-dummy-last"));
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
		}

		p.post-message {
			font-size: 18pt;
		}

		.board-tn {
			border-radius: 100%;
			margin-right: 16px;
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

	<span id="root">
		<br><br><br>
		<?php if ($maintenance_mode): ?>
			<div class="container-sm">
				<div class="alert alert-primary" role="alert">Huechan is currently in maintenance mode. Please remember to disable it after finishing your work.</div>
			</div>
		<?php endif; ?>
		<div class="container-sm">
			<p><h1>Home</h1></p>
			<div class="post-op bg-dark center-h" id="postlist-error" style="display: none !important;">
				You have not starred any Boards yet.&nbsp;
				<a href="/boards">Explore Boards</a>
			</div>
			<div id="postlist">
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
			</div>
			<div id="post-dummy-last">
				<div class="post-dummy-spacer"></div>
				<div class="post-op post-dummy bg-dark">
					<div>
						<span class="placeholder col-3"></span>
					</div>
					<div style="margin-top: 8px;"></div>
					<div>
						<div>
							<span class="placeholder col-4"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-3"></span>
							<span class="placeholder col-5"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-1"></span>
							<span class="placeholder col-2"></span>
							<span class="placeholder col-5"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br><br>
	</span>

	<?php
		echo retrieveBottomNavbar(false);
		echo retrieveCookieModal();
		echo retrieveModals();
		if ($user_role !== null) { echo retrieveModModals(); }
		echo retrieveNewPostOffcanvas();
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