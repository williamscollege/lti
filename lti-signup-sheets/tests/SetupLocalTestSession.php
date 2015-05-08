<?php

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
//	require_once(dirname(__FILE__) . '/../lang.cfg.php');
//	require_once(dirname(__FILE__) . '/../classes/ALL_CLASS_INCLUDES.php');
//	require_once(dirname(__FILE__) . '/../auth.cfg.php');
	require_once(dirname(__FILE__) . '/../util.php');

	$strServerName = $_SERVER['SERVER_NAME'];
	if (($strServerName == "localhost") OR ($strServerName == "127.0.0.1")) {
		// only allow this testing on localhost

		// Cancel any existing session
		session_start();
		$_SESSION = array();
		session_destroy();

		// Session Maintenance
		util_wipeSession(); // Clear all existing session data
		$FINGERPRINT = util_generateRequestFingerprint(); // Prevent/complicate session hijacking ands XSS attacks

//		$DB = util_createDbConnection();

		// Persist values
		$_SESSION['consumer_key']         = 'dummy_value_1';
		$_SESSION['resource_id']          = 'dummy_value_2';
		$_SESSION['userdata']['username'] = TESTINGUSER;
		$_SESSION['isAuthenticated']      = TRUE; // this value is specific to application
		$_SESSION['fingerprint']          = $FINGERPRINT; // this value is specific to application

//		echo $strServerName;
//		util_prePrintR($_SESSION);exit;

		header('Location: ' . APP_FOLDER . '/index.php');
	} else{
		// echo 'ZERO ACCESS';
		exit;
	}

