<?php
	// Check if Post actually exists and if it does, get name of author and Board
	$statement = $database->prepare("SELECT `name`, `board` FROM `posts` WHERE `id` = CONV(?, 16, 10);");
	$statement->execute([$post_id]);
	$results = $statement->fetchAll();
	if ($results == []) { http_response_code(404); exit(); }
	$post_by = $results[0][0] != null ? htmlentities($results[0][0]) : "Anonymous";
	$board_id = $results[0][1];

	$post = getPostAssoc($database, $post_id, $uid, true, true);
	if ($post == 0) {
		// post removed. todo
	}

	$post_content_shortened = shortenContent($post["contentRaw"]);

	$board_name = $post["boardname"];

	// Get description of Board
	$statement = $database->prepare("SELECT `description` FROM `boards` WHERE `id` = ?;");
	$statement->execute([$board_id]);
	$board_description = $statement->fetchAll()[0][0];

	// Get path to Board thumbnail
	$board_thumbnail = get_image_path($board_id.'-t', true);

	// If poll attached and user has voted on poll, get vote
	$poll_vote = checkIfUserHasVotedInPoll($database, $post["id"], $uid);

	// If post removed, get details of removal
	if (in_array("removed", $post["flags"])) {
		$removal = getRemovalAssoc($database, $post["id"]);
		switch ($removal["reason"]) {
			case 0:
				$removal_reason_text = "Posting hate speech (Global rule 1)";
				break;

			case 1:
				$removal_reason_text = "Posting content not fitting into its Board (Global rule 2)";
				break;

			case 2:
				$removal_reason_text = "Posting NSFW content (Global rule 3)";
				break;

			case 3:
				$removal_reason_text = "Posting private information (Global rule 4)";
				break;

			case 4:
				$removal_reason_text = "Posting illegal content (Global rule 5)";
				break;

			case 5:
				$removal_reason_text = "Spamming (Global rule 6)";
				break;

			case 6:
				$removal_reason_text = "Posting non-English content (Global rule 7)";
				break;

			case 7:
				$removal_reason_text = "Bypassing bans (Global rule 8)";
				break;

			case 8:
				$removal_reason_text = "Violating Board Rules (Global rule 9)";
				break;

			case 9:
				$removal_reason_text = "Other";
				break;

			case 10:
				$removal_reason_text = "Abusing the Reporting feature (Global rule 10)";
				break;
			
			default:
				$removal_reason_text = "<i>No reason provided.</i>";
				break;
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Post <?=($post_by != "Anonymous" ? "by $post_by " : "")?>on /<?=$board_id?>/ - Huechan</title>

	<meta property="og:type" content="website">
	<meta property="og:url" content="https://huechan.com/<?=$_SERVER["REQUEST_URI"]?>">
	<meta property="og:title" content="Post <?=($post_by != "Anonymous" ? "by $post_by " : "")?>on /<?=$board_id?>/">
	<meta property="og:description" content="<?=$post_content_shortened.($post["contentRaw"] == $post_content_shortened ? "" : "...")?>">
	<meta property="og:image" content="https://huechan.com<?=$post["image"]?>">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="Post <?=($post_by != "Anonymous" ? "by $post_by " : "")?>on /<?=$board_id?>/">
	<meta name="twitter:description" content="<?=$post_content_shortened.($post["contentRaw"] == $post_content_shortened ? "" : "...")?>">
	<meta name="twitter:image:src" content="https://huechan.com<?=$post["image"]?>">
	<meta name="twitter:image" content="https://huechan.com<?=$post["image"]?>">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/afd60586d3.js" crossorigin="anonymous"></script>

	<script src="/ext/cookies.js"></script>
	<?php if ($user_role !== null): ?><script src="/ext/modactions.js"></script><?php endif; ?>

	<script>
		window.onload = initPage;
		window.onresize = positionBottomNavbar;

		function initPage(first = false) {
			<?php if ($post == 0): ?>
				document.getElementById("postlist-error").innerHTML = "This Post was removed by Staff due to a violation of the Rules.";
			<?php endif; ?>

			document.getElementById("name-op").innerHTML = getNameLine(<?=$post["name"] ? $post["name"] : "''"?>, JSON.parse('<?=json_encode($post["groups"])?>'));

			<?php if ($uid == null): ?>
				if (first) {
					new bootstrap.Modal(document.getElementById("cookie-modal"), {
						backdrop: "static",
						keyboard: false
					}).show();
				} else {
			<?php endif; ?>
			<?php if (isset($post["poll"]["results"])): ?>
				displayPollResults("<?=$post["id"]?>", <?=json_encode($post["poll"]["results"])?>, <?=($poll_vote !== false ? $poll_vote : "null")?>)
			<?php endif; ?>
				loadReplies(false);
			<?php if ($uid == null): ?>
				}
			<?php endif; ?>
		}

		var lastPostTime = 0;
		var postlistPosts = new Object();

		function loadReplies(append = true) {
			var http = new XMLHttpRequest();
			http.open("GET", "/ext/filter_posts?by_inreplyto="+"<?=$post_id?>"+"&return_full=1"+(append ? "&offset_time="+lastPostTime : ""));
			http.onreadystatechange = function() {
				if (this.readyState == 4) {
					if (this.responseText.slice(0, 2) == "OK") {
						const posts = JSON.parse(this.responseText.slice(3));
						if (posts[0] == "last") {
							document.getElementById("postlist-error").style = "";
							var postDummys = document.getElementsByClassName("post-dummy");
							for (var i = 0; i < postDummys.length; i++) { postDummys[i].hidden = true; }
							positionBottomNavbar();
							updatecookies();
							enableTooltips();
							setupPostPopovers();
							return;
						}
						updatecookies();
						posts.forEach((currentValue, index, arr) => {
							if (currentValue !== "last") {
								lastPostTime = currentValue["datetime"];
								if (currentValue["image"] != null) {
									const image = new Image();
									image.addEventListener("load", () => {
										var postUI = getPostUI(currentValue);
										postlistPosts[index] = postUI;
										if (Object.keys(postlistPosts).length == (arr.length-(arr[arr.length-1]=="last"?1:0))) {
											if (!append) { document.getElementById("postlist").innerHTML = ""; }
											document.getElementById("postlist").innerHTML = "";
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
										document.getElementById("postlist").innerHTML = "";
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

		function loadThread() {
			document.getElementById("postlist-thread-loadbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin' style='font-size: 1.5rem'></i>";
			var http = new XMLHttpRequest();
			http.open("GET", "/ext/get_thread?id=<?=$post_id?>");
			http.onreadystatechange = function() {
				if (this.readyState == 4) {
					if (this.responseText.slice(0, 2) == "OK") {
						const posts = JSON.parse(this.responseText.slice(3));
						document.getElementById("postlist-thread").innerHTML = "";
						posts.forEach((currentValue, index, arr) => {
							if (currentValue == null) { var postUI = '<div style="margin-top: 16px;"></div><div class="post-op bg-dark" class="post-op bg-dark"><i class="text-secondary">This Post was deleted by the author.</i></div>'; }
							else if (currentValue == 0) { var postUI = '<div style="margin-top: 16px;"></div><div id="post-op bg-dark" class="post-op bg-dark"><i class="text-secondary">This Post was removed by staff due to a violation of the Rules.</i></div>'; }
							else { var postUI = getPostUI(currentValue, false); }

							document.getElementById("postlist-thread").innerHTML += postUI;
						});
						positionBottomNavbar();
						enableTooltips();
						setupPostPopovers();
					} else {
						document.getElementById("postlist-thread-loadbtn").innerHTML = "An error has occured. Please try again later.";
						document.getElementById("postlist-thread-loadbtn").setAttribute("class", "post-op bg-dark post-navelement center-h");
						var postDummys = document.getElementsByClassName("post-dummy");
						for (var i = 0; i < postDummys.length; i++) { postDummys[i].hidden = true; }
						return null;
					}
				}
			}
			http.send();
		}

		function observeLastDummyPost() {
			if (document.getElementById("postlist-error").style == "") { return null; }

			let observer = new IntersectionObserver((entries, observer) => { entries.forEach(entry => {
				if (entry.isIntersecting) {
					entry.target.classList.add('in-viewport');
					loadReplies();
					entry.target.classList.remove('in-viewport');
				} else { entry.target.classList.remove('in-viewport'); }
			}); }, { root: null, rootMargin: '0px', threshold: 0.1 });

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
	<?=retrieveNavbar($board_id, getBanType($database, $uid, $user_ip), $user_role !== null)?>

	<span id="root">
		<br><br><br>
		<div class="container-sm">
			<?php if ($maintenance_mode): ?>
				<div class="alert alert-primary" role="alert">Huechan is currently in maintenance mode. Please remember to disable it after finishing your work.</div>
			<?php endif; ?>
			<div style="margin-top: 16px;"></div>
			<?php if ($post != 0): ?>
				<div class="post-op bg-dark post-navelement" onclick="window.location.href = '/<?=$board_id?>/'">
					<table>
						<tr>
							<?php if ($board_thumbnail != null): ?>
								<td>
									<img src="<?=$board_thumbnail?>" width="48" class="board-tn">
								</td>
							<?php endif ?>
							<td>
								<b><?=$board_name?></b><br>
								<?=$board_description?>
							</td>
						</tr>
					</table>
				</div>
				<?php if ($post["inreplyto"] != null): ?>
					<div style="margin-top: 16px;"></div>
					<div id="postlist-thread">
						<div onclick="loadThread();" id="postlist-thread-loadbtn" class="post-op bg-dark post-navelement text-primary center-h">Show full Thread</div>
					</div>
				<?php endif; ?>
				<?php if (in_array("removed", $post["flags"])): ?>
					<div style="margin-top: 16px;"></div>
					<div class="post-op bg-dark">
						<b>This Post has been removed by staff.</b><br>
						Reason: <?=$removal_reason_text?><br>
						<?php if ($removal["details"] !== null): ?>Details: <?=$removal["details"]?><br><?php endif; ?>
					</div>
				<?php endif; ?>
				<div id="post-<?=$post_id?>-spacer" style="margin-top: 16px;"></div>
				<div id="post-<?=$post_id?>" class="post-op post-op-big bg-dark">
					<div class="dropend">
						<?php if (in_array("pinned", $post["flags"])): ?>
							<i class="fas fa-thumbtack fa-sm" data-bs-hover="tooltip" data-bs-placement="top" title="Pinned"></i><span style="margin-left: 4px;"></span>
						<?php endif; ?>
						<?php if (in_array("removed", $post["flags"])): ?>
							<i class="fas fa-eye-slash fa-sm" data-bs-hover="tooltip" data-bs-placement="top" title="Removed by Staff"></i><span style="margin-left: 4px;"></span>
						<?php endif; ?>
						<span class="dropdown-toggle" id="post-<?=$post_id?>-dropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
							<?php
								echo '<span id="name-op"></span> ';
								echo '<span id="date-op">'.$post["datetime"].'</span><script>document.getElementById("date-op").innerHTML = getTimeString(document.getElementById("date-op").innerHTML, true)</script>';
							?>
						</span>
						<span style="margin-left: 6px;"></span>
						<?php if (in_array("noreplies", $post["flags"])): ?>
							<a data-bs-hover="tooltip" data-bs-placement="top" title="Replying disabled" style="cursor: not-allowed;"><i class="fas fa-reply"></i><span style="margin-left: 6px;"></span><?=$post["replies"]?></a>
						<?php else: ?>
							<a href="javascript:;" data-bs-hover="tooltip" data-bs-placement="top" title="Reply" onclick="openNewPostOffcanvas('<?=$post_id?>', '<?=$board_id?>', '<?=$board_name?>');"><i class="fas fa-reply"></i><span style="margin-left: 6px;"></span><?=$post["replies"]?></a>
						<?php endif; ?>
						<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="post-<?=$post_id?>-dropdown-menu">
							<?php if (!in_array("noreplies", $post["flags"])): ?>
								<li><a class="dropdown-item" onclick="openNewPostOffcanvas('<?=$post_id?>', '<?=$board_id?>', '<?=$board_name?>');">Reply</a></li>
							<?php endif; ?>
							<?php if (!in_array("staff", $post["flags"]) && !in_array("removed", $post["flags"])): ?>
								<li><a class="dropdown-item" onclick="openReportPostModal('<?=$post_id?>', '<?=$board_id?>');">Report</a></li>
							<?php endif; ?>
							<?php if (in_array("author", $post["flags"]) && !in_array("removed", $post["flags"])): ?>
								<li><a class="dropdown-item text-danger" onclick="openDeletePostModal('<?=$post_id?>');">Delete</a></li>
							<?php endif; ?>
							<?php if (in_array("operator", $post["flags"]) && !in_array("removed", $post["flags"])): ?>
								<li><a class="dropdown-item text-danger" onclick="openModRemovePostModal('<?=$post_id?>', '<?=$board?>')"><i class="fas fa-user-shield"></i>&nbsp; Remove Post</a></li>
							<?php endif; ?>
							<?php if (!(!in_array("noreplies", $post["flags"]) || (!in_array("staff", $post["flags"]) && !in_array("removed", $post["flags"])) || (in_array("author", $post["flags"]) && !in_array("removed", $post["flags"])) || (in_array("operator", $post["flags"]) && !in_array("removed", $post["flags"])))): ?>
								<li><a class="dropdown-item disabled text-secondary">No options available.</a></li>
							<?php endif; ?>
						</ul>
					</div>
					<?php if ($post["inreplyto"] != null): ?>
						<table>
							<tr>
								<td><i class="fas fa-reply"></i><span style="margin-left: 8px"></span></td>
								<td><b>Replying to:</b> <a class="post-href" href="<?="/".$board_id."/".$post["inreplyto"]?>"><?=$post["inreplyto"]?></a></td>
							</tr>
						</table>
					<?php endif; ?>
					<div style="margin-top: 8px;"></div>
					<div>
						<span class="post-message"><?=$post["content"]?></span>
						<?php if (isset($post["poll"])): ?>
							<div style="margin-top: 8px;"></div>
							<div id="post-<?=$post_id?>-poll">
								<b class="text-secondary">Poll (<?=getRemainingTimeString($post["poll"]["end"])?>)</b>
								<div style="margin-top: 8px;"></div>
								<div class="d-grid gap-2">
									<?php
										foreach ($post["poll"]["options"] as $optionId => $optionName) {
											echo "<button id=\"post-".$post_id."-poll-".$optionId."\" class=\"btn btn-secondary post-".$post_id."-poll-button\" type=\"button\" onclick=\"voteInPoll('".$post_id."', ".$optionId.")\" style=\"width: 50%\" _hc_btn_orig=\"".$optionName."\">".$optionName."</button>";
										}
									?>
								</div>
								<div style="margin-top: 8px;"></div>
								<span id="post-<?=$post_id?>-poll-error" class="text-danger"></span>
							</div>
						<?php endif; ?>
						<?php if ($post["image"] != null): ?>
							<div style="margin-top: 16px;"></div>
							<img src="<?=$post["image"]?>" class="post-image-big img-fluid">
						<?php endif; ?>
					</div>
				</div>
				<div style="margin-top: 16px;"></div>
			<?php endif; ?>
			<div class="post-op bg-dark center-h" id="postlist-error" style="display: none !important;">
				This Post does not have any Replies yet.
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
		<br>
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