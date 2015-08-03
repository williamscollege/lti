<?php
	// ***************************
	// Enable localhost testing only!
	// ***************************
	$strServerName = $_SERVER['SERVER_NAME'];
	if (!($strServerName == "localhost") OR ($strServerName == "127.0.0.1")) {
		echo 'ZERO ACCESS';
		exit;
	}

	phpinfo();
