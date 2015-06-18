<?php
	/***********************************************
	 ** LTI Application: "Signup Sheets"
	 ** This page processes a launch request from an LTI tool consumer
	 ** About LTI: This application uses LTI to pass minimal user data from the user's authenticated session with the registered Tool Consumer (i.e. Instructure Canvas)
	 **    This LTI must initially be registered with the Tool Consumer.
	 **    A request to launch this application from within the LMS results in the Tool Consumer attempting an LTI handshake with the Tool Provider.
	 **    If successful, the user is allowed access to whatever permissions or features the application grants.
	 **    If not successful, then an error message results and access is blocked.
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	# This page processes a launch request from an LTI tool consumer

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');

	require_once(dirname(__FILE__) . '/lti_lib.php');

	// Session Maintenance: Cancel any existing session
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

			// Check: if no lti user_id, then return FALSE
			if ($this->user->getId()) {
				// Session Maintenance: Clear all existing session data
				util_wipeSession();
				// Session Maintenance: Update the current session id with a newly generated one
				session_regenerate_id(TRUE);
				// Session Maintenance: Prevent/complicate session hijacking ands XSS attacks
				$FINGERPRINT = util_generateRequestFingerprint();

				// Store values from Tool Consumer (Instructure Canvas) as SESSION to persist them for use in this application
				// These SESSION values are used in lti_lib.php and throughout the application

				// Initialise the user session and persist values
				$_SESSION['consumer_key']         = $this->consumer->getKey(); // LTI form value found in db [lti_consumer.consumer_key]
				$_SESSION['resource_id']          = $this->resource_link->getId(); // LTI form value found in db [lti_context.lti_resource_id]
				$_SESSION['userdata']['username'] = $this->resource_link->getSetting('custom_canvas_user_login_id', ''); // LTI form value
				$_SESSION['isAuthenticated']      = TRUE; // this value is specific to application
				$_SESSION['fingerprint']          = $FINGERPRINT; // this value is specific to application

				// SAVE Examples
				// require user be of a specific role: if ($this->user->isLearner() || $this->user->isStaff()) {
				// $_SESSION['consumer_key']      = $this->consumer->getKey();
				// $_SESSION['resource_id']       = $this->resource_link->getId();
				// $_SESSION['user_consumer_key'] = $this->user->getResourceLink()->getConsumer()->getKey();
				// $_SESSION['user_id']           = $this->user->getId();
				// $_SESSION['isStudent']         = $this->user->isLearner();
				// $_SESSION['isContentItem']     = FALSE;
				// $_SESSION['custom_canvas_course_id'] = $this->resource_link->getSetting('custom_canvas_course_id', '');
				// $_SESSION['user_consumer_key'] = $this->user->getResourceLink()->getConsumer()->getKey(); //unnecessary
				// $_SESSION['oauth_nonce']   = $this->resource_link->getSetting('oauth_nonce', ''); // empty
				// $_SESSION['roles']         = $this->resource_link->getSetting('roles', '');
				// echo '<pre>';print_r($_SESSION);echo '</pre>'; exit; // debugging
				// echo '<pre>';print_r($this);echo '</pre>'; exit; // debugging

				// Success: Redirect the user to the application's index page
				$this->redirectURL = getAppUrl();
			}
			else {
				$this->reason = 'Invalid attempt to initialize the LTI tool provider application.';
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
