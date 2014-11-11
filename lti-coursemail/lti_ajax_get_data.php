<?php

	require_once('lti_lib.php');
	require_once('util.php');

	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	$courseID = htmlentities((isset($_REQUEST["ajaxVal_Course"])) ? util_quoteSmart($_REQUEST["ajaxVal_Course"]) : 0);

	#------------------------------------------------#
	# Set API variables
	#------------------------------------------------#
	$apiPathPrefix   = "/api/v1/courses/";
	$apiPathEndpoint = "/enrollments?per_page=100";

	#------------------------------------------------#
	# Validation
	#------------------------------------------------#
	if ($apiPathPrefix == "" || $courseID <= 0 || $apiPathEndpoint == "") {
		echo "Invalid values for API call in file: 'lti_ajax_get_data.php'. Exiting.";
		exit;
	}

	#------------------------------------------------#
	# get Course Enrollments (Canvas API default behavior is to return 10 user enrollments; the 'per_page' param enables a maximum of 100 users; the 'page' param enables additional page results)
	#------------------------------------------------#

	# START: LOCAL TESTING
	if ($courseID == 123456) {
		$output = array(
			array('user_id' => "100001", 'login_id' => 'aa1', 'email' => "aa1@acme.edu", 'role' => 'StudentEnrollment', 'section_id' => '300001', 'section_name' => 'Friendly Reptiles', 'full_name' => 'Andy Alligator', 'sortable_name' => 'Alligator, Andy'),
			array('user_id' => "100002", 'login_id' => 'tt1', 'email' => "tt1@acme.edu", 'role' => 'TeacherEnrollment', 'section_id' => '300001', 'section_name' => 'Friendly Reptiles', 'full_name' => 'Tony Tiger', 'sortable_name' => 'Tiger, Tony'),
			array('user_id' => "100003", 'login_id' => 'zz1', 'email' => "zz1@acme.edu", 'role' => 'StudentEnrollment', 'section_id' => '300002', 'section_name' => 'Grumpy Reptiles', 'full_name' => 'Zaney Zebra', 'sortable_name' => 'Zebra, Zaney')
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
		$page                  = 1;
		$finished              = FALSE; // we're not finished yet (we just started)
		$curl_results_combined = array();

		// Loop to get all page results for courses with higher attendance
		while (!$finished) { // while not finished
			// set url
			curl_setopt($ch, CURLOPT_URL, LTI_TOOL_CONSUMER_URL . $apiPathPrefix . $courseID . $apiPathEndpoint . '&page=' . $page);

			// return the transfer as a string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

			// container for json output string
			$curl_results = curl_exec($ch);

			// check for multiple "pages" of data; if exists, concatenate, and exit when no more pages exists
			if ($curl_results == "[]" || $curl_results == "") { // if this page of results DOES NOT exist...
				// this will successfully fail when no more paged sets of data exist
				$finished = TRUE; // ...we are finished
			}
			else {
				// convert the returned json data to array
				$tmp_output = json_decode($curl_results, TRUE);

				// push new array items onto array container
				foreach ($tmp_output AS $obj) {
					array_push($curl_results_combined, $obj); // same as: $curl_results_combined[] = $obj;
				}
			}
			$page++; // increment page
		}

		// close curl resource to free up system resources
		curl_close($ch);

		// trim out cruft: remove incomplete enrollments
		foreach ($curl_results_combined as $i => $row) {
			if ($row['enrollment_state'] == "invited") {
				// remove 'invited' (i.e. pending) enrollments
				unset($curl_results_combined[$i]);
			}
			elseif (isset($row['user']['login_id']) == FALSE) {
				// remove objects for which 'login_id' is null
				unset($curl_results_combined[$i]);
			}
		}

		// trim out cruft; save a smaller subset of object indices
		$trimmed_array = array_map(function ($elt) {
			// create consistent email address
			if (strchr($elt['user']['login_id'], "@")) {
				// 'login_id' is an already formed email address
				$email = $elt['user']['login_id'];
			}
			else {
				// 'login_id' is a unix short name (use prefix to create email address)
				$email = $elt['user']['login_id'] . '@williams.edu';
			}
			return array(
				'user_id'       => $elt['user']['id'],
				'login_id'      => $elt['user']['login_id'],
				'email'         => $email,
				'role'          => $elt['role'],
				'section_id'    => $elt['course_section_id'],
				'section_name'  => ((isset($elt['sis_section_id'])) ? $elt['sis_section_id'] : $elt['course_section_id']), // if name is null, store id instead
				'full_name'     => $elt['user']['name'],
				'sortable_name' => $elt['user']['sortable_name']
			);
		}, $curl_results_combined);

		// sort array by last name, A-Z order
		usort($trimmed_array, function ($a, $b) {
			if ($a['sortable_name'] < $b['sortable_name']) {
				return -1;
			}
			if ($a['sortable_name'] > $b['sortable_name']) {
				return 1;
			}
			return 0;
		});

		// return json object
		echo json_encode($trimmed_array);
	}