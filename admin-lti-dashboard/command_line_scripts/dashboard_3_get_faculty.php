<?php
	/***********************************************
	 ** Project:    Monitor Williams SIS Imports into Canvas LMS
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Access:     Commandline access only on internal server without web directory
	 ** Purpose:    Insert list of faculty into Dashboard from most current "faculty_yyymmdd-hhmmss.csv" file (csv created from onecard)
	 **  1. The shell script "get_dashboard_data.sh" runs daily at 4:05am via cron job, and it does the following:
	 **  - fetches faculty data from the source db via STDOUT (the terminal) and writes it to a CSV file
	 **  - executes this php file, and passes the filename of the just-created "faculty_xxx.csv" as a commandline argument
	 **  2. This php file does the following:
	 **  - deletes any records in the ephemeral table (`dashboard_faculty_current`)
	 **  - opens the CSV file (passed as commandline argument) and reads contents into the table (`dashboard_faculty_current`)
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
		$str_action_path_simple = dirname(__FILE__) . "/" . basename(__FILE__);
		$flag_is_cron_job       = 1; // TRUE
	}


	#------------------------------------------------#
	# IMPORTANT STEPS TO REMEMBER
	#------------------------------------------------#
	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = FALSE;


	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name    = "Commandline: Dashboard Update Faculty Table";
	$str_event_action    = "error_dashboard_3_get_faculty_lacks_arg";
	$str_log_path_simple = 'n/a';
	$file_path           = "/opt/canvas_uploads/";         // internal_server:/opt/canvas_uploads/


	#------------------------------------------------#
	# Fetch commandline argument passed from shell script
	#------------------------------------------------#
	if (isset($argv)) {
		$faculty_csv_file = $argv[1];
	}

	if (!isset($argv) || !$argv[1]) {
		// argument failed to be passed. log error.
		create_eventlog(
			$connString,
			$debug,
			mysqli_real_escape_string($connString, $str_event_action),
			mysqli_real_escape_string($connString, $str_log_path_simple),
			mysqli_real_escape_string($connString, $str_action_path_simple),
			$items = 0,
			$adds = 0,
			$edits = 0,
			$removes = 0,
			$skips = 0,
			$errors = 1,
			$str_event_dataset_brief = "argument failed to pass correctly from from shell script",
			$str_event_dataset_full = "argument failed to pass correctly from from shell script",
			$flag_success = 0,
			$flag_is_cron_job
		);
		exit;
	}


	#------------------------------------------------#
	# Check for existence of file
	#------------------------------------------------#
	if (!file($file_path . $faculty_csv_file)) {
		echo "Requested faculty csv file not found! Exiting now...\n";
		exit;
	}


	#------------------------------------------------#
	# SQL Purpose: Delete table contents to clear way for new fresh data
	#------------------------------------------------#
	$queryDeleteFacultyData = "DELETE FROM `dashboard_faculty_current`";

	if ($debug) {
		echo "<pre>queryDeleteFacultyData = " . $queryDeleteFacultyData . "</pre>";
	}
	else {
		$resultsDeleteFacultyData = mysqli_query($connString, $queryDeleteFacultyData) or
		die(mysqli_error($connString));
	}


	#------------------------------------------------#
	# SQL Purpose: Load data and insert into table
	#------------------------------------------------#
	$queryFacultyBulkInsert = "LOAD DATA LOCAL INFILE '" . $file_path . $faculty_csv_file . "'
			INTO TABLE `dashboard_faculty_current`
			FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
			LINES TERMINATED BY '\n'
			IGNORE 1 LINES
			(`wms_user_id`,`username`,`first_name`,`last_name`,`email`);
	";

	if ($debug) {
		echo "<pre>queryFacultyBulkInsert = " . $queryFacultyBulkInsert . "</pre>";
	}
	else {
		$resultsFacultyBulkInsert = mysqli_query($connString, $queryFacultyBulkInsert) or
		die(mysqli_error($connString));
	}


	/*
		#------------------------------------------------#
		# alternate method to fetch contents of file as array
		#------------------------------------------------#
		$file_contents = file($file_path . $faculty_csv_file, FILE_SKIP_EMPTY_LINES);
		if ($debug) {
			echo "argument passed: " . $faculty_csv_file . "\n" . "file_contents:\n";
			util_prePrintR($file_contents);
		}
		// read each line from array and construct one bulk sql statement
		$sql_insert = "";
		foreach ($file_contents as $row_num => $value) {
			if ($debug) {
				echo "<hr>row_num = " . $row_num . "<br>value = " . $value . "<br>";
			}
			$items      = explode(",", $value);
			$item_count = count($items);
			// util_prePrintR($items);
			// create bulk sql insert, then execute
			// $sql_insert .= ;
		}
	*/

