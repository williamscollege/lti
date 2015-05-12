<?php
	/***********************************************
	 ** LTI Application: "Signup Sheets"
	 ** About LTI: This application uses LTI to pass minimal user data from the user's authenticated session with the registered Tool Consumer (i.e. Instructure Canvas)
	 **    This LTI must initially be registered with the Tool Consumer.
	 **    A request to launch this application from within the LMS results in the Tool Consumer attempting an LTI handshake with the Tool Provider.
	 **    If successful, the user is allowed access to whatever permissions or features the application grants.
	 **    If not successful, then an error message results and access is blocked.
	 ** Author: David Keiser-Clark, Williams College OIT
	 ***********************************************/

	# This page processes a launch request from an LTI tool consumer.

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');

	require_once(dirname(__FILE__) . '/lti_lib.php');

	// Session Maintenance: Cancel any existing session
	session_start();
	$_SESSION = array();
	session_destroy();

	// Initialise database (valid connection, else returns false); initiates session_start()
	$db = NULL;
	init($db);

	$data_connector = LTI_Data_Connector::getDataConnector(LTI_DB_TABLENAME_PREFIX, $db);
	$tool           = new LTI_Tool_Provider($data_connector, 'doLaunch'); // note that this callback fxn is deprecated but still functional as of 20150505
	$tool->setParameterConstraint('oauth_consumer_key', TRUE, 255);
	$tool->setParameterConstraint('resource_link_id', TRUE, 255);
	$tool->setParameterConstraint('user_id', TRUE, 255);
	$tool->setParameterConstraint('roles', TRUE);
	$tool->execute();

	exit;


	# Callback function to process a valid launch request.
	function doLaunch($tool_provider) {

		// Quick Check: if no user_id, then return FALSE
		if ($tool_provider->user->getId()) {

			// Session Maintenance: Clear all existing session data
			util_wipeSession();
			// Session Maintenance: Update the current session id with a newly generated one
			session_regenerate_id(TRUE);
			// Session Maintenance: Prevent/complicate session hijacking ands XSS attacks
			$FINGERPRINT = util_generateRequestFingerprint();

			// Store values from Tool Consumer (Instructure Canvas) as SESSION to persist them for use in this application
			// These SESSION values are used in lti_lib.php and throughout the application

			// Persist values
			$_SESSION['consumer_key']         = $tool_provider->consumer->getKey(); // LTI form value found in db [lti_consumer.consumer_key]
			$_SESSION['resource_id']          = $tool_provider->resource_link->getId(); // LTI form value found in db [lti_context.lti_resource_id]
			$_SESSION['userdata']['username'] = $tool_provider->resource_link->getSetting('custom_canvas_user_login_id', ''); // LTI form value
			$_SESSION['isAuthenticated']      = TRUE; // this value is specific to application
			$_SESSION['fingerprint']          = $FINGERPRINT; // this value is specific to application

			// SAVE NOTES
			// Or, require user to have specific role: if ($tool_provider->user->isLearner() || $tool_provider->user->isStaff()) {
			// Examples of other sometimes useful values:
			// $_SESSION['custom_canvas_course_id'] = $tool_provider->resource_link->getSetting('custom_canvas_course_id', '');
			// $_SESSION['user_consumer_key'] = $tool_provider->user->getResourceLink()->getConsumer()->getKey(); //unnecessary
			// $_SESSION['user_id']           = $tool_provider->user->getId(); //unnecessary
			// $_SESSION['isStudent']         = $tool_provider->user->isLearner();  //unnecessary
			// $_SESSION['oauth_nonce']   = $tool_provider->resource_link->getSetting('oauth_nonce', ''); // empty
			// $_SESSION['roles']         = $tool_provider->resource_link->getSetting('roles', '');
			// debugging
			// echo '<pre>';print_r($_SESSION);echo '</pre>'; exit;
			// echo '<pre>';print_r($tool_provider);echo '</pre>'; exit;

			// Success: Redirect the user to the application's index page
			return getAppUrl();
		}
		else {
			// Failure:
			$tool_provider->reason = "Error: User did not match. Please contact itech@" . INSTITUTION_DOMAIN . " for help.";
			return FALSE;
		}
	}
