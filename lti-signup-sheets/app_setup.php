<?php
	session_start();

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/lang.cfg.php');
	require_once(dirname(__FILE__) . '/classes/ALL_CLASS_INCLUDES.php');
	require_once(dirname(__FILE__) . '/auth.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');


	// Session Maintenance: Prevent/complicate session hijacking ands XSS attacks
	$FINGERPRINT = util_generateRequestFingerprint();

	// Create database connection object
	$DB = util_createDbConnection();


	if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
		// SECTION: failure to properly set SESSION via LTI
		util_wipeSession();
		util_redirectToAppPage('error.php?err=101', 'failure', 'msg_lti_failed_authentication');
		exit;

		/*	// AUTH_LDAP: Attempt to authenticate using AUTH_LDAP class
				if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
					// SECTION: not yet authenticated, wants to log in
					// Authenticate values (username, password) against LDAP
					if ($AUTH->authenticate($_REQUEST['username'], $_REQUEST['password'])) {
						session_regenerate_id(TRUE);
						$_SESSION['isAuthenticated']       = TRUE;
						$_SESSION['fingerprint']           = $FINGERPRINT;
						$_SESSION['userdata']              = array();
						$_SESSION['userdata']['username']  = $AUTH->username;
						$_SESSION['userdata']['email']     = $AUTH->email;
						$_SESSION['userdata']['firstname'] = $AUTH->fname;
						$_SESSION['userdata']['lastname']  = $AUTH->lname;
						$_SESSION['userdata']['sortname']  = $AUTH->sortname;
						util_redirectToAppHome();
					}
					else {
						util_wipeSession();
						util_redirectToAppPage('error.php?err=102', 'failure', 'msg_failed_sign_in');
						exit;
					}
				}
		*/
		// TODO - LDAP - Security hardening: should we have an ELSE clause here to catch any non-logged in users, log them, and exit them?
		// NOTE: handling of non-logged-in users is delegated to individual app code pages - the application does NOT automatically require users to be logged in
		//		else {
		//			// SECTION: must be signed in to view pages; otherwise, redirect to index splash page
		//			if (!strpos(APP_FOLDER . "/index.php", $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])) {
		//				// TODO: LDAP - add logging?
		//				util_wipeSession();
		//				util_redirectToAppPage('error.php?err=103', 'info', 'msg_do_sign_in');
		//				exit;
		//			}
		//		}
	}
	else {
		// SECTION: authenticated
		if ($_SESSION['fingerprint'] != $FINGERPRINT) {
			// error: suspected session spoofing attack. abort.
			// TODO: add logging?
			util_wipeSession();
			util_redirectToAppPage('error.php?err=104', 'failure', 'msg_failed_sign_in');
			exit;
		}
		/*	// signout button
			if (isset($_REQUEST['submit_signout'])) {
				// SECTION: wants to log out
				util_wipeSession();
				util_redirectToAppHome();
				exit;
			}
		*/
	}

	$IS_AUTHENTICATED = util_checkAuthentication();

	if ($IS_AUTHENTICATED) {
		// SECTION: is signed in

		// create user object
		$USER = User::getOneFromDb(['username' => $_SESSION['userdata']['username']], $DB);
		// ensure username exists in database
		if (!$USER->matchesDb) {
			// username does not exist
			util_wipeSession();
			util_redirectToAppPage('error.php?err=105', 'failure', 'msg_lti_failed_authentication');
			exit;
		}

		// SAVE for LDAP:
		// create user object
		// $USER = new User(['username' => $_SESSION['userdata']['username'], 'DB' => $DB]);
		// check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
		// $USER->refreshFromDb();
		// util_prePrintR($USER);
		// util_prePrintR($_SESSION['userdata']);
		// $USER->updateDbFromAuth($_SESSION['userdata']);
		// util_prePrintR($USER);
	}
	else {
		// SECTION: not authenticated, exit
		util_wipeSession();
		util_redirectToAppPage('error.php?err=106', 'failure', 'msg_lti_failed_authentication');
		exit;
	}
