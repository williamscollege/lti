<?php
	/***********************************************
	 ** Application: "Admin LTI Dashboard"
	 ** About LTI: An application uses LTI to pass minimal user data from the user's authenticated session with the registered Tool Consumer (i.e. Instructure Canvas)
	 **    An LTI must initially be registered with the Tool Consumer.
	 **    A request to launch this application from within the LMS results in the Tool Consumer attempting an LTI handshake with the Tool Provider.
	 **    If successful, the user is allowed access to whatever permissions or features the application grants.
	 **    If not successful, then an error message results and access is blocked.
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	# This page provides general functions to support the application.


	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/lti_db.php');


	# Initialise application session and database connection
	function init(&$db, $checkSession = NULL) {

		$ok = TRUE;

		// Set timezone
		if (!ini_get('date.timezone')) {
			date_default_timezone_set('America/New_York');
		}

		// Session Maintenance: Set session cookie path
		ini_set('session.cookie_path', getAppPath());

		// Session Maintenance: Open session
		session_start();

		// IMPORTANT: These values are created as SESSION values in lti_launch.php and then used in init() fxn in lti_lib.php
		if (!is_null($checkSession) && $checkSession) {
			// Security: Ensure values exist for these fields, else return FALSE
			$ok = isset($_SESSION['consumer_key']) && isset($_SESSION['resource_id']) && isset($_SESSION['userdata']['username']) && isset($_SESSION['isAuthenticated']) && isset($_SESSION['fingerprint']);
		}

		if (!$ok) {
			$_SESSION['error_message'] = 'Unable to open session.';
		}
		else {
			// Open database connection
			$db = open_db(!$checkSession);
			$ok = $db !== FALSE;
			if (!$ok) {
				if (!is_null($checkSession) && $checkSession) {
					// Display error message to LTI users
					// echo $_SESSION['error_message']; exit; // for debugging only
					$_SESSION['error_message'] = 'Unable to open database.'; // updated this msg to be generic for consumer display
				}
				else {
					// Display error message to LTI users
					// echo $_SESSION['error_message']; exit; // for debugging only
					$_SESSION['error_message'] = 'Unable to open database.'; // updated this msg to be generic for consumer display
				}
			}
		}
		return $ok;
	}


	# Get the web path to the application
	function getAppPath() {
		return APP_ROOT_PATH . '/';

		# $root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		# $dir  = str_replace('\\', '/', dirname(__FILE__));
		# $path = str_replace($root, '', $dir) . '/';
		# return $path;
	}


	# Get the URL to the application
	function getAppUrl() {
		$scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") ? 'http' : 'https';
		$url    = $scheme . '://' . $_SERVER['HTTP_HOST'] . getAppPath();
		return $url;
	}

