<?php
	session_start();

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/lang.cfg.php');
	require_once(dirname(__FILE__) . '/classes/ALL_CLASS_INCLUDES.php');
	require_once(dirname(__FILE__) . '/auth.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');


	// used to prevent/complicate session hijacking ands XSS attacks
	$FINGERPRINT = util_generateRequestFingerprint();

	$DB = util_createDbConnection();

	// ensure username exists in application database, else error
	if(!util_checkUsernameExistsInDB($_SESSION['userdata']['username'])){
		// failure to find a match between LTI provided username and this application's username (db)
		util_wipeSession();
		util_redirectToAppPage('error.php?err=1', 'failure', 'msg_lti_failed_authentication');
	}


	if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
		// SECTION: failure to properly set SESSION via LTI
		util_wipeSession();
		util_redirectToAppPage('error.php?err=2', 'failure', 'msg_lti_failed_authentication');
		exit;

		/*
			// AUTH_LDAP: Attempt to authenticate using AUTH_LDAP class
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
						util_redirectToAppPage('error.php', 'failure', 'msg_failed_sign_in');
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
		//				util_redirectToAppPage('error.php', 'info', 'msg_do_sign_in');
		//			}
		//		}
	}
	else {
		// SECTION: authenticated
		if ($_SESSION['fingerprint'] != $FINGERPRINT) {
			// error: Suspected session spoofing attack. abort.
			// TODO: add logging?
			util_wipeSession();
			util_redirectToAppPage('error.php', 'failure', 'msg_failed_sign_in');
		}
		/*if (isset($_REQUEST['submit_signout'])) {
			// SECTION: wants to log out
			util_wipeSession();
			util_redirectToAppHome();
			// NOTE: the above is the same as util_redirectToAppHomeWithPrejudice, but this code is easier to follow/read when the two parts are shown here
		}*/
	}

	$IS_AUTHENTICATED = util_checkAuthentication();

	if ($IS_AUTHENTICATED) { // SECTION: is signed in

		// now create user object
		$USER = new User(['username' => $_SESSION['userdata']['username'], 'DB' => $DB]);

		// if using LDAP:
		// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
		// $USER->refreshFromDb();
		// util_prePrintR($USER);
		// util_prePrintR($_SESSION['userdata']);
		// $USER->updateDbFromAuth($_SESSION['userdata']);
		// util_prePrintR($USER);
	}
	else {
		// $USER = User::getOneFromDb(['username' => 'canonical_public'], $DB);
		util_wipeSession();
		util_redirectToAppPage('error.php', 'failure', 'msg_lti_failed_authentication');
		exit;
	}
