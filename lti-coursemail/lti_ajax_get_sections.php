<?php

	require_once(dirname(__FILE__) . '/lti_lib.php');
	require_once(dirname(__FILE__) . '/util.php');

	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	$courseID = htmlentities((isset($_REQUEST["ajaxVal_Course"])) ? util_quoteSmart($_REQUEST["ajaxVal_Course"]) : 0);

	#------------------------------------------------#
	# Set API variables
	#------------------------------------------------#
	$apiPathPrefix   = "/api/v1/courses/";
	$apiPathEndpoint = "/sections";

	#------------------------------------------------#
	# Validation
	#------------------------------------------------#
	if ($apiPathPrefix == "" || $courseID <= 0 || $apiPathEndpoint == "") {
		echo "Invalid values for API call received by: 'ajax get sections file'. Exiting.";
		exit;
	}

	#------------------------------------------------#
	# get Course Sections (a single curl call to the Canvas API should return all course sections)
	#------------------------------------------------#

	# START: LOCAL TESTING
	if ($courseID == 123456) {
		$output = array(
			array('id' => 300001, 'name' => '15P-ARTH-101-01 Intro Class',),
			array('id' => 300002, 'name' => '15P-ARTH-101-01 Section 1 Lab',),
			array('id' => 300003, 'name' => '15P-ARTH-101-01 Section 2 Lab',)
		);
		echo json_encode($output);
		exit;
	}
	# END: LOCAL TESTING
	else {
		//##################################################
		# LIVE: Make API calls to Instructure Canvas using Curl commands
		//##################################################

		// create curl resource
		$ch = curl_init();

		// include extra headers in POST or GET request; this is similar to the CURL -H command line switch
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(LTI_TOOL_CONSUMER_AUTH_TOKEN));

		// Set initial page and boolean condition
		$page     = 1;
		$finished = FALSE; // we're not finished yet (we just started)
		$data_all = array();

		//##################################################
		// get course sections
		// example: curl -H 'Authorization: Bearer <TOKEN_HERE>' https://williams.instructure.com/api/v1/courses/1574287/sections
		//##################################################

		// set url
		curl_setopt($ch, CURLOPT_URL, LTI_TOOL_CONSUMER_URL . $apiPathPrefix . $courseID . $apiPathEndpoint);

		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// container for json output string
		$curl_results = curl_exec($ch);

		// convert the returned json data to array
		$tmp_output = json_decode($curl_results, TRUE);

		// close curl resource to free up system resources
		curl_close($ch);

		// push new array items onto array container
		foreach ($tmp_output AS $obj) {
			array_push($data_all, $obj); // same as: $data_all[] = $obj;
		}

		// util_prePrintR($data_all); // debugging

		// return json object
		echo json_encode($data_all);
	}