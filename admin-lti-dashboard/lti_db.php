<?php
	/***********************************************
	 ** Application: "Admin LTI Dashboard"
	 ** About LTI: An application uses LTI to pass minimal user data from the user's authenticated session with the registered Tool Consumer (i.e. Instructure Canvas)
	 **    An LTI must initially be registered with the Tool Consumer.
	 **    A request to launch this application from within the LMS results in the Tool Consumer attempting an LTI handshake with the Tool Provider.
	 **    If successful, the user is allowed access to whatever permissions or features the application grants.
	 **    If not successful, then an error message results and access is blocked.
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	# This page provides a function to verify that database connection exists

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/' . LTI_FOLDER . 'LTI_Tool_Provider.php');

	# Modification needed for local development work
	# require_once(dirname(__FILE__) . '\\' . LTI_FOLDER . '\LTI_Tool_Provider.php');


	# Return a connection to the database, return FALSE if an error occurs
	function open_db() {
		try {
			$db = new PDO(LTI_DB_NAME, LTI_DB_USERNAME, LTI_DB_PASSWORD);
		}
		catch (PDOException $e) {
			$db                        = FALSE;
			$_SESSION['error_message'] = "Database error {$e->getCode()}: {$e->getMessage()}";
		}

		return $db;
	}

