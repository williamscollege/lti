<?php
	/***********************************************
	 ** LTI Application: "Generic Kaltura Embed"
	 ** This page processes a launch request from an LTI tool consumer
	 ** Purpose: Build a dynamic LTI video player that will play the requested video based on Kaltura params (entry_id, wid) while leveraging Canvas LDAP authentication.
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	# This page processes a launch request from an LTI tool consumer

	require_once(dirname(__FILE__) . '/lib.php');
	require_once(dirname(__FILE__) . '/util.php');

	// Session Maintenance: Cancel any existing session
	session_name(LTI_SESSION_NAME);
	session_start();
	$_SESSION = array();
	session_destroy();


	#------------------------------------------------#
	# Create a custom launch specific class that extends the base class
	#------------------------------------------------#
	class Launch_LTI_Tool_Provider extends LTI_Tool_Provider {

		function __construct($data_connector = '', $callbackHandler = NULL) {

			parent::__construct($data_connector, $callbackHandler);
			$this->baseURL = getAppUrl();
		}

		function onLaunch() {
			global $db;

			// Check the user has an appropriate role
			if ($this->user->isLearner() || $this->user->isStaff()) {
	
				// Session Maintenance: Clear all existing session data
				util_wipeSession();
				// Session Maintenance: Update the current session id with a newly generated one
				session_regenerate_id(TRUE);
	
				// Store values from Tool Consumer (Instructure Canvas) as SESSION to persist them for use in this application
				// These SESSION values are used in lib.php and throughout the application

				// Initialise the user session and persist values
				$_SESSION['consumer_key']      = $this->consumer->getKey();
				$_SESSION['resource_id']       = $this->resource_link->getId();
				$_SESSION['user_consumer_key'] = $this->user->getResourceLink()->getConsumer()->getKey();
				$_SESSION['user_id']           = $this->user->getId();
				$_SESSION['isStudent']         = $this->user->isLearner();
				// Store Canvas Course ID value
				$_SESSION['custom_canvas_course_id'] = $this->resource_link->getSetting('custom_canvas_course_id', '');

				// echo '<pre>';print_r($_SESSION);echo '</pre>'; exit; // debugging
				// echo '<pre>';print_r($this);echo '</pre>'; exit; // debugging

				// Success: Redirect the user to the application's index page
				$this->redirectURL = getAppUrl();
			}
			else {
				$this->reason = 'User has an invalid role type.';
				$this->isOK   = FALSE;
			}

		}

		// fxn hook provided as placeholder; see ratings connect.php for possible future usage
		function onContentItem() {
			// Check that the Tool Consumer is allowing the return of an LTI link
			echo "fxn hook: onContentItem()";
			exit;
		}

		// fxn hook provided as placeholder; see ratings connect.php for possible future usage
		function onDashboard() {
			echo "fxn hook: onDashboard()";
			exit;
		}

		// fxn hook provided as placeholder; see ratings connect.php for possible future usage
		function onRegister() {
			echo "fxn hook: onRegister()";
			exit;
		}

		// fxn hook provided as placeholder; see ratings connect.php for possible future usage
		function onError() {
			$msg = $this->message;
			if ($this->debugMode && !empty($this->reason)) {
				echo "error message :" . $msg;
				$msg = $this->reason;
			}
			$this->error_output = $msg;
		}
	}


	#------------------------------------------------#
	# Initialise database (requires valid connection, else fails); initiates onLaunch()
	#------------------------------------------------#
	$db = NULL;
	if (init($db)) {
		$data_connector = LTI_Data_Connector::getDataConnector(LTI_DB_TABLENAME_PREFIX, $db);
		$tool           = new Launch_LTI_Tool_Provider($data_connector);
		$tool->setParameterConstraint('oauth_consumer_key', TRUE, 255);
		$tool->setParameterConstraint('resource_link_id', TRUE, 255, array('basic-lti-launch-request'));
		$tool->setParameterConstraint('user_id', TRUE, 255, array('basic-lti-launch-request'));
		$tool->setParameterConstraint('roles', TRUE, NULL, array('basic-lti-launch-request'));
	}
	else {
		$tool         = new Launch_LTI_Tool_Provider(NULL);
		$tool->reason = $_SESSION['error_message'];
	}
	$tool->handle_request();

