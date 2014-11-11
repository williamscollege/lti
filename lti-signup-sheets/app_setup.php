<?php
	session_start();

	require_once('institution.cfg.php');
	require_once('lang.cfg.php');

	require_once('classes/ALL_CLASS_INCLUDES.php');

	require_once('auth.cfg.php');
	require_once('util.php');

	$FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks

    $DB = util_createDbConnection();

    $all_actions = Action::getAllFromDb(['flag_delete'=>false],$DB);
    $ACTIONS = array();
    foreach ($all_actions as $a) {
        $ACTIONS[$a->name] = $a;
    }

	if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
		if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) { // SECTION: not yet authenticated, wants to log in

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
		//echo "<pre>"; print_r($USER); echo "</pre>";

		// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
		$USER->refreshFromDb();
		//echo "<pre>"; print_r($USER); echo "</pre>";
		//print_r($_SESSION['userdata']);
		$USER->updateDbFromAuth($_SESSION['userdata']);
		//echo "<pre>"; print_r($USER); echo "</pre>";

		//echo "<pre>"; print_r($USER); echo "</pre>";
	} else {
        $USER = User::getOneFromDb(['username'=>'canonical_public'],$DB);
    }
