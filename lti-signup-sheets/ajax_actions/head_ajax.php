<?php
	session_start();

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');
	require_once(dirname(__FILE__) . '/../classes/ALL_CLASS_INCLUDES.php');
	require_once(dirname(__FILE__) . '/../auth.cfg.php');
	require_once(dirname(__FILE__) . '/../util.php');

	# TODO: validate the request (user logged in, fingerprint checks out)
	if (!array_key_exists('isAuthenticated', $_SESSION) || !$_SESSION['isAuthenticated']) {
		echo 'not authenticated';
		exit;
	}

	$FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks
	if ($_SESSION['fingerprint'] != $FINGERPRINT) {
		echo 'bad fingerprint';
		exit;
	}

	# Create database connection object
	$DB = util_createDbConnection();


	$USER = User::getOneFromDb(['username' => $_SESSION['userdata']['username']], $DB);
	if (!$USER->matchesDb) {
		echo 'user did not load correctly';
		exit;
	}


	// ensure username exists in application database, else error
	if(!util_checkUsernameExistsInDB($_SESSION['userdata']['username'])){
		// failure to find a match between LTI provided username and this application's username (db)
		util_wipeSession();
		util_redirectToAppPage('error.php', 'failure', 'msg_lti_failed_authentication');
	}


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status'      => 'failure',
		'note'        => 'unknown reason',
		'html_output' => ''
	];
