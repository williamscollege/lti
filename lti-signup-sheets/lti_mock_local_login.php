<?php
	/***********************************************
	 ** LTI Application: "Signup Sheets"
	 ** MOCK AUTHENTICATION "login" page to enable testing on localhost
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	// ***************************
	// Enable localhost testing only!
	// ***************************
	$strServerName = $_SERVER['SERVER_NAME'];
	if (!($strServerName == "localhost") OR ($strServerName == "127.0.0.1")) {
		echo 'ZERO ACCESS';
		exit;
	}

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');


	// Session Maintenance: Cancel any existing session
	session_start();
	$_SESSION = array();
	session_destroy();

	// Session Maintenance: Set session cookie path
	ini_set('session.cookie_path', APP_FOLDER);

	// Session Maintenance: Open session
	session_start();

	// Session Maintenance: Clear all existing session data
	util_wipeSession();
	// Session Maintenance: Update the current session id with a newly generated one
	session_regenerate_id(TRUE);
	// Session Maintenance: Prevent/complicate session hijacking ands XSS attacks
	$FINGERPRINT = util_generateRequestFingerprint();
	// Imitate required session values
	// These SESSION values are used in lti_lib.php and throughout the application

	// Persist values
	$_SESSION['consumer_key']         = "my_consumer_key"; // $this->consumer->getKey(); // LTI form value found in db [lti_consumer.consumer_key]
	$_SESSION['resource_id']          = "my_resource_id"; // $this->resource_link->getId(); // LTI form value found in db [lti_context.lti_resource_id]
	$_SESSION['userdata']['username'] = "dwk2"; //"mockUserJBond"; // TESTINGUSER; // "areinhar"; // "cpaquett", "jjtest", "mbernhar", TESTINGUSER; // $this->resource_link->getSetting('custom_canvas_user_login_id', '');"" // LTI form value
	$_SESSION['isAuthenticated']      = TRUE; // this value is specific to application
	$_SESSION['fingerprint']          = $FINGERPRINT; // this value is specific to application

	// session_write_close();

	header('Location: ' . APP_FOLDER . '/index.php');
	exit;
