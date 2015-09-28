<?php

	// various utility functions


	//##################################################
	// Helper functions
	//##################################################

	# validation routine: trim fxn strips (various types of) whitespace characters from the beginning and end of a string
	function util_quoteSmart($value) {
		// stripslashes — Un-quotes a quoted string
		// trim — Strip whitespace (or other characters) from the beginning and end of a string
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
			$value = trim($value);
		}
		return $value;
	}


	# Output an object wrapped with HTML PRE tags for pretty output
	function util_prePrintR($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
		return TRUE;
	}


	# clear session and session cookies
	function util_wipeSession() {
		unset($_SESSION['consumer_key']);
		unset($_SESSION['resource_id']);
		unset($_SESSION['user_consumer_key']);
		unset($_SESSION['user_id']);
		unset($_SESSION['isStudent']);
		unset($_SESSION['custom_canvas_course_id']);

		unset($_SESSION[APP_STR . '_id']);
		$_COOKIE[APP_STR . '_id'] = "";
		setcookie(APP_STR . "_id", "", time() - 3600); /* set the expiration date to one hour ago */
		return;
	}
