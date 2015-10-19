<?php
	session_start();

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');
	require_once(dirname(__FILE__) . '/../classes/ALL_CLASS_INCLUDES.php');
	require_once(dirname(__FILE__) . '/../auth.cfg.php');
	require_once(dirname(__FILE__) . '/../util.php');


	// TODO: validate the request (user logged in, fingerprint checks out)
	if (!array_key_exists('isAuthenticated', $_SESSION) || !$_SESSION['isAuthenticated']) {
		// not authenticated
		util_wipeSession();
		util_redirectToAppPage('error.php?err=201', 'failure', 'msg_lti_failed_authentication');
		exit;
	}

	// Session Maintenance: Prevent/complicate session hijacking ands XSS attacks
	$FINGERPRINT = util_generateRequestFingerprint();
	if ($_SESSION['fingerprint'] != $FINGERPRINT) {
		// bad fingerprint
		util_wipeSession();
		util_redirectToAppPage('error.php?err=202', 'failure', 'msg_lti_failed_authentication');
		exit;
	}

	// Create database connection object
	$DB = util_createDbConnection();

	// create user object
	$USER = User::getOneFromDb(['username' => $_SESSION['userdata']['username']], $DB);
	// ensure username exists in database
	if (!$USER->matchesDb) {
		// username does not exist
		util_wipeSession();
		util_redirectToAppPage('error.php?err=203', 'failure', 'msg_lti_failed_authentication');
		exit;
	}

	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status'      => 'failure',
		'note'        => 'unknown reason',
		'html_output' => ''
	];
