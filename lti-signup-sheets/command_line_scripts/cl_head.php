<?php

	// PREVENT DIRECT WEB HITS.
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		echo 'no web access to this script';
		// TODO - make sure next line is NOT commented!!!
		exit;
	}


	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');
	require_once(dirname(__FILE__) . '/../classes/ALL_CLASS_INCLUDES.php');
	require_once(dirname(__FILE__) . '/../util.php');


	// Create database connection object
	$DB = util_createDbConnection();

