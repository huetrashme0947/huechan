<?php
	function upload_image($file, $id) {
		$path = "ext/images/".$id.".".strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
		$imageFileType = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		// Check, if image is invalid
		if (getimagesize($file["tmp_name"]) == false) { return [415, "File is not an image."]; }

		// Check, if file is too large (> 4 MB)
		if ($file["size"] > 4194304) { return [400, "File is too large. Maximum size is 4 MB."]; }

		// Check for unsupported format
		if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") { return [400, "File uses an unsupported format. Supported formats are JPEG, PNG and GIF."]; }

		// Upload file, when every check was passed
		if (!move_uploaded_file($file["tmp_name"], $path)) { return [500, "File could not be uploaded. Please try again later."]; }

		return true;
	}

	function archive_image($id, $delete = false) {
		// Check file type
		if (file_exists("images/$id.png")) {
			$extension = "png";
		} else if (file_exists("images/$id.jpg")) {
			$extension = "jpg";
		} else if (file_exists("images/$id.jpeg")) {
			$extension = "jpeg";
		} else if (file_exists("images/$id.gif")) {
			$extension = "gif";
		} else {
			return null;
		}

		if ($delete == true) {
			// Delete file completely. This will be done if the Post will be deleted
			if (!unlink("ext/images/$id.$extension")) { return false; }
		} else {
			// Move file to /images_removed. This will be done if the Post will be removed by staff
			if (!rename("images/$id.$extension", "images_archived/$id.$extension")) { return false; }
		}

		return true;
	}

	function get_image_path($id, $root = true) {
		if ($root) { $basepath = "ext/images"; } else { $basepath = "images"; }
		if (file_exists($basepath."/$id.png")) { return "/ext/images/$id.png"; }
		else if (file_exists($basepath."/$id.jpg")) { return "/ext/images/$id.jpg"; }
		else if (file_exists($basepath."/$id.jpeg")) { return "/ext/images/$id.jpeg"; }
		else if (file_exists($basepath."/$id.gif")) { return "/ext/images/$id.gif"; }
		else if (file_exists($basepath."_archived/$id.png")) { return "/ext/images_archived/$id.png"; }
		else if (file_exists($basepath."_archived/$id.jpg")) { return "/ext/images_archived/$id.jpg"; }
		else if (file_exists($basepath."_archived/$id.jpeg")) { return "/ext/images_archived/$id.jpeg"; }
		else if (file_exists($basepath."_archived/$id.gif")) { return "/ext/images_archived/$id.gif"; }
		else { return null; }
	}
?>
