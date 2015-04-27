<?php
	/***********************************************
	 ** LTI: "Signup Sheets"
	 ** Purpose: This tool lets any user create a sheet with openings at specific times, and then allows other users to sign up for those openings.
	 ** Purpose: This is analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name:
	 ** Purpose: for example: signing up for a particular lab slot, scheduling office hours, picking a study group time, or more general things like planning a party.
	 ** Author: Williams College OIT, David Keiser-Clark
	 ***********************************************/

	/*
	 * This page processes a launch request from an LTI tool consumer.
	 */

	require_once(dirname(__FILE__) . '/lti_lib.php');

	// Cancel any existing session
	session_name(LTI_SESSION_NAME);
	session_start();
	$_SESSION = array();
	session_destroy();

	// Initialise database
	$db = NULL;
	init($db);

	# Modification needed for local development work
	# init($db, FALSE);

	$data_connector = LTI_Data_Connector::getDataConnector(LTI_DB_TABLENAME_PREFIX, $db);
	$tool           = new LTI_Tool_Provider('doLaunch', $data_connector);
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

		// Check that the user has a valid user_id
		if ($tool_provider->user->getId()) {

			// Initialise the user session
			$_SESSION['consumer_key']      = $tool_provider->consumer->getKey();
			$_SESSION['resource_id']       = $tool_provider->resource_link->getId();
			$_SESSION['user_consumer_key'] = $tool_provider->user->getResourceLink()->getConsumer()->getKey();
			$_SESSION['user_id']           = $tool_provider->user->getId();
			$_SESSION['isStudent']         = $tool_provider->user->isLearner();
			// Store values from Tool Consumer (Instructure Canvas)
			$_SESSION['custom_canvas_user_login_id'] = $tool_provider->resource_link->getSetting('custom_canvas_user_login_id', '');
			$_SESSION['custom_canvas_oauth_nonce']   = $tool_provider->resource_link->getSetting('oauth_nonce', '');
			$_SESSION['custom_canvas_roles']         = $tool_provider->resource_link->getSetting('roles', '');
			// $_SESSION['custom_canvas_course_id'] = $tool_provider->resource_link->getSetting('custom_canvas_course_id', '');

			// Success: Redirect the user to display the list of items for the resource link
			return getAppUrl();
		}
		else {
			// Failure:
			$tool_provider->reason = 'Invalid role.';
			return FALSE;
		}
	}
