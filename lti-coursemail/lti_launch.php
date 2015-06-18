<?php
	/***********************************************
	 ** LTI Application: "Course Email"
	 ** This page processes a launch request from an LTI tool consumer
	 ** Purpose: Easily email course participants using your preferred email client (i.e Gmail, Thunderbird, Outlook, Mac Mail, etc.)
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	/*
	 * This page processes a launch request from an LTI tool consumer
	 */

	require_once(dirname(__FILE__) . '/lti_lib.php');

	// Session Maintenance: Cancel any existing session
	session_name(LTI_SESSION_NAME);
	session_start();
	$_SESSION = array();
	session_destroy();

	// Initialise database
	$db = NULL;
	init($db);


	$data_connector = LTI_Data_Connector::getDataConnector(LTI_DB_TABLENAME_PREFIX, $db);
	$tool = new LTI_Tool_Provider('doLaunch', $data_connector);
	$tool->setParameterConstraint('oauth_consumer_key', TRUE, 50);
	$tool->setParameterConstraint('resource_link_id', TRUE, 50);
	$tool->setParameterConstraint('user_id', TRUE, 50);
	$tool->setParameterConstraint('roles', TRUE);
	$tool->execute();

	exit;

	###
	### Callback function to process a valid launch request.
	###
	function doLaunch($tool_provider) {

		// Check the user has an appropriate role
		if ($tool_provider->user->isLearner() || $tool_provider->user->isStaff()) {

			// Initialise the user session
			$_SESSION['consumer_key']      = $tool_provider->consumer->getKey();
			$_SESSION['resource_id']       = $tool_provider->resource_link->getId();
			$_SESSION['user_consumer_key'] = $tool_provider->user->getResourceLink()->getConsumer()->getKey();
			$_SESSION['user_id']           = $tool_provider->user->getId();
			$_SESSION['isStudent']         = $tool_provider->user->isLearner();
			// Store values from Tool Consumer (Instructure Canvas)
			$_SESSION['custom_canvas_course_id'] = $tool_provider->resource_link->getSetting('custom_canvas_course_id', '');


			// Success: Redirect the user to display the list of items for the resource link
			return getAppUrl();

		}
		else {
			// Failure:
			$tool_provider->reason = 'Invalid role.';
			return FALSE;

		}

	}

?>
