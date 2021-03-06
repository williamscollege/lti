<?php
	/***********************************************
	 ** Project:    Monitor Williams SIS Imports into Canvas LMS
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Access:     Commandline access only on internal server without web directory
	 ** Purpose:    Monitor the SIS data that Williams sends to Instructure Canvas LMS [cronjob: every 2 hours]
	 **  1. The shell script "get_onecard_data.sh" runs every other hour via cron job, and it does the following:
	 **  - executes the curl call that posts the zipped CSV files to Canvas
	 **  - logs the Canvas "import status" (aka "Return Code") to the "get_onecard_data.log"
	 **  - executes this php file, which then does the following:
	 **  2. This php file opens "get_onecard_data.log", retrieves the most recent entry, and does the following:
	 **  - parses and records the status of CSV files that were created, zipped and sent to Canvas (`dashboard_sis_imports_raw.file_prep_status`)
	 **  - parses and records the final "import status" into discrete fields (`dashboard_sis_imports_raw.[many]`)
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Requires commandline php
	 **  - Enable PHP modules: PDO, mysqli, curl, mbyte, dom
	 ***********************************************/


	require_once(dirname(__FILE__) . '/dashboard_institution.cfg.php');
	require_once(dirname(__FILE__) . '/dashboard_connDB.php');
	require_once(dirname(__FILE__) . '/dashboard_util.php');


	#------------------------------------------------#
	# Security: Prevent web access to this file
	#------------------------------------------------#
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		exit;        // prevent script from running as a web application
	}
	else {
		// script ran via server commandline, not as web application
		// $flag_is_cron_job = 1; // TRUE
	}


	#------------------------------------------------#
	# IMPORTANT STEPS TO REMEMBER
	#------------------------------------------------#
	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = FALSE;


	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name    = "Commandline: Dashboard Fetch SIS Upload Initial Status";
	$str_event_action    = "error_dashboard_1_get_canvas_lacks_arg";
	$str_log_path_simple = 'n/a';
	$file_path           = "/var/log/";                // internal_server:/opt/canvas_uploads/
	$file_name           = "get_onecard_data.log";     // file: get_onecard_data.log
	$str_delimiter_01    = "====================================";
	$str_delimiter_02    = "Return Code:";
	$str_delimiter_03    = '"created_at":';
	$str_delimiter_04    = '"id":';


	// check for existence of file
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
		// echo "str_fragment:" . $str_fragment . "<br />";
		// echo "int_pos_begin_created_at:" . $int_pos_begin_created_at . ", int_pos_end_created_at:" . $int_pos_end_created_at . "<br />";
		echo "parsed_created_at:<br />" . $parsed_created_at . "<hr />";
	}

	// parse out "id" value from: log_last_return_code
	$int_pos_begin_id = stripos($log_last_return_code, $str_delimiter_04) + strlen($str_delimiter_04); // find: needle; add chars of delimiter
	$str_fragment     = substr($log_last_return_code, $int_pos_begin_id); // return: conveniently shortened haystack
	$int_pos_end_id   = stripos($str_fragment, ","); // find: needle
	$parsed_import_id = substr($log_last_return_code, $int_pos_begin_id, $int_pos_end_id);
	if ($debug) {
		// echo "str_fragment:" . $str_fragment . "<br />";
		// echo "int_pos_begin_id:" . $int_pos_begin_id . ", pos_id_end:" . $int_pos_end_id . "<br />"; // ie 9126653
		echo "parsed_import_id:<br />" . $parsed_import_id . "<hr />";
	}

	#------------------------------------------------#
	# SQL Purpose: Check if this `curl_import_id` already exists
	#------------------------------------------------#
	$queryCheckExists = "
		SELECT * FROM `dashboard_sis_imports_raw` WHERE `curl_import_id` = " . $parsed_import_id . ";
	";
	$resultsCheckExists = mysqli_query($connString, $queryCheckExists) or
	die(mysqli_error($connString));

	$check_existence = mysqli_num_rows($resultsCheckExists);

	if ($check_existence) {
		#------------------------------------------------#
		# SQL Purpose: Update existing record using captured data
		#------------------------------------------------#
		$queryEditData = "
			UPDATE
				`dashboard_sis_imports_raw`
			SET
				`cronjob_datetime` = now()
				,`created_at` = '" . mysqli_real_escape_string($connString, $parsed_created_at) . "'
				-- ,`ended_at` =
				,`file_prep_status` = '" . mysqli_real_escape_string($connString, $log_last_file_prep_status) . "'
				,`curl_return_code` = '" . mysqli_real_escape_string($connString, $log_last_return_code) . "'
				-- ,`curl_import_id` =
				-- ,`curl_final_import_status` =
			WHERE
				`curl_import_id` = " . $parsed_import_id . "
		";

		if ($debug) {
			echo "<pre>queryEditData = " . $queryEditData . "</pre>";
		}
		else {
			$resultsEditData = mysqli_query($connString, $queryEditData) or
			die(mysqli_error($connString));
		}
	}
	else {
		#------------------------------------------------#
		# SQL Purpose: Insert new record using captured data
		#------------------------------------------------#
		$queryInsertData = "
		INSERT INTO
			`dashboard_sis_imports_raw`
			(
				`cronjob_datetime`
				,`created_at`
				,`ended_at`
				,`file_prep_status`
				,`curl_return_code`
				,`curl_import_id`
				,`curl_final_import_status`
			)
			VALUES
			(
				now()
				,'" . mysqli_real_escape_string($connString, $parsed_created_at) . "'
				, NULL
				, '" . mysqli_real_escape_string($connString, $log_last_file_prep_status) . "'
				, '" . mysqli_real_escape_string($connString, $log_last_return_code) . "'
				, " . $parsed_import_id . "
				, NULL
			)
		";

		if ($debug) {
			echo "<pre>queryInsertData = " . $queryInsertData . "</pre>";
		}
		else {
			$resultsInsertData = mysqli_query($connString, $queryInsertData) or
			die(mysqli_error($connString));
		}
	}
