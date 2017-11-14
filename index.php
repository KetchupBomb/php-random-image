<?php
	/**
		getImages()

		Given a directory path of $path, search dir and return all entries within dir.
		An exclude array contains relative (., ..) references which ought to be removed.
		Each entry is properly formatted to be a "full" path to the images.
	*/
	function getImages($path) {
		$exclude = array(".", "..");
		$tmp = array_diff(scandir($path), $exclude);
		foreach($tmp as $key => $val)
			$tmp[$key] = $path . $val;

		return $tmp;
	}

	/**
		getImage()

		Given an array, return a random element.
		Array keys are abstracted away since indices 1 and 2 are removed due to (., ..) references.
	*/
	function getImage($arr) {
		$index = array_rand($arr, 1);
		$image = $arr[$index];
		return $image;
	}

	/**
		sendImage()

		Given an image, calculate proper headers (type, length), and output $image in binary fashion.
	*/
	function sendImage($image) {
		$headers = array();
		$headers["Content-Type"] = getImageMimeType($image);
		$headers["Content-Length"] = getImageLength($image);
		$headers["Image"] = $image;
		sendHeaders($headers);

		$fp = fopen($image, "rb");
		fpassthru($fp);
	}


	/**
		getImageMimeType()

		Given an image, use PHP's FileInfo module (finfo) to determine MIME type dynamically.
	*/
	function getImageMimeType($image) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $image);
	}

	/**
		getImageLength()

		Given an image, return the Content-Length (file size).
	*/
	function getImageLength($image) {
		return filesize($image);
	}

	/**
		sendHeaders()

		For each header, call PHP header() function.
	*/
	function sendHeaders($headers) {
		foreach ($headers as $header => $val) {
			header($header . ": " . $val);
		}
	}

	/**
		redirectImage()

		Optional function which causes an HTTP 300 to be served to the client to redirect to the absolute path to an image.
		Doesn't seem to work in Mumble, so not going to use it but it's an option instead of sendImage().
	*/
	function redirectImage($image) {
		header("Location: " . $image);
	}

	/**
		log()

		Logging function to record what images was served.
	*/
	function logImage($image) {
		$logFile = "mumble_image.log";
		$fd = fopen($logFile, "a");
		fwrite($fd, print($image) . "\n");
		fclose($fd);
	}

	/**
		vnstati()

		Creates an images in an existing directory (vnstati_images) called "image.png".
		Returns a string path to the file created.
	*/
	function vnstati() {
		shell_exec("vnstati -vs -o vnstati_images/image.png");
		return "vnstati_images/image.png";
	}

##########

	# If there is a GET parameter of "?vnstati", return the vnstat statistics
	if(isset($_GET["vnstati"])) {
		$image = vnstati();
		sendImage($image);
		exit;
	}

	# Otherwise do the normal behavior of finding a random image
	$path = "/data/";
	$images = getImages($path);
	$image = getImage($images);
	sendImage($image);
	#logImage($image);
	exit;
