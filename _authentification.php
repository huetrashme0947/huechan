<?php
	function checkIfNewUser() {
		// Check if cookies are set
		return !isset($_COOKIE["aph"]) || !isset($_COOKIE["uu"]);
	}

	function generateUID($database, $uip, $xff = null) {
		// Generate user ID (generate 8 bytes randomly, translate to hex, so you get 16 byte hex number)
		while (true) {
			$id = bin2hex(random_bytes(8));

			$statement = $database->prepare("SELECT count(*) FROM `uids` WHERE `id` = CONV(?, 16, 10);");
			$statement->execute([$id]);

			// If the generated ID is not taken yet, assign. Else, regenerate
			if ($statement->fetchAll()[0][0] == 0) {
				$statement = $database->prepare("INSERT INTO `uids` (`id`, `ip` `xff`) VALUES (CONV(?, 16, 10), INET_ATON(?), INET_ATON(?));");
				$statement->execute([$id, $uip, $xff]);
				return $id;
			}
		}
	}

	function checkIfUIDIsTaken($database, $uid) {
		$statement = $database->prepare("SELECT count(*) FROM `uids` WHERE `id` = CONV(?, 16, 10);");
		$statement->execute([$uid]);
		return $statement->fetchAll()[0][0] != 0;
	}

	function checkIfBanned($database, $uid = null, $ip) {
		// Retrieve all bans for corresponding UID and IP and check if there is any which is currently in force
		$statement = $database->prepare("SELECT `start`, `type` FROM `bans` WHERE `uid` = CONV(?, 16, 10) OR `ip` = INET_ATON(?) ORDER BY `start` DESC;");
		$statement->execute([$uid, $ip]);
		$results = $statement->fetchAll();

		// Get end of ban
		if ($results == []) {
			// Never banned
			return false;
		} else if ($results[0][1] == 0) {
			// 48 hours
			return DateTime::createFromFormat("Y-m-d H:i:s", $results[0][0])->getTimestamp() + 172800 > time();
		} else if ($results[0][1] == 1) {
			// 14 days
			return DateTime::createFromFormat("Y-m-d H:i:s", $results[0][0])->getTimestamp() + 1209600 > time();
		} else {
			// Permanent
			return true;
		}
	}

	function getBanType($database, $uid = null, $ip) {
		if (!checkIfBanned($database, $uid, $ip)) { return null; }
		// Get type of current ban of user
		$statement = $database->prepare("SELECT `type` FROM `bans` WHERE `uid` = CONV(?, 16, 10) OR `ip` = INET_ATON(?) ORDER BY `start` DESC;");
		$statement->execute([$uid, $ip]);
		return $statement->fetchAll()[0][0];
	}

	function checkIfAuthorized($database, $authkey, $uid) {
		// Get authkey and check if matching with provided one
		return generateAuthkey($database, $uid) == $authkey;
	}

	function generateAuthkey($database, $uid) {
		// Generate authkey (retrieve IDs of last 20 not deleted Posts, concatenate to one string, hash)
		$statement = $database->prepare("SELECT CONV(`id`, 10, 16) FROM `posts` WHERE `uid` = CONV(?, 16, 10) AND (`v` IS NULL OR `v` = 0 OR `v` = 1) ORDER BY `datetime` DESC LIMIT 20;");
		$statement->execute([$uid]);
		$generated_authkey = $uid;
		$results = $statement->fetchAll();
		foreach ($results as $col) { $generated_authkey .= correctIDOutputFromDB(strtolower($col[0])); }
		return hash("sha256", $generated_authkey);
	}

	function correctIDOutputFromDB($id) {
		if ($id == null) { return $id; }
		while (strlen($id)<16) { $id = "0".$id; }
		return strtolower($id);
	}

	function checkRole($database, $uid) {
		// Retrieve role column of corresponding UID. If there is any, return role, else return null
		$statement = $database->prepare("SELECT `role` FROM `roles` WHERE `uid` = CONV(?, 16, 10);");
		$statement->execute([$uid]);
		$results = $statement->fetchAll();
		return $results == [] ? null : $results[0][0];
	}

	// Deprecated, does not work anymore
	function authorizeAsStaff($database, $uid, $privilege_key) {
		// Search for provided key
		$statement = $database->prepare("SELECT `role`, `id` FROM `privkeys` WHERE `str` = ?;");
		$statement->execute([$privilege_key]);
		$results = $statement->fetchAll();
		if ($results != []) {
			// Provided key is valid, so assign corresponding role to UID
			$statement = $database->prepare("INSERT INTO `roles` (`uid`, `role`, `privkey`) VALUES (?, ?, ?);");
			$statement->execute([$uid, $results[0][0], $results[0][1]]);
			return $results[0][0];
		} else {
			// Provided key invalid, so return error
			return false;
		}
	}

	function retrieveNavbar($searchbar_target = null, $ban_mode = null, $staff = false) {
		if ($ban_mode === null) {
			return '<nav id="navbar-top" class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
						<div class="container">
							<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-icon"></span>
							</button>
							<div class="collapse navbar-collapse" id="navbarSupportedContent">
								<ul class="navbar-nav me-auto mb-2 mb-lg-0">
									<li class="nav-item">
										<a class="nav-link" href="/">Home</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" href="/boards">Boards</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" id="starred-dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Starred</a>
										<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="starred-dropdown" id="starred-dropdown-content">
											<li><a class="dropdown-item disabled">No Boards starred.</a></li>
										</ul>
									</li>
									<script>fillStarredMenu();</script>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
										<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
											<li><a class="dropdown-item" href="/myposts">My Posts</a></li>
											<li><a class="dropdown-item" href="/help">Help</a></li>
											<li><a class="dropdown-item" href="/about">About</a></li>'.
											($staff ? '<li><a class="dropdown-item" href="/mod_reports"><i class="fas fa-user-shield"></i>&nbsp; Pending Reports</a></li>' : "")
										.'</ul>
									</li>
								</ul>
								<a class="me-auto navbar-brand" href="/"><img src="/ext/images/huechan.png" width="110" style="margin-top: -4px"></a>
								<span style="margin-left: 212px;"></span>'.
								(false ? "" : '<a class="me-2 text-light" id="notifications-button" onclick="document.getElementById("notifications-button").innerHTML = \'<i class="fas fa-bell fa-lg"></i>\';" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" title="Notifications" data-bs-html="true" data-bs-content=\'<div class="text-light">There are no new notifications.</div>\'><i class="fas fa-bell fa-lg"></i></a>')
								.'<span style="margin-left: 8px;"></span>
								<a class="me-2 text-light" data-bs-toggle="modal" data-bs-target="#search-modal"><i class="fas fa-search fa-lg"></i></a>
							</div>
	  					</div>
	  				</nav>

	  				<div class="modal fade" id="search-modal">
						<div class="modal-dialog">
						<br>
							<div class="modal-content text-light" style="background-color: #171717 !important;">
								<form class="d-flex" action="'.($searchbar_target == null ? '' : '/'.$searchbar_target).'/search" method="GET">
									<input class="form-control me-2 bg-dark text-light" name="q" id="search-bar" style="border-width: 0; background-color: #171717 !important; font-size: 24px; padding: !important 32px;" type="search" placeholder="Search ('. ($searchbar_target == null ? 'Global' : ('/'.$searchbar_target.'/')) .')" aria-label="Search" required>
									<a style="position: absolute; top: 27.5%; right: 2.5%; color: #6c757d !important;" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times fa-lg"></i></a>
								</form>
							</div>
						</div>
					</div>';
		} else if ($ban_mode == 0 || $ban_mode == 1) {
			return '<nav id="navbar-top" class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
						<div class="container">
							<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-icon"></span>
							</button>
							<div class="collapse navbar-collapse" id="navbarSupportedContent">
								<ul class="navbar-nav me-auto mb-2 mb-lg-0">
									<li class="nav-item">
										<a class="nav-link" href="/">Home</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
										<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
											<li><a class="dropdown-item" href="/myposts">My Posts</a></li>
											<li><a class="dropdown-item" href="/help">Help</a></li>
											<li><a class="dropdown-item" href="/about">About</a></li>
										</ul>
									</li>
								</ul>
								<div class="me-auto navbar-brand">
									<a href="/"><img src="/ext/images/huechan.png" width="110" style="margin-top: -4px"></a>
								</div>
								<ul class="navbar-nav me-auto mb-2 mb-lg-0" style="visibility: hidden; margin: 0 !important">
									<li class="nav-item">
										<a class="nav-link" href="/">Home</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
										<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
											<li><a class="dropdown-item" href="/myposts">My Posts</a></li>
											<li><a class="dropdown-item" href="/help">Help</a></li>
											<li><a class="dropdown-item" href="/about">About</a></li>
										</ul>
									</li>
								</ul>
							</div>
	  					</div>
	  				</nav>';
		} else if ($ban_mode == 2) {
			return '<nav id="navbar-top" class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark" style="min-height: 56px">
						<div class="container">
							<div class="navbar-collapse" id="navbarSupportedContent">
								<ul class="navbar-nav mx-auto">
									<li class="mx-auto"><a class="navbar-brand" href="/"><img src="/ext/images/huechan.png" width="110" style="margin-top: -4px"></a></li>
								</ul>
							</div>
	  					</div>
	  				</nav>';
	  	}
	}

	function retrieveBottomNavbar($fixed = true) {
		return '<nav class="navbar '.($fixed ? 'fixed-bottom ' : '').' navbar-dark bg-dark" id="navbar-bottom" style="padding-top: 24px;">
					<div class="container">
						<p>&copy; 2022' . ((int)date("Y") > 2022 ? ("-".date("Y")) : "") . ' HUE_TrashMe</p>
						<p>
							<a href="/contact" style="margin-right: 16px;">Contact</a>
							<a href="/privacy" style="margin-right: 16px;">Privacy Policy</a>
							<a href="/rules" style="margin-right: 16px;">Rules</a>
							<a href="/support">Support</a>
						</p>
					</div>
				</nav>';
	}

	function retrieveModModals() {
		return '<div class="modal fade" id="mod-remove-post-modal">
					<div class="modal-dialog">
						<div class="modal-content bg-dark text-light">
							<div class="modal-header">
								<h4 class="modal-title"><i class="fas fa-user-shield"></i>&nbsp; Remove Post</h4>
							</div>
							<div class="modal-body">
								<form>
									<h5>Rule which was violated</h5>
									<select style="width: 100%" class="form-control" id="mod-remove-post-reason" onchange="checkIfModRemovalAllowed();">
										<option value="" id="mod-remove-post-reason-default">Please choose a reason.</option>
										<option value="0">G1 Posting hate speech</option>
										<option value="1">G2 Posting content not fitting into its Board</option>
										<option value="2">G3 Posting NSFW content</option>
										<option value="3">G4 Posting private information</option>
										<option value="4">G5 Posting illegal content</option>
										<option value="5">G6 Spamming</option>
										<option value="6">G7 Posting non-English content</option>
										<option value="7">G8 Bypassing bans</option>
										<option value="8">G9 Violating Board Rules (please specify which rule in the next field)</option>
										<option value="9">Other</option>
									</select>
									<br>
									<h5>Details</h5>
									<textarea class="form-control" id="mod-remove-post-details" rows="4" placeholder="Attach some details (optional)" oninput="checkIfModRemovalAllowed();"></textarea>
									<br>
									<h5>Ban user?</h5>
									<select style="width: 100%" class="form-control" id="mod-remove-post-ban" onchange="checkIfModRemovalAllowed();">
										<option value="">No ban</option>
										<option value="0">Ban for 48 hours</option>
										<option value="1">Ban for 14 days</option>
										<option value="2">Ban permanently</option>
									</select>
									<br>
									<p>Please only remove a Post, if it objectively violates the Rules, not because you as a person do not like it.</p>
									<p class="text-danger" id="mod-remove-post-error"></p>
									<input type="hidden" id="mod-remove-post-id">
									<input type="hidden" id="mod-remove-post-report">
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" id="mod-remove-post-cancelbtn" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
								<button type="button" id="mod-remove-post-submitbtn" onclick="modRemovePost();" class="btn btn-danger">Submit</button>
							</div>
						</div>
					</div>
				</div>';
	}

	function retrieveModals() {
		return '<div class="modal fade" id="delete-post-modal">
					<div class="modal-dialog">
						<div class="modal-content bg-dark text-light">
							<div class="modal-header">
								<h4 class="modal-title">Delete Post</h4>
							</div>
							<div class="modal-body">
								<p>Deleting this Post will remove it from its Board, hide it in Threads and make its link unusable. This cannot be undone.</p>
								<form>
									<input type="hidden" id="delete-post-id">
									<p class="text-danger" id="delete-post-error"></p>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" id="delete-post-cancelbtn" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
								<button type="button" id="delete-post-submitbtn" class="btn btn-danger" onclick="deletePost();">Delete</button>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="report-post-modal">
					<div class="modal-dialog">
						<div class="modal-content bg-dark text-light">
							<div class="modal-header">
								<h4 class="modal-title">Report Post</h4>
							</div>
							<div class="modal-body">
								<form>
									<h5>Why are you reporting this Post?</h5>
									<select style="width: 100%" class="form-control" id="report-post-reason">
										<option value="0">It contains hate speech.</option>
										<option value="1">It does not belong to this Board.</option>
										<option value="2">It contains content not suitable for minors.</option>
										<option value="3">It contains private information the Poster has no right to share.</option>
										<option value="4">It contains illegal content.</option>
										<option value="5">It contains spam.</option>
										<option value="6">It is a non-English Post.</option>
										<option value="7">I think the Poster is bypassing a ban.</option>
										<option value="8">It violates a Board rule. (please specify which rule in the next field)</option>
										<option value="9">Other</option>
									</select>
									<br>
									<h5>Please give us some details.</h5>
									<textarea class="form-control" id="report-post-details" rows="4" placeholder="Describe as detailed as possible why you are reporting this Post"></textarea>
									<br>
									<p>Knowingly reporting something which is not violating Global or Board Rules can result in a ban.</p>
									<p class="text-danger" id="report-post-error"></p>
									<input type="hidden" id="report-post-id">
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" id="report-post-cancelbtn" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
								<button type="button" id="report-post-submitbtn" onclick="reportPost();" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</div>
				</div>';
	}

	function retrieveCookieModal() {
		return '<div class="modal fade" id="cookie-modal">
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
				</div>';
	}

	function retrieveNewPostOffcanvas() {
		return '<div class="offcanvas offcanvas-start" tabindex="-1" id="newpost-offcanvas" aria-labelledby="newpost-offcanvas-label">
					<div class="offcanvas-header">
						<h5 class="offcanvas-title" id="offcanvasExampleLabel">Post</h5>
						<a class="btn-close btn-close-white" id="newpost-closebtn" data-bs-dismiss="offcanvas" aria-label="Close"></a>
					</div>
					<div class="offcanvas-body">
						<form>
							<div class="mb-3">
								<table>
									<tr id="newpost-replying">
										<td><i class="fas fa-reply"><span style="margin-left: 8px"></span></td>
										<td><b>Replying to:</b> <span id="newpost-replying-inreplyto"></span></td>
									</tr>
									<tr>
										<td><i class="fas fa-paper-plane"></i><span style="margin-left: 8px"></span></td>
										<td><b>Posting to:</b> <span id="newpost-board"></span></td>
									</tr>
								</table>
							</div>
							<div class="mb-3">
								<input class="form-control" type="file" id="newpost-file" onchange="checkIfUploadAllowed();">
							</div>
							<div class="mb-3">
								<input class="form-control" type="text" id="newpost-name" placeholder="Anonymous" maxlength="25">
							</div>
							<div class="mb-3">
								<textarea class="form-control" id="newpost-content" oninput="checkIfUploadAllowed();" rows="4" placeholder="Message" maxlength="2000"></textarea>
							</div>
							<div class="mb-3">
								<p class="text-danger" id="newpost-error"></p>
							</div>
							<input type="hidden" id="newpost-inreplyto">
							<input type="hidden" id="newpost-boardid">
							<button type="button" class="btn btn-primary btn-disabled" id="newpost-submitbtn" onclick="submitPost();" disabled>Submit</button>
						</form>
					</div>
				</div>';
	}
?>