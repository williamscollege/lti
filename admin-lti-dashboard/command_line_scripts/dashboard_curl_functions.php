<?php
	/***********************************************
	 ** Project:    Dashboard for Automating Canvas Maintenance
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ***********************************************/

	require_once(dirname(__FILE__) . '/dashboard_util.php');


	#------------------------------------------------#
	# Project:		"Upload Avatar Image Files to Canvas"
	# Step 1: 		GET: Fetch "Avatar Options" for this user (skip users that already have uploaded a cloud based avatar image)
	# Step 4:		GET: Fetch "Avatar Options" for this user (GET 'opaque_token' for the just-uploaded 'profile_pic.jpg')
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
	# Project:		"Upload Avatar Image Files to Canvas"
	# Step 2:		POST: Upload Image via POST by HTTPS (image must be publicly accessible/viewable)
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlUploadImageToCloud($userID, $apiPathPrefix, $apiPathEndpoint) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/users/self/files?as_user_id=1234567' \
		// -X POST \
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
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . $userID);

		// set form post to true
		curl_setopt($ch, CURLOPT_POST, 1);

		// array containing multiple elements of form data
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
	# Project:		"Upload Avatar Image Files to Canvas"
	# Step 3:		GET: Fetch status of upload (MUST use entire 'status_url' (including file number and opaque string) from previous curl post)
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlUploadStatus($apiFullPath) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/files/58587960/CWd3hAIfzN2ulgcXRvzNJiJlDS41SVUwAnPopwzn/status' \
		// -X GET \
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
	# Project:		"Upload Avatar Image Files to Canvas"
	# Step 5:		PUT: Use the retrieved 'opaque_token' to 'Update User Settings' (Set new avatar image by using 'opaque_token' for 'profile_pic.jpg')
	# Notes:		Make API call to Instructure Canvas using curl command
	#------------------------------------------------#
	function curlConfirmImageUpload($userID, $apiPathPrefix, $apiPathEndpoint, $opaqueToken) {
		// Example of request showing API endpoint
		// curl 'https://<canvas>/api/v1/users/self/?as_user_id=1234567' \
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
		curl_setopt($ch, CURLOPT_URL, TOOL_CONSUMER_URL . $apiPathPrefix . $apiPathEndpoint . $userID);

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
	# Validation Check
	#------------------------------------------------#
	function integerCheck($posNum) {
		if (!isset($posNum) || intval($posNum) <= 0) {
			echo "<br />Invalid Number! Program will DIE now.<br />";
			exit;
		}
	}

