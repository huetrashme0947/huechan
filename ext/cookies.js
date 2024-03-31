function setcookie(cname, cvalue, exdays=30) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/;samesite=lax;secure";
}

function getcookie(cname) {
	let name = cname + "=";
	let ca = document.cookie.split(';');
	for(let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

var didLoadNotifications = false;

function updatecookies() {
	if (sessionStorage.posts == null) { sessionStorage.posts = "{}"; }
	if (!didLoadNotifications) { didLoadNotifications = true; loadNotifications(); }
}

function checkIfUploadAllowed() {
	var allowed = true;
	var error = "";

	var file = document.getElementById("newpost-file").files[0];
	var content = document.getElementById("newpost-content");
	var isreply = !document.getElementById("newpost-replying").hidden;

	if (file == null && content.value == "") {
		allowed = false;
	} else if (!isreply && file == null && document.getElementById("newpost-boardid").value != "z") {
		allowed = false;
	}

	if (file != null) {
		var filetype = file.name.split(".").pop().toLowerCase();
		var filesize = file.size;

		if (filetype != "png" && filetype != "jpg" && filetype != "jpeg" && filetype != "gif") {
			allowed = false;
			error = "File uses an unsupported format. Supported formats are JPEG, PNG, GIF.";
		} else if (filesize > 4194304) {
			allowed = false;
			error = "File is too large. Maximum size is 4 MB.";
		}
	}

	document.getElementById("newpost-error").innerHTML = error;
	if (allowed) {
		document.getElementById("newpost-submitbtn").disabled = false;
		document.getElementById("newpost-submitbtn").setAttribute("class", "btn btn-primary");
	} else {
		document.getElementById("newpost-submitbtn").disabled = true;
		document.getElementById("newpost-submitbtn").setAttribute("class", "btn btn-primary btn-disabled");
	}
}

function generateCookies() {
	document.getElementById("cookie-agreebtn").setAttribute("disabled", "");
	document.getElementById("cookie-agreebtn").setAttribute("class", "btn btn-success btn-disabled");
	document.getElementById("cookie-agreebtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Agree";
	var http = new XMLHttpRequest();
	http.open("POST", "/ext/generate_aph");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.responseText == "OK") {
				localStorage.hc_data = JSON.stringify({"starred":[], "starred_names":[],"posts":[]});
				sessionStorage.posts = "{}";
				
				document.getElementById("cookie-agreebtn").innerHTML = "<i class='fas fa-check'></i>&nbsp; Agree";
				$('#cookie-modal').modal('toggle');
				initPage(false);
				setTimeout(() => {
					document.getElementById("cookie-agreebtn").removeAttribute("disabled");
					document.getElementById("cookie-agreebtn").innerHTML = "Agree";
					document.getElementById("cookie-agreebtn").setAttribute("class", "btn btn-success");
				}, 500);
			} else {
				document.getElementById("cookie-error").innerHTML = this.responseText;
				document.getElementById("cookie-agreebtn").removeAttribute("disabled");
				document.getElementById("cookie-agreebtn").innerHTML = "Agree";
				document.getElementById("cookie-agreebtn").setAttribute("class", "btn btn-success");
			}
		}
	}
	http.send();
}

function enableTooltips() {
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-hover="tooltip"]'));
	var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => { return new bootstrap.Tooltip(tooltipTriggerEl); });
}

function positionBottomNavbar() {
	var rootHeight = document.getElementById("root").offsetHeight;
	var windowHeight = window.innerHeight - document.getElementById("navbar-bottom").offsetHeight;
	if (rootHeight > windowHeight) { document.getElementById("navbar-bottom").setAttribute("class", "navbar navbar-dark bg-dark"); }
	else { document.getElementById("navbar-bottom").setAttribute("class", "navbar fixed-bottom navbar-dark bg-dark"); }
}

function reportPost() {
	document.getElementById("report-post-error").innerHTML = "";
	document.getElementById("report-post-submitbtn").setAttribute("disabled", "");
	document.getElementById("report-post-cancelbtn").setAttribute("disabled", "");
	document.getElementById("report-post-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Submitting...";

	// Send report
	var http = new XMLHttpRequest();
	var formData = new FormData();
	formData.append("id", document.getElementById("report-post-id").value);
	formData.append("reason", document.getElementById("report-post-reason").value);
	formData.append("details", document.getElementById("report-post-details").value);
	http.open("POST", "/ext/create_report");
	http.onreadystatechange = function() {
		if (this.responseText == "OK") {
			document.getElementById("report-post-submitbtn").innerHTML = "<i class='fas fa-check'></i>&nbsp; Submitted";
			document.getElementById("report-post-submitbtn").setAttribute("class", "btn btn-success btn-disabled");
			setTimeout(() => {
				$('#report-post-modal').modal('toggle');
				setTimeout(() => {
					document.getElementById("report-post-submitbtn").removeAttribute("disabled");
					document.getElementById("report-post-cancelbtn").removeAttribute("disabled");
					document.getElementById("report-post-submitbtn").innerHTML = "Submit";
					document.getElementById("report-post-submitbtn").setAttribute("class", "btn btn-primary");
				}, 500);
			}, 1000);
		} else {
			document.getElementById("report-post-error").innerHTML = this.responseText;
			document.getElementById("report-post-submitbtn").removeAttribute("disabled");
			document.getElementById("report-post-cancelbtn").removeAttribute("disabled");
			document.getElementById("report-post-submitbtn").innerHTML = "Submit";
		}
	}
	http.send(formData);
}

function deletePost() {
	document.getElementById("delete-post-submitbtn").setAttribute("disabled", "");
	document.getElementById("delete-post-cancelbtn").setAttribute("disabled", "");
	document.getElementById("delete-post-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Deleting...";

	var http = new XMLHttpRequest();
	var formData = new FormData();
	formData.append("id", document.getElementById("delete-post-id").value);
	http.open("POST", "/ext/delete_post");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.responseText == "OK") {
				hcdata = JSON.parse(localStorage.hc_data);
				hcdata.posts.splice(hcdata.posts.indexOf(document.getElementById("delete-post-id").value), 1);
				localStorage.hc_data = JSON.stringify(hcdata);
				document.getElementById("delete-post-submitbtn").innerHTML = "<i class='fas fa-check'></i>&nbsp; Deleted";
				setTimeout(() => {
					$('#delete-post-modal').modal('toggle');
					setTimeout(() => {
						document.getElementById("delete-post-submitbtn").removeAttribute("disabled");
						document.getElementById("delete-post-cancelbtn").removeAttribute("disabled");
						document.getElementById("delete-post-submitbtn").innerHTML = "Delete";
						document.getElementById("post-" + document.getElementById("delete-post-id").value).innerHTML = '<i class="text-secondary">This Post was deleted by the author.</i>';
						positionBottomNavbar();
					}, 500);
				}, 1000);
			} else {
				document.getElementById("delete-post-error").innerHTML = this.responseText;
				document.getElementById("delete-post-submitbtn").removeAttribute("disabled");
				document.getElementById("delete-post-cancelbtn").removeAttribute("disabled");
				document.getElementById("delete-post-submitbtn").innerHTML = "Delete";
			}
		}
	}
	http.send(formData);
}

function openNewPostOffcanvas(inReplyTo = "", board, boardname) {
	if (inReplyTo == "") {
		document.getElementById("newpost-replying").hidden = true;
		document.getElementById("newpost-file").required = true;
		document.getElementById("newpost-content").required = false;
	} else {
		document.getElementById("newpost-replying").hidden = false;
		document.getElementById("newpost-replying-inreplyto").innerHTML = '<a href="/'+board+'/'+inReplyTo+'">'+inReplyTo+'</a>';
		document.getElementById("newpost-file").required = false;
		document.getElementById("newpost-content").required = false;
	}

	document.getElementById("newpost-inreplyto").value = inReplyTo;
	document.getElementById("newpost-boardid").value = board;
	document.getElementById("newpost-board").innerHTML = boardname;
	checkIfUploadAllowed();
	new bootstrap.Offcanvas(document.getElementById("newpost-offcanvas")).show();
}

function openReportPostModal(id) {
	document.getElementById("report-post-id").value = id;
	new bootstrap.Modal(document.getElementById("report-post-modal"), {
		backdrop: true,
		keyboard: true
	}).show();
}

function triggerToast(body) {
	document.getElementById("toast-body").innerHTML = body;
	new bootstrap.Toast(document.getElementById("toast")).show();
}

function submitPost() {
	document.getElementById("newpost-submitbtn").setAttribute("disabled", "");
	document.getElementById("newpost-submitbtn").setAttribute("class", "btn btn-primary btn-disabled");
	document.getElementById("newpost-closebtn").setAttribute("hidden", "");
	document.getElementById("newpost-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Submitting...";

	// Submit data
	var http = new XMLHttpRequest();
	var formData = new FormData();
	formData.append("image", document.getElementById("newpost-file").files[0]);
	formData.append("name", document.getElementById("newpost-name").value);
	formData.append("content", document.getElementById("newpost-content").value);
	formData.append("board", document.getElementById("newpost-boardid").value);
	formData.append("inreplyto", document.getElementById("newpost-inreplyto").value);
	http.open("POST", "/ext/create_post");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.responseText.slice(0, 2) == "OK") {
				hcdata = JSON.parse(localStorage.hc_data);
				hcdata.posts.push(this.responseText.slice(3));
				localStorage.hc_data = JSON.stringify(hcdata);
				document.getElementById("newpost-submitbtn").innerHTML = "<i class='fas fa-check'></i>&nbsp; Submitted";
				document.getElementById("newpost-submitbtn").setAttribute("class", "btn btn-success btn-disabled");
				triggerToast('Post was sent. <a href="/'+document.getElementById("newpost-boardid").value+'/'+this.responseText.slice(3)+'" class="text-white"><b>View</b></a>');
				setTimeout(() => {
					bootstrap.Offcanvas.getInstance(document.getElementById("newpost-offcanvas")).hide();
					setTimeout(() => {
						document.getElementById("newpost-submitbtn").removeAttribute("disabled");
						document.getElementById("newpost-closebtn").removeAttribute("hidden");
						document.getElementById("newpost-submitbtn").innerHTML = "Submit";
						document.getElementById("newpost-submitbtn").setAttribute("class", "btn btn-primary");
					}, 500);
				}, 1000);
			} else {
				document.getElementById("newpost-error").innerHTML = this.responseText;
				document.getElementById("newpost-submitbtn").removeAttribute("disabled");
				document.getElementById("newpost-submitbtn").innerHTML = "Submit";
				document.getElementById("newpost-submitbtn").setAttribute("class", "btn btn-primary");
				document.getElementById("newpost-closebtn").removeAttribute("hidden");
			}
		}
	}
	http.send(formData);
}

function displayPollResults(id, pollResults, vote = null) {
	Array.from(document.getElementsByClassName("post-"+id+"-poll-button")).forEach((currentValue, index, arr) => {
		currentValue.setAttribute("disabled", "");
	});
	Array.from(document.getElementsByClassName("post-"+id+"-poll-button")).forEach((currentValue, index, arr) => {
		currentValue.innerHTML = currentValue.getAttribute("_hc_btn_orig")+" ("+pollResults[index.toString()]+"%)";
		currentValue.style = "width: "+(pollResults[index.toString()]/2)+"%; white-space: nowrap; text-align: left;";
	});
	if (vote !== null) { document.getElementById("post-"+id+"-poll-"+vote).innerHTML += "&nbsp;<i class='far fa-check-circle'></i>"; }
}

function voteInPoll(id, vote) {
	document.getElementById("post-"+id+"-poll-"+vote).innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; "+document.getElementById("post-"+id+"-poll-"+vote).innerHTML;
	Array.from(document.getElementsByClassName("post-"+id+"-poll-button")).forEach((currentValue, index, arr) => {
		currentValue.setAttribute("disabled", "");
	});

	// Submit data
	var http = new XMLHttpRequest();
	var formData = new FormData();
	formData.append("post", id);
	formData.append("vote", vote);
	http.open("POST", "/ext/vote_in_poll");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.responseText.slice(0, 2) == "OK") {
				const pollResults = JSON.parse(this.responseText.slice(3));
				displayPollResults(id, pollResults, vote);
			} else if (this.responseText.slice(0, 36) == "You have already voted in this poll.") {
				const pollResults = JSON.parse(this.responseText.slice(37));
				displayPollResults(id, pollResults);
				document.getElementById("post-"+id+"-poll-error").innerHTML = "You have already voted in this poll.";
			} else {
				document.getElementById("post-"+id+"-poll-error").innerHTML = this.responseText;
				document.getElementById("post-"+id+"-poll-"+vote).innerHTML = document.getElementById("post-"+id+"-poll-"+vote).getAttribute("_hc_btn_orig");
			}
		}
	}
	http.send(formData);
}

function loadNotifications() {
	const notificationsButton = document.getElementById("notifications-button");

	// Setup sessionStorage for notifications if not already present
	if (sessionStorage.notifications == null) { sessionStorage.notifications = "[]"; }

	// Load notifications
	var http = new XMLHttpRequest();
	http.open("GET", "/ext/fetch_notifications");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.responseText.slice(0, 2) == "OK") {
				notifications = JSON.parse(this.responseText.slice(3)).concat(JSON.parse(sessionStorage.notifications));
				var outputStr = '<div class="text-light">';
				if (notifications.length == 0) { bootstrap.Popover.getOrCreateInstance(notificationsButton); return; }
				notificationsButton.setAttribute("data-bs-content", "");
				notifications.forEach((currentValue, index, arr) => {
					outputStr += (index == 0 ? "" : '<hr>') + getNotificationUI(currentValue);
					console.log(currentValue);
				});
				outputStr += '</div>';
				notificationsButton.setAttribute("data-bs-content", outputStr);
				sessionStorage.notifications = JSON.stringify(notifications);
				if (this.responseText != "OK []") {
					notificationsButton.innerHTML = '<i class="fas fa-bell fa-lg"></i><span class="position-absolute translate-middle bg-danger rounded-circle" style="padding: .375rem !important;"></span>';
				}
			} else {
				notificationsButton.setAttribute("data-bs-content", "<div class=\"text-light\">An error occured while retrieving the notifications.</div>");
			}
			bootstrap.Popover.getOrCreateInstance(notificationsButton);
		}
	};
	http.send();
}

function getNotificationUI(notification) {
	var postLink = "/"+notification["attachment"]["board"]+"/"+notification["attachment"]["post"];
	var returnStr = '<div class="row"><div class="col-3"><i class="fas ';
	if (notification["type"] == "reply") { returnStr += 'fa-reply'; }
	else if (notification["type"] == "removed") { returnStr += 'fa-exclamation-triangle'; }
	else if (notification["type"] == "poll") { returnStr += 'fa-poll'; }
	else if (notification["type"] == "broadcast") { returnStr += 'fa-bullhorn'; }
	//else if (notification["type"] == "report") { returnStr += 'fa-folder-open'; }
	returnStr += ' fa-3x"></i></div><div class="col">';
	if (notification["type"] == "reply") { returnStr += '<b>New reply</b><br><a href="'+postLink+'">'+notification["attachment"]["post"]+'</a>'; }
	else if (notification["type"] == "removed") { returnStr += '<b>Your Post was removed</b><br><a href="'+postLink+'">View details</a>'; }
	else if (notification["type"] == "poll") { returnStr += '<b>Your poll ended</b><br><a href="'+postLink+'">View results</a>'; }
	else if (notification["type"] == "broadcast") { returnStr += '<b>'+notification["attachment"]["message"]+'</b><br><a href="'+postLink+'">Show post</a>'; }
	//else if (notification["type"] == "report") { returnStr += '<b>We reviewed your Report</b><br><a href="/beta/d44dddb715adb841">View details</a>'; }
	returnStr += '</div></div>';
	return returnStr;
}

function openDeletePostModal(id) {
	document.getElementById("delete-post-id").value = id;
	new bootstrap.Modal(document.getElementById("delete-post-modal"), {
		backdrop: true,
		keyboard: true
	}).show();
}

// Add function to get all starred Boards, retrieve all names and output them to "Starred" dropdown in menubar
function fillStarredMenu() {
	const starred = JSON.parse(localStorage.hc_data).starred;
	const starred_names = JSON.parse(localStorage.hc_data).starred_names;
	if (starred.length != 0) { document.getElementById("starred-dropdown-content").innerHTML = ""; }
	else { document.getElementById("starred-dropdown-content").innerHTML = '<li><a class="dropdown-item disabled">No Boards starred.</a></li>'; }
	starred.forEach((currentValue, index, arr) => {
		document.getElementById("starred-dropdown-content").innerHTML += '<li><a class="dropdown-item" href="/'+currentValue+'/">'+starred_names[index]+'</a></li>'
	});
}

function addPopover(anchor) {
	anchor.setAttribute("data-bs-toggle", "popover");
	anchor.setAttribute("data-bs-trigger", "hover focus");
	anchor.setAttribute("data-bs-html", "true");

	// Check if Post is already present in Session Storage
	if (anchor.innerHTML in JSON.parse(sessionStorage.posts)) {
		anchor.setAttribute("data-bs-content", getPostPopover(JSON.parse(sessionStorage.posts)[anchor.innerHTML]));
		new bootstrap.Popover(anchor);
		return;
	}

	// Get Post and load into Session Storage
	var http = new XMLHttpRequest();
	http.open("GET", "/ext/get_post?id="+anchor.innerHTML);
	http.addEventListener("readystatechange", function() {
		if (this.readyState == 4) {
			if (this.responseText.slice(0, 2) == "OK") {
				const post = JSON.parse(this.responseText.slice(3));
				var posts = JSON.parse(sessionStorage.posts);
				if (post == null || post == 0) { return undefined; }
				posts[post["id"]] = post;
				sessionStorage.posts = JSON.stringify(posts);
				anchor.setAttribute("data-bs-content", getPostPopover(post));
				new bootstrap.Popover(anchor);
			}
		}
	});
	http.send();
}

function formatContent(text) {
	var returnStr = text + "    ";
	const matches = returnStr.matchAll(/(?:^|<br>)(&gt;(?:[^<])*)/gm);
	var i = 0;
	const OFFSET = 34;
	console.log(returnStr);
	for (const match of matches) {
		returnStr = returnStr.slice(0, (i*OFFSET)+match.index+4) + "<span class=\"text-success\">" + returnStr.slice((i*OFFSET)+match.index+4, (i*OFFSET)+match.index+match.length) + "</span>" + returnStr.slice((i*OFFSET)+match.index+match.length, returnStr.length);
		i++;
		console.log(returnStr);
	}
	return returnStr.slice(0, returnStr.length-4);
}

function shortenContent(text, limit = 500) {
	var returnStr = text.replaceAll("\n", "<br>");
	if (text.length < limit && (returnStr.match(/<br>/g) || []).length < 4) { return returnStr; }
	var wordArray = returnStr.split(" ");
	returnStr = "";
	for (var i = 0; i < wordArray.length; i++) {
		if ((returnStr + wordArray[i] + " ").length > limit || (returnStr.match(/<br>/g) || []).length >= 4) {
			break;
		}
		returnStr += wordArray[i] + " ";
	}
	return returnStr.slice(0, returnStr.length-1);
}

function getTimeString(time, full = false) {
	const d = new Date(time);
	if (!full) {
		var timeDiff = ~~((Date.now()-d)/1000);
		if (timeDiff == 0) { return "now"; }
		if (timeDiff < 60) { return timeDiff+"s ago"; }
		if (timeDiff < 3600) { return ~~(timeDiff/60)+"m ago"; }
		if (timeDiff < 86400) { return ~~(timeDiff/3600)+"h ago"; }
		if (timeDiff < 604800) { return ~~(timeDiff/86400)+"d ago"; }

		// If older than 1 week, just output plain date and time in "Y/m/d" format
		return d.getFullYear() +"/"+ ("0"+(d.getMonth()+1)).slice(-2) +"/"+ ("0"+d.getDate()).slice(-2);
	}

	// Full format
	var retStr = d.getFullYear() +"/"+ ("0"+(d.getMonth()+1)).slice(-2) +"/"+ ("0"+d.getDate()).slice(-2) +" ";
	if (d.getHours() == 0) {
		retStr += "12:"+("0"+d.getMinutes()).slice(-2)+" AM";
	} else if (d.getHours() < 12) {
		retStr += d.getHours()+":"+("0"+d.getMinutes()).slice(-2)+" AM";
	} else if (d.getHours() == 12) {
		retStr += "12:"+("0"+d.getMinutes()).slice(-2)+" PM";
	} else {
		retStr += (d.getHours()-12)+":"+("0"+d.getMinutes()).slice(-2)+" PM";
	}
	return retStr;
}

function getRemainingTimeStr(time) {
	const d = new Date(time);
	var timeDiff = ~~((d-Date.now())/1000);
	if (timeDiff < 0) { return false; }
	if (timeDiff < 60) { return timeDiff+"s remaining"; }
	if (timeDiff < 3600) { return ~~(timeDiff/60)+"m remaining"; }
	if (timeDiff < 86400) { return ~~(timeDiff/3600)+"h remaining"; }
	return ~~(timeDiff/86400)+"d remaining";
}

function getPostUI(post, mixed = true) {
	var id = post["id"];
	var board = post["board"];
	var inReplyTo = post["inreplyto"];
	var content = shortenContent(post["content"]);
	var contentShortened = content != post["content"];
	var name = post["name"];
	var datetime = getTimeString(post["datetime"]);
	var isAuthor = post["flags"].includes("author");
	var isOperator = post["flags"].includes("operator");
	var isStaff = post["flags"].includes("staff");
	var noReplies = post["flags"].includes("noreplies");
	var isRemoved = post["flags"].includes("removed");
	var isPinned = post["flags"].includes("pinned");
	var boardname = post["boardname"];
	var imagePath = post["image"];
	var replies = post["replies"];
	var hasPoll = typeof post["poll"] != "undefined";

	// Create string for element
	var postUI = '<div id="post-'+id+'-spacer" style="margin-top: 16px;"></div><div id="post-'+id+'" class="post-op bg-dark"><div class="dropend">';
	if (isPinned) { postUI += '<i class="fas fa-thumbtack fa-sm" data-bs-hover="tooltip" data-bs-placement="top" title="Pinned"></i><span style="margin-left: 8px;"></span>'; }
	else if (isRemoved) { postUI += '<i class="fas fa-eye-slash fa-sm" data-bs-hover="tooltip" data-bs-placement="top" title="Only visible to staff"></i><span style="margin-left: 8px;"></span>'; }
	postUI += '<span class="dropdown-toggle" id="post-'+id+'-dropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">';
	if (isStaff) { postUI += '<b class="text-danger">'+(name != "" ? name : "Anonymous")+' (Staff)</b>'; }
	else { postUI += '<b class="text-success">'+(name != "" ? name : "Anonymous")+'</b>'; }
	if (mixed) { postUI += ' (/'+board+'/)'; }
	postUI += '&nbsp;'+datetime+'<span style="margin-left: 2px;"></span></span><span style="margin-left: 8px;"></span>';
	if (noReplies) { postUI += '<a data-bs-hover="tooltip" data-bs-placement="top" title="Replying disabled" style="cursor: not-allowed;"><i class="fas fa-reply"></i><span style="margin-left: 6px;"></span>'+replies+'</a>'; }
	else { postUI += '<a href="javascript:;" data-bs-hover="tooltip" data-bs-placement="top" title="Reply" onclick="openNewPostOffcanvas(\''+id+'\', \''+board+'\', \''+boardname+'\');"><i class="fas fa-reply"></i><span style="margin-left: 6px;"></span>'+replies+'</a>'; }
	postUI += '<ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="post-'+id+'-dropdown-menu">';
	if (!noReplies) { postUI += '<li><a class="dropdown-item" onclick="openNewPostOffcanvas(\''+id+'\', \''+board+'\', \''+boardname+'\');">Reply</a></li>'; }
	if (!isStaff) { postUI += '<li><a class="dropdown-item" onclick="openReportPostModal(\''+id+'\', \''+board+'\');">Report</a></li>'; }
	if (isAuthor) { postUI += '<li><a class="dropdown-item text-danger" onclick="openDeletePostModal(\''+id+'\');">Delete</a></li>'; }
	if (isOperator) { postUI += '<li><a class="dropdown-item text-danger" onclick="openModRemovePostModal(\''+id+'\', \''+board+'\')"><i class="fas fa-user-shield"></i>&nbsp; Remove Post</a></li>'; }
	if (!(!noReplies || !isStaff || isAuthor || isOperator)) { postUI += '<li><a class="dropdown-item disabled text-secondary">No options available.</a></li>'; }
	postUI += '</ul></div>';
	if (inReplyTo != null) {
		postUI += '<i class="fas fa-reply"></i><span style="margin-left: 8px"></span><b>Replying to:</b> <a class="post-href" href="/'+board+'/'+inReplyTo+'">'+inReplyTo+'</a>';
	}
	postUI += '<div style="margin-top: 8px;"></div><div class="post-content" onclick="window.location.href=\'/'+board+'/'+id+'\';"><table><tr>';
	if (imagePath != null) { postUI += '<td style="padding-right: 12px;"><img src="'+imagePath+'" width="192" class="post-image" style="object-fit: cover; width: 192px; height: 192px;" loading="lazy"></td>'; }
	postUI += '<td style="vertical-align: top; max-width: 40% !important;">'+content;
	if (contentShortened) { postUI += '<span style="margin-left: 8px;"></span><a href="/'+board+'/'+id+'">Show more</a>'; }
	else if (hasPoll) { postUI += '<br><a href="/'+board+'/'+id+'">Show poll</a>'; }
	postUI += '</td></tr></table></div></div>';

	return postUI;
}

function getPostPopover(post) {
	var inReplyTo = post["inreplyto"];
	var content = shortenContent(post["content"], 200);
	var contentShortened = content != post["content"];
	var name = post["name"];
	var datetime = getTimeString(post["datetime"]);
	var isStaff = post["flags"].includes("staff");
	var imagePath = post["image"];

	var postUI = (imagePath != null ? "<p" : "<span")+" class='text-light'>";
	if (isStaff) { postUI += "<b class='text-danger'>"+(name != "" ? name : "Anonymous")+" (Staff)</b> "+datetime; }
	else { postUI += "<b class='text-success'>"+(name != "" ? name : "Anonymous")+"</b> "+datetime; }
	if (inReplyTo != null) { postUI += "<br><b>Replying to:</b> "+inReplyTo; }
	if (content != "") { postUI += "<br>"+content+(contentShortened ? "..." : ""); }
	postUI += "</"+(imagePath != null ? "p>" : "span>");
	if (imagePath != null) { postUI += "<img src='"+imagePath+"' width='192' class='post-image'>"; }

	return postUI;
}

function setupPostPopovers() {
	var postLinks = document.getElementsByClassName("post-href");
	var postLinkNodes = document.querySelectorAll(".post-href");
	for (var i = 0; i < postLinks.length; i++) { addPopover(postLinks[i]); }
}
