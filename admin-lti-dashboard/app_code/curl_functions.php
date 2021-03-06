<?php
	/***********************************************
	 ** Project:    Dashboard for Automating Canvas Maintenance
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ***********************************************/

	require_once(dirname(__FILE__) . '/../util.php');

	#------------------------------------------------#
	# Project:		"Sync Canvas Users to Dashboard"
	# Purpose:		GET: Fetch all Canvas user accounts using paged curl calls
	# Parent file:	/app_code/sync_canvas_users_to_dashboard.php
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlFetchUsers($pageNumber, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/accounts/1234567/users?per_page=100&page=1' \
		// -X GET \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($pageNumber);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . "?per_page=100&page=" . $pageNumber);

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$array_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		return $array_output;
	}


	#------------------------------------------------#
	# Project:		"Set Canvas Notification Preferences"
	# Purpose:		PUT: Reset single Canvas user "Notification Preferences" with custom values using curl calls
	# Parent file:	/app_code/set_canvas_notification_preferences.php
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlSetUserNotificationPreferences($userID, $username, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/users/self/communication_channels/email/username@williams.edu/notification_preferences?as_user_id=1234567' \
		// -X PUT \
		// -F "notification_preferences[new_discussion_topic][frequency]=immediately" \
		// -F "notification_preferences[new_discussion_entry][frequency]=immediately" \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// create array of form elements
		$formValues = array(
			'notification_preferences[new_discussion_topic][frequency]' => 'immediately',
			'notification_preferences[new_discussion_entry][frequency]' => 'immediately'
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $username . "@williams.edu" . $apiPathEndpoint . $userID);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// array containing multiple elements of form data
		curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);

		// PUT requests are very simple, just make sure to specify a content-length header and set post fields as a string
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$array_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		return $array_output;
	}


	#------------------------------------------------#
	# Project:		"Auto Enrollments: Canvas Course"
	# Purpose:		POST: Enroll single user into specified course using curl calls
	# Parent files:	/app_code/auto_enroll_canvas_course_fft.php, auto_enroll_canvas_course_oc.php, auto_enroll_canvas_course_adl.php
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlEnrollUserInCourse($intCourseID, $intSectionID, $userID, $type, $enrollment_state, $limit_privileges_to_course_section, $notify, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// Note: Canvas expects user_id to be Canvas user_id
		// curl 'https://<canvas>/api/v1/courses/:course_id/enrollments \' \
		// -X POST \
		// -F 'enrollment[course_section_id]=1' \
		// -F 'enrollment[user_id]=1' \
		// -F 'enrollment[type]=StudentEnrollment' \
		// -F 'enrollment[enrollment_state]=active' \
		// -F 'enrollment[limit_privileges_to_course_section]=true' \
		// -F 'enrollment[notify]=false' \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// create array of form elements
		$formValues = array(
			'enrollment[course_section_id]'                  => $intSectionID,
			'enrollment[user_id]'                            => $userID,
			'enrollment[type]'                               => $type,
			'enrollment[enrollment_state]'                   => $enrollment_state,
			'enrollment[limit_privileges_to_course_section]' => $limit_privileges_to_course_section,
			'enrollment[notify]'                             => $notify
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $intCourseID . $apiPathEndpoint);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// array containing multiple elements of form data
		curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);

		// requests are simple: just specify a content-length header and set desired [GET,PUT,POST,DELETE] field as a string
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$array_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		return $array_output;
	}


	#------------------------------------------------#
	# Project:		"Auto Enrollments: Canvas Course"
	# Purpose:		GET: Fetch enrollment id single user from specified course using curl calls
	# Parent files:	/app_code/auto_enroll_canvas_course_fft.php, auto_enroll_canvas_course_oc.php, auto_enroll_canvas_course_adl.php
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlFetchUserEnrollmentID($intSectionID, $userID, $type, $role, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/sections/1234567/enrollments' \
		// -X GET \
		// -F 'user_id=123456' \
		// -F 'type=StudentEnrollment' \
		// -F 'role=StudentEnrollment' \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// create array of form elements
		$formValues = array(
			'user_id' => $userID,
			'type'    => $type,
			'role'    => $role
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $intSectionID . $apiPathEndpoint);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// array containing multiple elements of form data
		curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);

		// requests are simple: just specify a content-length header and set desired [GET,PUT,POST,DELETE] field as a string
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$array_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		return $array_output;
	}


	#------------------------------------------------#
	# Project:		"Auto Enrollments: Canvas Course"
	# Purpose:		DELETE: Drop (remove) single user from specified course using curl calls
	# Parent files:	/app_code/auto_enroll_canvas_course_fft.php, auto_enroll_canvas_course_oc.php, auto_enroll_canvas_course_adl.php
	# Project:		"Auto Enrollments: Canvas Course"
	#------------------------------------------------#
	function curlDropUserFromCourse($intCourseID, $intEnrollmentID, $task, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/courses/:course_id/enrollments/:enrollment_id \' \
		// -X DELETE \
		// -F 'task=delete' \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($intEnrollmentID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// create array of form elements
		$formValues = array(
			'task' => $task,
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $intCourseID . $apiPathEndpoint . $intEnrollmentID);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// array containing multiple elements of form data
		curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);

		// requests are simple: just specify a content-length header and set desired [GET,PUT,POST,DELETE] field as a string
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$array_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		return $array_output;
	}


	#------------------------------------------------#
	# Project:		"Upload Avatar Image Files to Canvas"
	# Step 1: 		GET: Fetch "Avatar Options" for this user (skip users that already have uploaded a cloud based avatar image)
	# Notes:		Make API call to Instructure Canvas using curl command
	# Notes:		Use temporarily publicly available HTTPS image file sources (remove images from public server when script has finished)
	#------------------------------------------------#
	function curlFetchUserAvatarOptions($userID, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/users/self/avatars?as_user_id=1234567' \
		// -X GET \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . $userID);

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$array_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		return $array_output;
	}


	#------------------------------------------------#
	# Validation Check
	#------------------------------------------------#
	function integerCheck($posNum) {
		if (!isset($posNum) || intval($posNum) <= 0) {
			echo "<br />Invalid Number! Program will DIE now.<br />";
			exit;
		}
	}

