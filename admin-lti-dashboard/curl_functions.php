<?php
	/***********************************************
	 ** Project:    Dashboard for Automating Canvas Maintenance
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ***********************************************/


	require_once(dirname(__FILE__) . '/util.php');


	#------------------------------------------------#
	# Project:		"Sync Canvas Users to Dashboard"
	# Purpose:		Fetch all Canvas user accounts using paged curl calls
	# Parent file:	sync_canvas_users_to_dashboard.php
	# Notes:		Make API call to Instructure Canvas using Curl (GET) command
	#------------------------------------------------#
	function curlFetchUsers($pageNumber, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://williams.test.instructure.com/api/v1/accounts/1234567/users?per_page=100&page=1' \
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
	# Purpose:		Reset Canvas User "Notification Preferences" with custom values using curl PUT calls (do only once per user account)
	# Parent file:	set_canvas_notification_preferences.php
	# Notes:		Make API call to Instructure Canvas using Curl (PUT) command
	#------------------------------------------------#
	function curlSetUserNotificationPreferences($userID, $username, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://williams.test.instructure.com/api/v1/users/self/communication_channels/email/username@williams.edu/notification_preferences?as_user_id=1234567' \
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
			'notification_preferences[new_discussion_entry][frequency]' => 'immediately',
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $username . "@williams.edu" . $apiPathEndpoint . $userID);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// post array containing multiple elements of form data
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


	# TODO break point old code below

	#------------------------------------------------#
	# Project:    "Bulk Push Avatar Image Files" using publicly available HTTPS image file sources
	# Step 1: Fetch "Avatar Options" for this user (skip users that already have uploaded a cloud based avatar image)
	# Step 4: Fetch "Avatar Options" for this user (GET 'opaque_token' for the just-uploaded 'profile_pic.jpg')
	# Notes: Make API call to Instructure Canvas using Curl commands. This will consist of a single curl command.
	#------------------------------------------------#
	function curlListAvatarOptions($userID, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://williams.test.instructure.com/api/v1/users/self/avatars?as_user_id=1234567' \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . "?as_user_id=" . $userID);

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
	# Step 2: Upload Image via POST by HTTPS (image must be publicly accessible/viewable)
	# Notes: Make API call to Instructure Canvas using Curl commands. This will consist of a single curl command.
	#------------------------------------------------#
	function curlUploadImageToCloud($userID, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://williams.test.instructure.com/api/v1/users/self/files?as_user_id=1234567' \
		// -F 'url=http://placekitten.com.s3.amazonaws.com/homepage-samples/200/286.jpg' \
		// -F 'name=profile_pic.jpg' \
		// -F 'content_type=image/jpeg' \
		// -F 'parent_folder_path=profile pictures' \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// pass form elements as array
		$formValues = array(
			'url'                => PUBLIC_IMAGES_FOLDER . $userID . '.jpg',
			'name'               => 'profile_pic.jpg',
			'content_type'       => 'image/jpeg',
			'parent_folder_path' => 'profile pictures'
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . "?as_user_id=" . $userID);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// post array containing multiple elements of form data
		curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);

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
	# Step 3: Check status of upload (MUST use entire 'status_url' (including file number and opaque string) from previous curl post)
	# Notes: Make API call to Instructure Canvas using Curl commands. This will consist of a single curl command.
	#------------------------------------------------#
	function curlUploadStatus($apiFullPath) {
		// Example of request showing API endpoint
		// curl 'https://williams.test.instructure.com/api/v1/files/58587960/CWd3hAIfzN2ulgcXRvzNJiJlDS41SVUwAnPopwzn/status' \
		// -H "Authorization: Bearer TOKEN"

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// set url
		curl_setopt($ch, CURLOPT_URL, $apiFullPath);

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
	# Step 5: Use the retrieved 'opaque_token' to 'Update User Settings' (Set new avatar image by using 'opaque_token' for 'profile_pic.jpg')
	# Notes: Make API PUT call to Instructure Canvas using Curl commands. This will consist of a single curl command.
	#------------------------------------------------#
	function curlConfirmImageUpload($userID, $apiPathPrefix, $apiPathEndpoint, $opaqueToken) {
		// Example of request showing API endpoint
		// curl 'https://williams.test.instructure.com/api/v1/users/self/?as_user_id=1234567' \
		// -X PUT \
		// -F 'user[avatar][token]=563c07bb2c2d7b30647e9dbe182c5bff468eb859' \
		// -H "Authorization: Bearer TOKEN"

		// basic validation
		integerCheck($userID);

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(TOOL_CONSUMER_AUTH_TOKEN));

		// create array of form elements
		$formValues = array(
			'user[avatar][token]' => $opaqueToken
		);

		// set url
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . "?as_user_id=" . $userID);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// post array containing multiple elements of form data
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
	# Validation Check
	#------------------------------------------------#
	function integerCheck($posNum) {
		if (!isset($posNum) || intval($posNum) <= 0) {
			echo "<br />Invalid Number! Program will DIE now.<br />";
			exit;
		}
	}

