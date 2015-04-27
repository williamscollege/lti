<?php
	session_start();

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/lang.cfg.php');

	require_once(dirname(__FILE__) . '/classes/ALL_CLASS_INCLUDES.php');

	require_once(dirname(__FILE__) . '/auth.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');

	$FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks

	$DB = util_createDbConnection();

echo 'session user login id = ' . $_SESSION['custom_canvas_user_login_id'];
	if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {

		// Attempt to authenticate using AUTH_LTI class
		if ((isset($_SESSION['custom_canvas_user_login_id'])) && (isset($_SESSION['oauth_nonce']))) {
			// SECTION: not yet authenticated, wants to log in

			// Set global $AUTH to use appropriate class as defined in auth.cfg.php
			$AUTH = $temporary_AUTH_LTI;

			// Authenticate values (username, nonce) provided by Tool_Consumer (Canvas) against values stored in Tool_Provider (LTI) database
			if ($AUTH->authenticate($_SESSION['custom_canvas_user_login_id'], $_SESSION['oauth_nonce'])) {
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
				echo 'TODO - remove echo statment: failure - msg_failed_sign_in'; exit;
				util_redirectToAppHome('failure', 'msg_failed_sign_in');
			}

			// Failure: Attempt AUTH_LDAP class
			// - Write Auth_LTI.class.php class to ensure passed username and nonce values match values in LTI database; if so, create AUTH object, if not then show error code and exit.
		}
		// Attempt to authenticate using AUTH_LDAP class
		elseif ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
			// SECTION: not yet authenticated, wants to log in

			// Set global $AUTH to use appropriate class as defined in auth.cfg.php
			$AUTH = $temporary_AUTH_LDAP;

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
				util_redirectToAppHome('failure', 'msg_failed_sign_in');
			}
		}
		// TODO - Security hardening: should we have an ELSE clause here to catch any non-logged in users, log them, and exit them?
		// NOTE: handling of non-logged-in users is delegated to individual app code pages - the application does NOT automatically require users to be logged in
		//		else {
		//			// SECTION: must be signed in to view pages; otherwise, redirect to index splash page
		//			if (!strpos(APP_FOLDER . "/index.php", $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])) {
		//				// TODO: add logging?
		//				util_redirectToAppHome('info', 'msg_do_sign_in');
		//			}
		//		}
	}
	else { // SECTION: authenticated
		if ($_SESSION['fingerprint'] != $FINGERPRINT) {
			// TODO: add logging?
			util_redirectToAppHomeWithPrejudice();
		}
		if (isset($_REQUEST['submit_signout'])) {
			// SECTION: wants to log out
			util_wipeSession();
			util_redirectToAppHome();
			// NOTE: the above is the same as util_redirectToAppHomeWithPrejudice, but this code is easier to follow/read when the two parts are shown here
		}
	}

	$IS_AUTHENTICATED = util_checkAuthentication();

	if ($IS_AUTHENTICATED) { // SECTION: is signed in

		// now create user object
		$USER = new User(['username' => $_SESSION['userdata']['username'], 'DB' => $DB]);

		// util_prePrintR($USER);
		// util_prePrintR($_SESSION['userdata']);
		// $USER->updateDbFromAuth($_SESSION['userdata']);
	}
	else {
		$USER = User::getOneFromDb(['username' => 'canonical_public'], $DB);
	}
