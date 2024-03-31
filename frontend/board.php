<?php
	// Check if Board actually exists and if it does, get name, description and pinned Post
	$statement = $database->prepare("SELECT `name`, `description`, CONV(`pinned`, 10, 16) FROM `boards` WHERE `id` = ?;");
	$statement->execute([$board_id]);
	$results = $statement->fetchAll();
	$board_name = $results[0][0];
	$board_description = $results[0][1];
	$board_pinned = correctIDOutputFromDB($results[0][2]);

	// Get pinned Post if not null
	if ($board_pinned != null) { $board_pinned = getPostAssoc($database, $board_pinned, $uid, true); }
	$board_pinned_content = shortenContent($board_pinned["content"]);
	$board_pinned_content_shortened = $board_pinned["content"] != $board_pinned_content;

	// Get banner path
	$board_banner = get_image_path($board_id.'-b', true);
	$board_icon = get_image_path($board_id.'-t', true);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=$board_name?> - Huechan</title>

	<meta property="og:type" content="website">
	<meta property="og:url" content="https://huechan.com/<?=$_SERVER["REQUEST_URI"]?>">
	<meta property="og:title" content="<?=$board_name?>">
	<meta property="og:description" content="<?=$board_description?>">
	<meta property="og:image" content="https://huechan.com<?=$board_icon?>">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="<?=$board_name?>">
	<meta name="twitter:description" content="<?=$board_description?>">
	<meta name="twitter:image" content="https://huechan.com<?=$board_icon?>">
	<meta name="twitter:image:src" content="https://huechan.com<?=$board_icon?>">

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
				// Toggle star button, if board is starred
				if (JSON.parse(localStorage.hc_data).starred.includes(<?='"'.$board_id.'"'?>)) {
					document.getElementById("board-star-icon").setAttribute("class", "fas fa-star");
					document.getElementById("board-star-btn").setAttribute("data-bs-original-title", "Unstar this Board");
				}
				loadPosts(false);
			<?php if ($uid == null): ?>
				}
			<?php endif; ?>
		};

		var lastPostTime = 0;
		var postlistPosts = new Object();

		function loadPosts(append = true) {
			var http = new XMLHttpRequest();
			http.open("GET", "/ext/filter_posts?by_board=<?=$board_id?>&noreplies=1&return_full=1"+(append ? "&offset_time="+lastPostTime : ""));
			http.onreadystatechange = function() {
				if (this.readyState == 4) {
					if (this.responseText.slice(0, 2) == "OK") {
						const posts = JSON.parse(this.responseText.slice(3));
						if (posts[0] == "last") {
							document.getElementById("postlist-error").style = "";
							var postDummys = document.getElementsByClassName("post-dummy");
							for (var i = 0; i < postDummys.length; i++) { postDummys[i].style = "cursor: default"; }
							var placeholders = document.getElementsByClassName("placeholder");
							for (var i = 0; i < placeholders.length; i++) { placeholders[i].style = "cursor: default"; }
							positionBottomNavbar();
							enableTooltips();
							setupPostPopovers();
							return null;
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
						for (var i = 0; i < postDummys.length; i++) { postDummys[i].hidden = true; }
						return null;
					}
				}
			}
			http.send();
		}

		window.onresize = positionBottomNavbar;

		function toggleStarButton() {
			var iconState = document.getElementById("board-star-icon").getAttribute("class");
			if (iconState === "far fa-star") {
				// Add board to localStorage.hc_data.starred[]
				var hcdata = JSON.parse(localStorage.hc_data);
				hcdata.starred.push(<?='"'.$board_id.'"'?>);
				hcdata.starred_names.push(<?='"'.$board_name.'"'?>);
				localStorage.hc_data = JSON.stringify(hcdata);
				document.getElementById("board-star-icon").setAttribute("class", "fas fa-star");
				document.getElementById("board-star-btn").setAttribute("data-bs-original-title", "Unstar this Board");
			} else {
				// Remove board from localStorage.hc_data.starred[]
				var hcdata = JSON.parse(localStorage.hc_data);
				var starred = hcdata.starred;
				var starred_names = hcdata.starred_names;
				starred.splice(starred.indexOf(<?='"'.$board_id.'"'?>), 1);
				starred_names.splice(starred_names.indexOf(<?='"'.$board_name.'"'?>), 1);
				hcdata.starred = starred;
				hcdata.starred_names = starred_names;
				localStorage.hc_data = JSON.stringify(hcdata);
				document.getElementById("board-star-icon").setAttribute("class", "far fa-star");
				document.getElementById("board-star-btn").setAttribute("data-bs-original-title", "Star this Board");
			}
			fillStarredMenu();
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
						loadPosts();
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
			max-width: 15em;
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

		.post-content {
			cursor: pointer;
			max-width: 90em !important;
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
	<span id="root">
		<br><br>
		<?=retrieveNavbar($board_id, getBanType($database, $uid, $user_ip), $user_role !== null)?>
		<div style="margin-top: 8px;"></div>

		<?php if ($maintenance_mode): ?>
			<br>
			<div style="margin-top: -8px;"></div>
			<div class="container-sm">
				<div class="alert alert-primary" role="alert">Huechan is currently in maintenance mode. Please remember to disable it after finishing your work.</div>
			</div>
			<div style="margin-top: 16px;"></div>
		<?php endif; ?>

		<?php if ($board_banner != null): ?>
			<div class="container-fluid" style="padding: 0">
				<img src="<?=$board_banner?>" style="width: 100%;">
			</div>
		<?php endif; ?>
		<?php if (!$maintenance_mode || $board_banner != null): ?><br><?php endif; ?>
		<div class="container-sm">
			<h1 class="center-h"><?=$board_name?></h1>
			<p class="center-h"><?=$board_description?></p>
			<p class="center-h">
				<button type="button" id="board-star-btn" onclick="toggleStarButton();" class="btn btn-warning" data-bs-hover="tooltip" data-bs-placement="top" title="Star this Board"><i id="board-star-icon" class="far fa-star"></i></button>
				<span style="margin-left: 8px;">
				<?=
					$board_id == "z" && $user_role != 1 ? "" :
					'<button type="button" class="btn btn-success" onclick="openNewPostOffcanvas(\'\', \''.$board_id.'\', \''.$board_name.'\');" data-bs-hover="tooltip" data-bs-placement="top" title="Post"><i class="fas fa-plus"></i></button><span style="margin-left: 4px;">';
				?>
				<button type="button" class="btn btn-primary" onclick="document.getElementById('search-bar').focus();" data-bs-hover="tooltip" data-bs-placement="top" title="Search in Board"><i class="fas fa-search"></i></button>
			</p>
		</div>
		<div style="margin-top: 32px;"></div>
		<hr>
		<div style="margin-top: 32px;"></div>
		<div class="container-sm">
			<?php if ($board_pinned["id"] != null): ?>
				<div id="post-<?=$board_pinned["id"]?>-spacer" style="margin-top: 16px;"></div>
				<div id="post-<?=$board_pinned["id"]?>" class="post-op bg-dark">
					<div class="dropend">
						<i class="fas fa-thumbtack fa-sm" data-bs-hover="tooltip" data-bs-placement="top" title="Pinned"></i><span style="margin-left: 4px;"></span>
						<span class="dropdown-toggle" id="post-<?=$board_pinned["id"]?>-dropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
							<b class="text-danger">Anonymous (Staff)</b>
							<?='<span id="date-op">'.$board_pinned["datetime"].'</span><script>document.getElementById("date-op").innerHTML = getTimeString(document.getElementById("date-op").innerHTML)</script>';?>
						</span>
						<span style="margin-left: 4px;"></span>
						<a data-bs-hover="tooltip" data-bs-placement="top" title="Replying disabled" style="cursor: not-allowed;"><i class="fas fa-reply"></i><span style="margin-left: 6px;"></span><?=$board_pinned["replies"]?></a>
						<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="post-<?=$board_pinned["id"]?>-dropdown-menu">
							<li><a class="dropdown-item disabled text-secondary">No options available.</a></li>
						</ul>
					</div>
					<div style="margin-top: 8px;"></div>
					<div class="post-content" onclick="window.location.href='/<?=$board_pinned["board"]?>/<?=$board_pinned["id"]?>';">
						<table>
							<tr>
								<?php if ($board_pinned["image"] != null): ?><td style="padding-right: 12px;"><img src="<?=$board_pinned["image"]?>" width="192" class="post-image"></td><?php endif; ?>
								<td style="vertical-align: top; max-width: 40% !important;">
									<?=$board_pinned_content?>
									<?php if ($board_pinned_content_shortened): ?><span style="margin-left: 8px;"></span><a href="/'+board+'/'+id+'">Show more</a><?php endif; ?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<?php endif; ?>
			<div class="post-dummy-spacer"></div>
			<div class="post-op bg-dark center-h" id="postlist-error" style="display: none !important;">
				This Board does not have any Posts yet.
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