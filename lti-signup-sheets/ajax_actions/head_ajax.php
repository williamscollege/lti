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


	function create_and_send_QueuedMessage($DB, $userid, $user_email, $subject, $body, $openingID = 0, $sheetID = 0) {
		// QueuedMessage::factory($db, $user_id, $target, $summary, $body, $opening_id = 0, $sheet_id = 0, $type = 'email' )
		$qm = QueuedMessage::factory($DB, $userid, $user_email, $subject, $body, $openingID, $sheetID);
		$qm->updateDb();

		if (!$qm->matchesDb) {
			// create record failed
			$results['notes'] = "database error: could not create queued message for signup";
			error_log("QueuedMessage failed to insert db record (email subject: $subject)");
			echo json_encode($results);
			exit;
		}
		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			// do not attempt delivery on local workstation
			if (!$_SERVER['SERVER_NAME'] == 'localhost') {
				if (!$qm->attemptDelivery()) {
					// write to errorlog if fails
					error_log("attemptDelivery failed for QueuedMessage (email subject: $subject)");
				}
			}
		}

	}
