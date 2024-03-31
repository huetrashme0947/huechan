function checkIfModRemovalAllowed() {
	if ((document.getElementById("mod-remove-post-reason").value == 8 || document.getElementById("mod-remove-post-reason").value == 9) && document.getElementById("mod-remove-post-details").value == "") {
		document.getElementById("mod-remove-post-submitbtn").setAttribute("disabled", "");
		document.getElementById("mod-remove-post-details").setAttribute("placeholder", "Attach some details");
	} else if (document.getElementById("mod-remove-post-reason").value == "") {
		document.getElementById("mod-remove-post-submitbtn").setAttribute("disabled", "");
	} else {
		document.getElementById("mod-remove-post-submitbtn").removeAttribute("disabled");
		document.getElementById("mod-remove-post-details").setAttribute("placeholder", "Attach some details (optional)");
		document.getElementById("mod-remove-post-reason-default").setAttribute("disabled", "");
	}
}

function modRemovePost() {
	document.getElementById("mod-remove-post-error").innerHTML = "";
	document.getElementById("mod-remove-post-submitbtn").setAttribute("disabled", "");
	document.getElementById("mod-remove-post-cancelbtn").setAttribute("disabled", "");
	document.getElementById("mod-remove-post-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Submitting...";

	var http = new XMLHttpRequest();
	var formData = new FormData();
	formData.append("id", document.getElementById("mod-remove-post-id").value);
	formData.append("reason", document.getElementById("mod-remove-post-reason").value);
	formData.append("details", document.getElementById("mod-remove-post-details").value);
	formData.append("ban", document.getElementById("mod-remove-post-ban").value);
	http.open("POST", "/ext/mod_remove_post");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			console.log(this.responseText);
			if (this.responseText == "OK") {
				document.getElementById("mod-remove-post-submitbtn").innerHTML = "<i class='fas fa-check'></i>&nbsp; Submitted"
				document.getElementById("mod-remove-post-submitbtn").setAttribute("class", "btn btn-success btn-disabled")
				setTimeout(() => {
					$('#mod-remove-post-modal').modal('toggle');
					setTimeout(() => {
						document.getElementById("mod-remove-post-submitbtn").removeAttribute("disabled");
						document.getElementById("mod-remove-post-cancelbtn").removeAttribute("disabled");
						document.getElementById("mod-remove-post-submitbtn").innerHTML = "Submit";
						document.getElementById("mod-remove-post-submitbtn").setAttribute("class", "btn btn-primary");
						document.getElementById("post-" + document.getElementById("mod-remove-post-id").value).innerHTML = '<i class="text-secondary">This Post was removed by staff due to a violation of the Rules.</i>';
					}, 500);
				}, 1000);
			} else {
				document.getElementById("mod-remove-post-error").innerHTML = this.responseText;
				document.getElementById("mod-remove-post-submitbtn").removeAttribute("disabled");
				document.getElementById("mod-remove-post-cancelbtn").removeAttribute("disabled");
				document.getElementById("mod-remove-post-submitbtn").innerHTML = "Submit";
			}
		}
	}
	http.send(formData);
}

function openModRemovePostModal(id, board, reason = null, report = null) {
	document.getElementById("mod-remove-post-id").value = id;
	document.getElementById("mod-remove-post-report").value = "";
	if (board == "x") {
		document.querySelector("#mod-remove-post-reason > option[value='1']").setAttribute("disabled", "");
		document.querySelector("#mod-remove-post-reason > option[value='6']").removeAttribute("disabled");
	} else if (board == "y") {
		document.querySelector("#mod-remove-post-reason > option[value='1']").removeAttribute("disabled");
		document.querySelector("#mod-remove-post-reason > option[value='6']").setAttribute("disabled", "");
	}

	if (reason != null) { document.getElementById("mod-remove-post-reason").value = reason; }
	if (report != null) { document.getElementById("mod-remove-post-report").value = report; }

	checkIfModRemovalAllowed();

	new bootstrap.Modal(document.getElementById("mod-remove-post-modal"), {
		backdrop: "static",
		keyboard: false
	}).show();
}

function openModReviewReportModal(id, status) {
	document.getElementById("mod-review-report-title").innerHTML = status ? "Approve" : "Reject";
	document.getElementById("mod-review-report-pg1").innerHTML = status ? "approve" : "reject";
	document.getElementById("mod-review-report-pg2").innerHTML = status ? "remove the Reported Post from Huechan." : "leave the reported Post untouched.";
	document.getElementById("mod-review-report-ban-title").innerHTML = status ? "author of Post?" : "Reporting user?";
	document.getElementById("mod-review-report-id").innerHTML = id;
	new bootstrap.Modal(document.getElementById("mod-review-report-modal"), {keyboard: false}).show();
}

function reviewReport() {
	document.getElementById("mod-review-report-error").innerHTML = "";
	document.getElementById("mod-review-report-cancelbtn").setAttribute("disabled", "");
	document.getElementById("mod-review-report-submitbtn").setAttribute("disabled", "");
	document.getElementById("mod-review-report-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Submitting...";

	var http = new XMLHttpRequest();
	var formData = new FormData();
	formData.append("id", document.getElementById("mod-review-report-id").innerHTML);
	formData.append("status", document.getElementById("mod-review-report-title").innerHTML == "Approve");
	formData.append("keep_details", document.getElementById("mod-review-report-details").checked);
	formData.append("ban", document.getElementById("mod-review-report-ban").value);
	http.open("POST", "/ext/mod_review_report");
	http.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.responseText == "OK") {
				document.getElementById("mod-review-report-submitbtn").setAttribute("class", "btn btn-success");
				document.getElementById("mod-review-report-submitbtn").innerHTML = "<i class='fas fa-check'></i>&nbsp; Submitted";
				setTimeout(() => {
					$('#mod-review-report-modal').modal('toggle');
					setTimeout(() => {
						document.getElementById("mod-review-report-submitbtn").removeAttribute("disabled");
						document.getElementById("mod-review-report-cancelbtn").removeAttribute("disabled");
						document.getElementById("mod-review-report-submitbtn").innerHTML = "Submit";
						document.getElementById("mod-review-report-submitbtn").setAttribute("class", "btn btn-primary");
						document.getElementById("report-" + document.getElementById("mod-review-report-id").innerHTML).remove();
						document.getElementById("report-" + document.getElementById("mod-review-report-id").innerHTML + "-spacer").remove();
						if (document.getElementById("postlist").innerHTML == "") { document.getElementById("postlist-error").style = ""; }
						positionBottomNavbar();
					}, 500);
				}, 1000);
			} else {
				document.getElementById("mod-review-report-error").innerHTML = this.responseText;
				document.getElementById("mod-review-report-cancelbtn").removeAttribute("disabled");
				document.getElementById("mod-review-report-submitbtn").removeAttribute("disabled");
				document.getElementById("mod-review-report-submitbtn").innerHTML = "Submit";
			}
		}
	}
	http.send(formData);
}

function getReportUI(report) {
	var id = report["id"];
	var reason = parseInt(report["reason"]);
	var reason_text = "";
	var details = report["details"];
	var post = report["post"];
	var datetime = getTimeString(report["datetime"]);
	var status = report["status"];
	var board = report["board"];
	var boardname = report["boardname"];
	var past_bans = report["past_bans"];

	// Get reason string
	switch (reason) {
		case 0: reason_text = "It contains hate speech."; break;
		case 1: reason_text = "It does not belong to this Board."; break;
		case 2: reason_text = "It contains content not suitable for minors."; break;
		case 3: reason_text = "It contains private information the Poster has no right to share."; break;
		case 4: reason_text = "It violates a Board rule."; break;
		case 5: reason_text = "It contains illegal content."; break;
		case 6: reason_text = "It contains spam."; break;
		case 7: reason_text = "It is a non-English Post."; break;
		case 8: reason_text = "I think the Poster is bypassing a ban."; break;
		case 9: reason_text = "Other"; break;
		default: reason_text = "<i>No reason provided.</i>"; break;
	}

	var reportUI = '<div id="report-'+id+'-spacer" style="margin-top: 16px;"></div><div id="report-'+id+'" class="post-op bg-dark"><b>Report #'+id+'</b> '+datetime+'<br>Reason: '+reason_text+'<br><input type="hidden" id="report-'+id+'-reason" value="'+reason+'"><input type="hidden" id="report-'+id+'-board" value="'+board+'">Details: '+(details != "" ? details : "<i>No details provided.</i>")+'<br>Board: '+boardname+'<br>Post: <a class="post-href" id="report-'+id+'-post" href="/'+board+'/'+post+'">'+post+'</a><br>The Reporting user has '+(past_bans == 0 ? "not abused the Reporting system in the past." : "abused the Reporting system "+past_bans+" time"+(past_bans == 1 ? "" : "s")+" in the past.")+'<br>';
	reportUI += '<div style="margin-top: 8px"></div><div><button type="button" class="btn btn-danger" onclick="openModReviewReportModal('+id+', false);" data-bs-hover="tooltip" data-bs-placement="top" title="Reject"><i class="fas fa-times fa-lg"></i></button><span style="margin-left: 8px;"><button type="button" class="btn btn-success" onclick="openModReviewReportModal('+id+', true);" data-bs-hover="tooltip" data-bs-placement="top" title="Approve"><i class="fas fa-check"></i></button></div></div>';

	return reportUI;
}