<?php
	/***********************************************
	 ** Project:    Monitor SIS Uploads: Tracking Williams SIS data to Canvas
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Monitor the SIS data that Williams sends to populate Instructure Canvas LMS
	 ** Requirements:
	 **  - Requires commandline php
	 **  - This file is triggered at conclusion of already existing perl file (which is called by a shell script on a cron job)
	 ** Current features:
	 **  - use existing perl file to send fetched final "import status" to this php file
	 **  - parse and record the final "import status" and insert into database records
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, curl, mbyte, dom
	 ***********************************************/


	require_once(dirname(__FILE__) . '/shell_institution.cfg.php');
	require_once(dirname(__FILE__) . '/shell_connDB.php');
	require_once(dirname(__FILE__) . '/shell_util.php');

	// TODO - Add additional security here?

	#------------------------------------------------#
	# IMPORTANT STEPS TO REMEMBER
	#------------------------------------------------#
	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = FALSE;


	# //////////////////////////////////////////////////
	# BEGIN-TEST

	// fetch commandline argument passed from shell script
	// $curl_response = $argv[1];

	// debugging only
	$curl_response = '{"created_at":"2015-12-07T18:55:05Z","started_at":"2015-12-07T18:55:16Z","ended_at":"2015-12-07T19:09:32Z","updated_at":"2015-12-07T19:09:32Z","progress":100,"id":9138772,"workflow_state":"imported_with_messages","data":{"import_type":"instructure_csv","supplied_batches":["term","course","section","user","enrollment"],"counts":{"accounts":0,"terms":19,"abstract_courses":0,"courses":1534,"sections":1534,"xlists":0,"users":4372,"enrollments":12884,"groups":0,"group_memberships":0,"grade_publishing_results":0}},"batch_mode":null,"batch_mode_term_id":null,"override_sis_stickiness":null,"add_sis_stickiness":null,"clear_sis_stickiness":null,"diffing_data_set_identifier":null,"diffed_against_import_id":null,"processing_warnings":[["users_20151207-135501.csv","user 1683415 has already claimed 1117865\'s requested login information, skipping"],["enrollments_20151207-135501.csv","User 1130947 didn\'t exist for user enrollment"]]}';
	// util_prePrintR($curl_response);	echo "<hr />";

	// put returned json into object
	$obj_curl_response = json_decode($curl_response);
	util_prePrintR($obj_curl_response);

	// retrieve values from object for later SQL update and insert
	$obj_processing_warnings = "";
	foreach ($obj_curl_response as $name => $val) {
		if ($name == "created_at") {
			$obj_created_at = util_convert_UTC_string_to_date_object($val);
		}
		if ($name == "started_at") {
			$obj_started_at = util_convert_UTC_string_to_date_object($val);
		}
		if ($name == "ended_at") {
			$obj_ended_at = util_convert_UTC_string_to_date_object($val);
		}
		if ($name == "updated_at") {
			$obj_updated_at = util_convert_UTC_string_to_date_object($val);
		}
		if ($name == "progress") {
			$obj_progress = $val;
		}
		if ($name == "id") {
			$obj_id = $val;
		}
		if ($name == "workflow_state") {
			$obj_workflow_state = $val;
		}
		if ($name == "data") {
			foreach ($val as $data_name => $data_val) {
				if ($data_name == "import_type") {
					$obj_data_import_type = $data_val;
				}
				if ($data_name == "supplied_batches") {
					$obj_data_supplied_batches = implode(", ", $data_val);
				}
				if ($data_name == "counts") {
					foreach ($data_val as $count_name => $count_val) {
						if ($count_name == "accounts") {
							$obj_data_counts_accounts = $count_val;
						}
						if ($count_name == "terms") {
							$obj_data_counts_terms = $count_val;
						}
						if ($count_name == "abstract_courses") {
							$obj_data_counts_abstract_courses = $count_val;
						}
						if ($count_name == "courses") {
							$obj_data_counts_courses = $count_val;
						}
						if ($count_name == "sections") {
							$obj_data_counts_sections = $count_val;
						}
						if ($count_name == "xlists") {
							$obj_data_counts_xlists = $count_val;
						}
						if ($count_name == "users") {
							$obj_data_counts_users = $count_val;
						}
						if ($count_name == "enrollments") {
							$obj_data_counts_enrollments = $count_val;
						}
						if ($count_name == "groups") {
							$obj_data_counts_groups = $count_val;
						}
						if ($count_name == "group_memberships") {
							$obj_data_counts_group_memberships = $count_val;
						}
						if ($count_name == "grade_publishing_results") {
							$obj_data_counts_grade_publishing_results = $count_val;
						}
					}
				}
			}
		}
		if ($name == "batch_mode") {
			$obj_batch_mode = $val;
		}
		if ($name == "batch_mode_term_id") {
			$obj_batch_mode_term_id = $val;
		}
		if ($name == "override_sis_stickiness") {
			$obj_override_sis_stickiness = $val;
		}
		if ($name == "add_sis_stickiness") {
			$obj_add_sis_stickiness = $val;
		}
		if ($name == "clear_sis_stickiness") {
			$obj_clear_sis_stickiness = $val;
		}
		if ($name == "diffing_data_set_identifier") {
			$obj_diffing_data_set_identifier = $val;
		}
		if ($name == "diffed_against_import_id") {
			$obj_diffed_against_import_id = $val;
		}
		if ($name == "processing_warnings") {
			foreach ($val as $warning_name => $warning_val) {
				$obj_processing_warnings .= implode(", ", $warning_val) . "<br />";
			}
		}
	}

	// testing output
	echo "import_type = " . $obj_data_import_type . "<br />";
	echo "created_at=" . $obj_created_at . "<br />";
	echo "created_at=" . $obj_created_at . "<br />";
	echo "ended_at=" . $obj_ended_at . "<br />";
	echo "data_supplied_batches= " . $obj_data_supplied_batches . "<br />";
	echo "enrollments= " . $obj_data_counts_enrollments . "<br />";
	echo "processing_warnings= " . $obj_processing_warnings . "<br />";

	exit;
	#------------------------------------------------#
	# UPDATE SQL Record
	# Curl was successful. Update Dashboard local db to reflect this action has been completed
	# requirement: `flag_is_set_notification_preference` = 1 (set)
	#------------------------------------------------#

	$queryEditSISRaw = "
				UPDATE
					`dashboard_sis_imports_raw`
				SET
					`ended_at` = '" . mysqli_real_escape_string($connString, $obj_ended_at) . "'
					, `curl_raw_import_status` = '" . mysqli_real_escape_string($connString, $curl_response) . "'
				WHERE
					`curl_import_id` = " . $obj_id . "
			";

	if ($debug) {
		echo "<pre>queryEditSISRaw = " . $queryEditSISRaw . "</pre>";
	}
	else {
		$resultsEditSISRaw = mysqli_query($connString, $queryEditSISRaw) or
		die(mysqli_error($connString));
	}

	#------------------------------------------------#
	# SQL: insert captured data into `dashboard_sis_imports_raw`
	#------------------------------------------------#
	$queryCaptureResults = "
			INSERT INTO
				`dashboard_sis_imports_parsed`
				(
					`created_at`
					,`ended_at`
					,`file_prep_status`
					,`curl_raw_return_code`
					,`curl_parsed_import_id`
					,`curl_raw_import_status`
				)
				VALUES
				(
					  NULL
					, NULL
					, NULL
					, NULL
					, 123456
					, '" . mysqli_real_escape_string($connString, $curl_response) . "'
				)
		";

	if ($debug) {
		echo "<pre>queryCaptureResults = " . $queryCaptureResults . "</pre>";
	}
	else {
		$resultsCaptureResults = mysqli_query($connString, $queryCaptureResults) or
		die(mysqli_error($connString));
	}
	echo "still more to develop. exiting now.";
	exit;
	# END-TEST
	# //////////////////////////////////////////////////


	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$file_path        = "/var/log/";                // server: canvas-images:/var/log/
	$file_name        = "get_onecard_data.log";    // file: get_onecard_data.log
	$str_delimiter_01 = "====================================";
	$str_delimiter_02 = "Return Code:";
	$str_delimiter_03 = '"created_at":';
	$str_delimiter_04 = '"id":';


	if ($debug) {
		echo "php script #2 begins...<br />\n";
	}

	if (!file($file_path . $file_name)) {
		echo "Requested log file not found! Exiting now...";
		exit;
	}

	// fetch contents of file as string
	$file_contents = implode('', file($file_path . $file_name));
	if ($debug) {
		echo "file_contents:<br />" . $file_contents . "<hr />";
	}

	// fetch the last (most recent) log record by matching last occurrence of a character string
	$log_last = strrchr($file_contents, $str_delimiter_01);
	$log_last = substr($log_last, 1); // omit the first character from above
	if ($debug) {
		echo "log_last:<br />" . $log_last . "<hr />";
	}

	// fetch first portion of log_last: file_prep_status
	$int_pos_ret_code          = stripos($log_last, $str_delimiter_02);
	$log_last_file_prep_status = substr($log_last, 0, $int_pos_ret_code);
	if ($debug) {
		echo "log_last_file_prep_status:<br />" . $log_last_file_prep_status . "<hr />";
	}

	// fetch second portion of log_last: return_code
	$log_last_return_code = substr($log_last, $int_pos_ret_code + strlen($str_delimiter_02)); // omit text "Return Code:"
	if ($debug) {
		echo "log_last_return_code:<br />" . $log_last_return_code . "<hr />";
	}

	// parse out "created_at" value from: log_last_return_code
	$int_pos_begin_created_at = stripos($log_last_return_code, $str_delimiter_03) + strlen($str_delimiter_03); // find: needle; add chars of delimiter
	$str_fragment             = substr($log_last_return_code, $int_pos_begin_created_at); // return: conveniently shortened haystack
	$int_pos_end_created_at   = stripos($str_fragment, ","); // find: needle
	$parsed_created_at        = str_replace('"', "", substr($log_last_return_code, $int_pos_begin_created_at, $int_pos_end_created_at)); // strip off double quote prefix & suffix
	$parsed_created_at        = util_convert_UTC_string_to_date_object($parsed_created_at);
	if ($debug) {
		// UTC time = "2015-12-08T20:55:05Z"; // 3:55 pm or 15:55
		// converted time = 2015-12-08 15:55:05
		// echo "int_pos_begin_created_at:" . $int_pos_begin_created_at . "<br />";
		// echo "str_fragment:" . $str_fragment . "<br />";
		// echo "int_pos_end_created_at:" . $int_pos_end_created_at . "<br />";
		echo "parsed_created_at:<br />" . $parsed_created_at . "<hr />";
	}

	// parse out "id" value from: log_last_return_code
	$int_pos_begin_id = stripos($log_last_return_code, $str_delimiter_04) + strlen($str_delimiter_04); // find: needle; add chars of delimiter
	$str_fragment     = substr($log_last_return_code, $int_pos_begin_id); // return: conveniently shortened haystack
	$int_pos_end_id   = stripos($str_fragment, ","); // find: needle
	$parsed_import_id = substr($log_last_return_code, $int_pos_begin_id, $int_pos_end_id);
	if ($debug) {
		// expected format of value: 9126653
		// echo "int_pos_begin_id:" . $int_pos_begin_id . "<br />";
		// echo "str_fragment:" . $str_fragment . "<br />";
		// echo "pos_id_end:" . $int_pos_end_id . "<br />";
		echo "parsed_import_id:<br />" . $parsed_import_id . "<hr />";
	}


	#------------------------------------------------#
	# SQL: insert captured data into `dashboard_sis_imports_raw`
	#------------------------------------------------#
	$queryCaptureResults = "
				INSERT INTO
					`dashboard_sis_imports_raw`
					(
						`created_at`
						,`ended_at`
						,`file_prep_status`
						,`curl_raw_return_code`
						,`curl_parsed_import_id`
						,`curl_raw_import_status`
					)
					VALUES
					(
						'" . mysqli_real_escape_string($connString, $parsed_created_at) . "'
						, NULL
						, '" . mysqli_real_escape_string($connString, $log_last_file_prep_status) . "'
						, '" . mysqli_real_escape_string($connString, $log_last_return_code) . "'
						, " . $parsed_import_id . "
						, NULL
					)
			";

	if ($debug) {
		echo "<pre>queryCaptureResults = " . $queryCaptureResults . "</pre>";
	}
	else {
		$resultsCaptureResults = mysqli_query($connString, $queryCaptureResults) or
		die(mysqli_error($connString));
	}


	/*
	FINAL
	{
	"created_at":"2015-12-07T14:55:04Z",
	"started_at":"2015-12-07T14:55:05Z",
	"ended_at":"2015-12-07T15:05:29Z",
	"updated_at":"2015-12-07T15:05:29Z",
	"progress":100,
	"id":9137693,
	"workflow_state":"imported_with_messages",
	"data":{"import_type":"instructure_csv",
	"supplied_batches":["term",
	"course",
	"section",
	"user",
	"enrollment"],
	"counts":{"accounts":0,
	"terms":19,
	"abstract_courses":0,
	"courses":1534,
	"sections":1534,
	"xlists":0,
	"users":4372,
	"enrollments":12883,
	"groups":0,
	"group_memberships":0,
	"grade_publishing_results":0}},
	"batch_mode":null,
	"batch_mode_term_id":null,
	"override_sis_stickiness":null,
	"add_sis_stickiness":null,
	"clear_sis_stickiness":null,
	"diffing_data_set_identifier":null,
	"diffed_against_import_id":null,
	"processing_warnings":[["users_20151207-095501.csv", "user 1683415 has already claimed 1117865's requested login information, skipping"], ["enrollments_20151207-095501.csv", "User 1130947 didn't exist for user enrollment"]]
	}

	notes:
		//if ($debug) {
			// array: get known file (from filepath) into an array using optional flags parameters
			// $trimmed_lines = file($file_name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			// loop through our array, show HTML source as HTML source; and line numbers too.
			//foreach ($trimmed_lines as $line_num => $line) {
			//	echo "Line #<b>{$line_num}</b> :" . htmlspecialchars($line) . "<br />\n";
			//}
			//echo "<hr />";
		//}

	* */



