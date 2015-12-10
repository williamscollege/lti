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
	$debug = TRUE;

	echo "still in development. exiting now.";
	exit;

	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$file_path         = "/var/log/";				// server: canvas-images:/var/log/
	$file_name         = "get_onecard_data.log";	// file: get_onecard_data.log
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



