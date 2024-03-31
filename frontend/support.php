<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Support - Huechan</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/afd60586d3.js" crossorigin="anonymous"></script>
	<script type="module" src="https://cdn.jsdelivr.net/npm/friendly-challenge@0.9.1/widget.module.min.js" async defer></script>
	<script nomodule src="https://cdn.jsdelivr.net/npm/friendly-challenge@0.9.1/widget.min.js" async defer></script>

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

			document.querySelectorAll("#emailverify-code > *[id]").forEach((element) => {
				element.addEventListener("input", () => {
					if (!/[0-9]/.test(element.value)) {
						element.value = "";
						return;
					}

					if (element.id.slice(element.id.length-1) != "5") {
						document.getElementById("emailverify-code-"+(parseInt(element.id.slice(element.id.length-1))+1)).focus();
					} else {
						document.activeElement.blur();
					}
				});
			});
		};

		function checkIfSubmitAllowed() {
			const subject = document.getElementById("support-subject").value;
			const details = document.getElementById("support-details").value;
			const email = document.getElementById("support-email").value;
			const solution = document.getElementById("support-solution").value;
			const submitBtn = document.getElementById("support-submitbtn");

			if (subject != "" && details != "" && email != "" && solution != "" && details.length <= 2000 && /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
				submitBtn.disabled = false;
				submitBtn.setAttribute("class", "btn btn-primary");
			} else {
				submitBtn.disabled = true;
				submitBtn.setAttribute("class", "btn btn-primary btn-disabled");
			}
		}

		function checkIfVerifySubmitAllowed() {
			const codeInputs = [document.getElementById("emailverify-code-0").value, document.getElementById("emailverify-code-1").value, document.getElementById("emailverify-code-2").value, document.getElementById("emailverify-code-3").value, document.getElementById("emailverify-code-4").value, document.getElementById("emailverify-code-5").value];
			const submitBtn = document.getElementById("emailverify-submitbtn");

			var allInputsFilled = true;
			codeInputs.forEach(function (currentValue) {
				if (currentValue == "") { allInputsFilled = false; }
			});

			if (allInputsFilled) {
				submitBtn.disabled = false;
				submitBtn.setAttribute("class", "btn btn-primary");
			} else {
				submitBtn.disabled = true;
				submitBtn.setAttribute("class", "btn btn-primary btn-disabled");
			}
		}

		function submitTicket() {
			document.getElementById("support-submitbtn").setAttribute("disabled", "");
			document.getElementById("support-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Submitting...";

			// Submit data
			var http = new XMLHttpRequest();
			var formData = new FormData();
			formData.append("subject", document.getElementById("support-subject").value);
			formData.append("details", document.getElementById("support-details").value);
			formData.append("email", document.getElementById("support-email").value);
			formData.append("captcha", document.getElementById("support-solution").value);
			http.open("POST", "/ext/create_support_ticket");
			http.onreadystatechange = function() {
				if (this.readyState == 4) {
					if (this.responseText == "OK") {
						document.getElementById("emailverify-email").innerHTML = document.getElementById("support-email").value;
						document.getElementById("support-container").setAttribute("hidden", "");
						document.getElementById("emailverify-container").removeAttribute("hidden");
					} else {
						document.getElementById("support-error-inner").innerHTML = this.responseText;
						document.getElementById("support-submitbtn").removeAttribute("disabled");
						document.getElementById("support-submitbtn").innerHTML = "Submit";
					}
				}
			};
			http.send(formData);
		}

		function submitVerifyCode() {
			document.getElementById("emailverify-submitbtn").setAttribute("disabled", "");
			document.getElementById("emailverify-submitbtn").innerHTML = "<i class='fas fa-circle-notch fa-spin'></i>&nbsp; Submitting...";

			// Get verification code
			const codeInputs = [document.getElementById("emailverify-code-0").value, document.getElementById("emailverify-code-1").value, document.getElementById("emailverify-code-2").value, document.getElementById("emailverify-code-3").value, document.getElementById("emailverify-code-4").value, document.getElementById("emailverify-code-5").value];
			var verificationCode = "";
			codeInputs.forEach(function (currentValue) {verificationCode += currentValue});

			// Submit data
			var http = new XMLHttpRequest();
			var formData = new FormData();
			formData.append("code", verificationCode);
			http.open("POST", "/ext/verify_support_ticket");
			http.onreadystatechange = function() {
				if (this.readyState == 4) {
					if (this.responseText == "OK") {
						document.getElementById("emailverify-container").setAttribute("hidden", "");
						document.getElementById("alldone-container").removeAttribute("hidden");
					} else {
						document.getElementById("emailverify-error").innerHTML = this.responseText;
						document.getElementById("emailverify-submitbtn").removeAttribute("disabled");
						document.getElementById("emailverify-submitbtn").innerHTML = "Submit";
					}
				}
			};
			http.send(formData);
		}

		function saveCaptchaSolution(solution) {
			document.getElementById("support-solution").value = solution;
			checkIfSubmitAllowed();
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

		#emailverify-code > *[id] {
			font-size: 1.5rem;
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

	<div class="container-sm" id="support-container">
		<p>
			<h1>Support</h1>
			Please use this form to contact us for ordinary requests.
		</p>
		<p>
			<form style="max-width: 720px">
				<div class="mb-3">
					<select style="width: 100%" class="form-control" id="support-subject" onchange="document.getElementById('support-subject-default').disabled = true; checkIfSubmitAllowed();">
						<option value="" id="support-subject-default">Please choose a subject.</option>
						<option value="0">I want to give Feedback or report a Bug.</option>
						<option value="1">I have a suggestion for a Board or feature.</option>
						<option value="2">I want to appeal my ban.</option>
						<option value="3">I want to Report a Post.</option>
						<option value="4">I'm having concerns about a Staff decision.</option>
						<option value="5">I have a general question.</option>
						<option value="6">Other</option>
					</select>
				</div>
				<p class="text-danger" id="support-error-outer"></p>
				<div id="support-form">
					<div class="mb-3">
						<textarea class="form-control" id="support-details" oninput="checkIfSubmitAllowed();" rows="5" placeholder="Please provide some details. (max. 2000 characters)" maxlength="2000"></textarea>
					</div>
					<div class="mb-3">
						<input type="email" class="form-control" id="support-email" oninput="checkIfSubmitAllowed();" placeholder="Email address (the reply will be sent to this address)" maxlength="5000">
					</div>
					<div class="mb-3">
						<div class="frc-captcha" data-sitekey="FCMN76OA5E5LO9KQ" data-callback="saveCaptchaSolution"></div>
					</div>
					<div class="mb-3">
						<p class="text-danger" id="support-error-inner"></p>
					</div>
					<input type="hidden" id="support-solution">
					<button type="button" class="btn btn-primary btn-disabled" id="support-submitbtn" onclick="submitTicket();" disabled>Submit</button>
				</div>
			</form>
		</p>
	</div>

	<div class="container-sm" id="emailverify-container" hidden>
		<p>
			<h1>Confirm Email Address</h1>
			We've sent a verification code to <span id="emailverify-email"></span>. If you cannot find the code, please check your spam folder or wait a few minutes.
		</p>
		<p>
			<form style="max-width: 360px">
				<div id="emailverify-code" class="inputs d-flex flex-row justify-content-center mt-2">
					<input class="m-2 text-center form-control rounded" type="text" id="emailverify-code-0" maxlength="1" oninput="checkIfVerifySubmitAllowed();">
					<input class="m-2 text-center form-control rounded" type="text" id="emailverify-code-1" maxlength="1" oninput="checkIfVerifySubmitAllowed();">
					<input class="m-2 text-center form-control rounded" type="text" id="emailverify-code-2" maxlength="1" oninput="checkIfVerifySubmitAllowed();">
					<input class="m-2 text-center form-control rounded" type="text" id="emailverify-code-3" maxlength="1" oninput="checkIfVerifySubmitAllowed();">
					<input class="m-2 text-center form-control rounded" type="text" id="emailverify-code-4" maxlength="1" oninput="checkIfVerifySubmitAllowed();">
					<input class="m-2 text-center form-control rounded" type="text" id="emailverify-code-5" maxlength="1" oninput="checkIfVerifySubmitAllowed();">
				</div>
				<br>
				<p class="text-danger" id="emailverify-error"></p>
				<button type="button" class="btn btn-primary btn-disabled" id="emailverify-submitbtn" onclick="submitVerifyCode();" disabled>Submit</button>
			</form>
		</p>
	</div>

	<div class="container-sm" id="alldone-container" hidden>
		<p>
			<h1>All done!</h1>
			A support ticket has been created for your inquiry and we will start investigating your case and contact you when we have new information. Please check your inbox and spam folder regularly during the next days.<br><br>
			<p><a href="/">Back to Home</a></p>
		</p>
	</div>

	<br><br><br><br>

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