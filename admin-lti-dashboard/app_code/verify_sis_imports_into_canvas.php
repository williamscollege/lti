<?php
	/***********************************************
	 ** Project:    Sync Canvas Users to Dashboard
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Verify Integrity of SIS Imports into Canvas
	 ** Requirements:
	 **  - Requires populated database tables containing parsed data for analysis in this file
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down parent releases folder to only allow administrator to access/view/run files
	 **  - Run every two hours using cron job
	 ** Current features:
	 **  - verify integrity of data by checking recorded values with expected values or ranges
	 **  - report: Log Summary output to browser and written to text file
	 **  - send mail: for admins, send error notifications
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, curl, mbyte, dom
	 ***********************************************/


	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../include/connDB.php');
	require_once(dirname(__FILE__) . '/../util.php');
	require_once(dirname(__FILE__) . '/curl_functions.php');

	#------------------------------------------------#
	# IMPORTANT STEPS TO REMEMBER
	#------------------------------------------------#
	# Run PHP file: (1) daily from server via cron job, or (2) manually from browser as web application
	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = FALSE;

	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name       = "Verify Integrity of SIS Imports";
	$str_event_action       = "verify_sis_imports_into_canvas";
	$now_datetime           = new DateTime();
	$cron_frequency         = 7200;            // 7200 (cron pushes files to Canvas every 2 hours, on odd hours, like 3:55, 5:55, 7:55)
	$cron_frequency_offset  = 3600;            // 3600 (one hour later, cron gets Canvas import results, on even hours, like 4:55, 6:55, 8:55)
	$curl_duration          = 3600;            // 3600 (1 hour is generous: first script requires a minute, second script requires 10-20 minutes)
	$huge_ten_year_duration = 315532800;    // 315532800 (10 years, plus or minus a leap year day)
	$float_range            = 0.15;            // 15% range is allowable difference between expected values
	$arrayErrorMessages     = [];            // array to hold any error messages
	$flag_match_found       = FALSE;        // flag for testing matches
	$intCountAdds           = 0;
	$intCountEdits          = 0;
	$intCountRemoves        = 0;
	$intCountSkips          = 0;
	$intCountErrors         = 0;

	# Set timezone to keep php from complaining
	date_default_timezone_set(DEFAULT_TIMEZONE);

	// set values dynamically
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		// script ran as web application
		$str_action_path_simple = '/app_code/' . basename($_SERVER['PHP_SELF']);
		$flag_is_cron_job       = 0; // FALSE
	}
	else {
		// script ran via server commandline, not as web application
		$str_action_path_simple = '/app_code/' . basename(__FILE__);
		$flag_is_cron_job       = 1; // TRUE
	}

	# ---------------------------------------------------------------------------

	#------------------------------------------------#
	# SQL Purpose: fetch the top 20 records from `dashboard_sis_imports_raw`
	#------------------------------------------------#
	$queryRaw = "
		SELECT *
		FROM `dashboard_sis_imports_raw`
		ORDER BY `created_at` DESC LIMIT 50;
	";
	$resultsRaw = mysqli_query($connString, $queryRaw) or
	die(mysqli_error($connString));

	$arrayRaw = []; // store results in array
	while ($row = mysqli_fetch_assoc($resultsRaw)) {
		array_push($arrayRaw, $row);
	}
	if ($debug) {
		echo "<hr/>arrayRaw: (example: arrayRaw[0][\"created_at\"] is: " . $arrayRaw[0]["created_at"] . ")";
		util_prePrintR($arrayRaw);
	}

	#------------------------------------------------#
	# SQL Purpose: fetch the top 20 record from `dashboard_sis_imports_parsed`
	#------------------------------------------------#
	$queryParsed = "
		SELECT *
		FROM `dashboard_sis_imports_parsed`
		ORDER BY `created_at` DESC LIMIT 50;
	";
	$resultsParsed = mysqli_query($connString, $queryParsed) or
	die(mysqli_error($connString));

	$arrayParsed = []; // store results in array
	while ($row = mysqli_fetch_assoc($resultsParsed)) {
		array_push($arrayParsed, $row);
	}
	if ($debug) {
		echo "<hr/>arrayParsed: (example: arrayParsed[0][\"created_at\"] is: " . $arrayParsed[0]["created_at"] . ")<br />";
		util_prePrintR($arrayParsed);
	}


	#------------------------------------------------#
	# logical checks
	#------------------------------------------------#
	// if datetime record does not exist, then create an artificial datetime object (avoids checks for NULL value)
	$raw_0_created_at    = isset($arrayRaw[0]["created_at"]) ? new DateTime($arrayRaw[0]["created_at"]) : new DateTime("1999-11-30 00:00:00");
	$raw_0_ended_at      = isset($arrayRaw[0]["ended_at"]) ? new DateTime($arrayRaw[0]["ended_at"]) : new DateTime("1999-11-30 00:00:00");
	$raw_1_created_at    = isset($arrayRaw[1]["created_at"]) ? new DateTime($arrayRaw[1]["created_at"]) : new DateTime("1999-11-30 00:00:00");
	$raw_1_ended_at      = isset($arrayRaw[1]["ended_at"]) ? new DateTime($arrayRaw[1]["ended_at"]) : new DateTime("1999-11-30 00:00:00");
	$parsed_0_created_at = isset($arrayParsed[0]["created_at"]) ? new DateTime($arrayParsed[0]["created_at"]) : new DateTime("1999-11-30 00:00:00");
	$parsed_0_ended_at   = isset($arrayParsed[0]["ended_at"]) ? new DateTime($arrayParsed[0]["ended_at"]) : new DateTime("1999-11-30 00:00:00");


	# 1. Check server failure: is most recent `dashboard_sis_imports_raw` record > 3 hours old?
	$seconds_elapsed = $now_datetime->getTimestamp() - $raw_0_created_at->getTimestamp();
	$intCountEdits += 1;
	if ($seconds_elapsed > ($cron_frequency + $curl_duration)) {
		$intCountEdits += 1;
		if ($seconds_elapsed > $huge_ten_year_duration) {
			array_push($arrayErrorMessages, "Server failure (error 101): Missing or null value for import id " . $arrayRaw[0]["curl_import_id"] . " (`dashboard_sis_imports_raw.created_at`).");
		}
		else {
			$intCountEdits += 1;
			array_push($arrayErrorMessages, "Server failure (error 102): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed since import id " . $arrayRaw[0]["curl_import_id"] . " (`dashboard_sis_imports_raw.created_at`).");
		}
	}

	$seconds_elapsed = $now_datetime->getTimestamp() - $raw_1_ended_at->getTimestamp();
	$intCountEdits += 1;
	if ($seconds_elapsed > ($cron_frequency + $cron_frequency_offset + $curl_duration)) {
		$intCountEdits += 1;
		if ($seconds_elapsed > $huge_ten_year_duration) {
			array_push($arrayErrorMessages, "Server failure (error 103): Missing or null value for import id " . $arrayRaw[1]["curl_import_id"] . " (`dashboard_sis_imports_raw.ended_at`).");
		}
		else {
			$intCountEdits += 1;
			array_push($arrayErrorMessages, "Server failure (error 104): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed since import id " . $arrayRaw[1]["curl_import_id"] . " (`dashboard_sis_imports_raw.ended_at`).");
		}
	}

	# 2. Check server failure: is most recent `dashboard_sis_imports_parsed` record > 4 hours old?
	$seconds_elapsed = $now_datetime->getTimestamp() - $parsed_0_created_at->getTimestamp();
	$intCountEdits += 1;
	if ($seconds_elapsed > ($cron_frequency + $cron_frequency_offset + $curl_duration)) {
		$intCountEdits += 1;
		if ($seconds_elapsed > $huge_ten_year_duration) {
			array_push($arrayErrorMessages, "Server failure (error 105): Missing or null value for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed.created_at`).");
		}
		else {
			$intCountEdits += 1;
			array_push($arrayErrorMessages, "Server failure (error 106): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed since import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed.created_at`).");
		}
	}

	$seconds_elapsed = $now_datetime->getTimestamp() - $parsed_0_ended_at->getTimestamp();
	$intCountEdits += 1;
	if ($seconds_elapsed > ($cron_frequency + $cron_frequency_offset + $curl_duration)) {
		$intCountEdits += 1;
		if ($seconds_elapsed > $huge_ten_year_duration) {
			array_push($arrayErrorMessages, "Server failure (error 107): Missing or null value for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed.ended_at`).");
		}
		else {
			$intCountEdits += 1;
			array_push($arrayErrorMessages, "Server failure (error 108): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed since import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed.ended_at`).");
		}
	}

	# 3. Does most recent parsed "id" have corresponding matching raw "curl_import_id"?
	foreach ($arrayRaw as $row) {
		if ($row["curl_import_id"] == $arrayParsed[0]["id"]) {
			$flag_match_found = TRUE;
			break;
		}
	}
	$intCountEdits += 1;
	if (!$flag_match_found) {
		array_push($arrayErrorMessages, "Match missing (error 109): Corresponding import `dashboard_sis_imports_parsed.id` (" . $arrayParsed[0]["id"] . ") not found in `dashboard_sis_imports_raw.curl_import_id`.");
		$flag_match_found = FALSE; // reset flag
	}

	# 4. Check curl timeout failure: Second to most recent `dashboard_sis_imports_raw`
	$intCountEdits += 1;
	if (($now_datetime->getTimestamp() - $raw_0_ended_at->getTimestamp()) < $huge_ten_year_duration) {
		// use most recent raw record (it contains a valid ended_at datetime)
		$seconds_elapsed = $raw_0_ended_at->getTimestamp() - $raw_0_created_at->getTimestamp();
		$intCountEdits += 1;
		if ($seconds_elapsed > $curl_duration) {
			array_push($arrayErrorMessages, "Curl timeout (error 110): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed between `created_at` and `ended_at` for import id " . $arrayRaw[0]["curl_import_id"] . " (`dashboard_sis_imports_raw`).");
		}
	}
	else {
		// use second most recent (avoid using first as it has a null ended_at datetime)
		$seconds_elapsed = $raw_1_ended_at->getTimestamp() - $raw_1_created_at->getTimestamp();
		$intCountEdits += 1;
		if ($seconds_elapsed > $curl_duration) {
			array_push($arrayErrorMessages, "Curl timeout (error 111): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed between `created_at` and `ended_at` for import id " . $arrayRaw[1]["curl_import_id"] . " (`dashboard_sis_imports_raw`).");
		}
	}

	# 5. Check curl timeout failure: most recent `dashboard_sis_imports_parsed`
	$seconds_elapsed = $parsed_0_ended_at->getTimestamp() - $parsed_0_created_at->getTimestamp();
	$intCountEdits += 1;
	if ($seconds_elapsed > $curl_duration) {
		array_push($arrayErrorMessages, "Curl timeout (error 112): " . number_format($seconds_elapsed / 60, 0) . " minutes elapsed between `created_at` and `ended_at` for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed`).");
	}

	# 6. Check Failure Indicators: missing or null values (`dashboard_sis_imports_raw`)
	$intCountEdits += 1;
	if (!isset($arrayRaw[0]["curl_import_id"]) || $arrayRaw[0]["curl_import_id"] == 0) {
		array_push($arrayErrorMessages, "Failure (error 113): `curl_import_id` value is zero or null for import id " . $arrayRaw[0]["curl_import_id"] . " (`dashboard_sis_imports_raw`).");
	}

	$intCountEdits += 1;
	if (!isset($arrayRaw[0]["curl_return_code"]) || $arrayRaw[0]["curl_return_code"] == "") {
		array_push($arrayErrorMessages, "Failure (error 114): `curl_return_code` value is missing or null for import id " . $arrayRaw[0]["curl_import_id"] . " (`dashboard_sis_imports_raw`).");
	}

	# 7. Check for unexpected values (`dashboard_sis_imports_parsed`)
	$intCountEdits += 1;
	if ($arrayParsed[0]["progress"] < 100) {
		array_push($arrayErrorMessages, "Failure (error 115): `Progress` value should be 100, but instead is " . $arrayParsed[0]["progress"] . " for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if (strpos($arrayParsed[0]["workflow_state"], "fail") === TRUE) {
		array_push($arrayErrorMessages, "Failure (error 116): `workflow_state` contains the word: `fail` for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if (strpos($arrayParsed[0]["workflow_state"], "imported") === FALSE) {
		array_push($arrayErrorMessages, "Failure (error 117): `workflow_state` does not contain the word: `imported` for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if ($arrayParsed[0]["data_supplied_batches"] != "term, course, section, user, enrollment") {
		array_push($arrayErrorMessages, "Failure (error 118): `data_supplied_batches` does not match the file words: `term, course, section, user, enrollment` for import id " . $arrayParsed[0]["id"] . " (`dashboard_sis_imports_parsed`).");
	}

	# 8. Check for unexpected value comparisons/ranges (`dashboard_sis_imports_parsed`)
	$intCountEdits += 1;
	if ($arrayParsed[0]["data_counts_terms"] < $arrayParsed[1]["data_counts_terms"]) {
		array_push($arrayErrorMessages, "Failure (error 119): `data_counts_terms` for import id " . $arrayParsed[0]["id"] . " has lower value (" . $arrayParsed[0]["data_counts_terms"] . ") than previous SIS import (" . $arrayParsed[1]["data_counts_terms"] . ") (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if ($arrayParsed[0]["data_counts_courses"] < ($arrayParsed[1]["data_counts_courses"] - $arrayParsed[1]["data_counts_courses"] * $float_range)) {
		array_push($arrayErrorMessages, "Failure (error 120): `data_counts_courses` for import id " . $arrayParsed[0]["id"] . " has significantly lower value (" . $arrayParsed[0]["data_counts_courses"] . ") than previous SIS import (" . $arrayParsed[1]["data_counts_courses"] . ") (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if ($arrayParsed[0]["data_counts_sections"] < ($arrayParsed[1]["data_counts_sections"] - $arrayParsed[1]["data_counts_sections"] * $float_range)) {
		array_push($arrayErrorMessages, "Failure (error 121): `data_counts_sections` for import id " . $arrayParsed[0]["id"] . " has significantly lower value (" . $arrayParsed[0]["data_counts_sections"] . ") than previous SIS import (" . $arrayParsed[1]["data_counts_sections"] . ") (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if ($arrayParsed[0]["data_counts_users"] < ($arrayParsed[1]["data_counts_users"] - $arrayParsed[1]["data_counts_users"] * $float_range)) {
		array_push($arrayErrorMessages, "Failure (error 122): `data_counts_users` for import id " . $arrayParsed[0]["id"] . " has significantly lower value (" . $arrayParsed[0]["data_counts_users"] . ") than previous SIS import (" . $arrayParsed[1]["data_counts_users"] . ") (`dashboard_sis_imports_parsed`).");
	}

	$intCountEdits += 1;
	if ($arrayParsed[0]["data_counts_enrollments"] < ($arrayParsed[1]["data_counts_enrollments"] - $arrayParsed[1]["data_counts_enrollments"] * $float_range)) {
		array_push($arrayErrorMessages, "Failure (error 123): `data_counts_enrollments` for import id " . $arrayParsed[0]["id"] . " has significantly lower value (" . $arrayParsed[0]["data_counts_enrollments"] . ") than previous SIS import (" . $arrayParsed[1]["data_counts_enrollments"] . ") (`dashboard_sis_imports_parsed`).");
	}


	#------------------------------------------------#
	# prepare values for eventlog (and also for notifications, if errors exist)
	#------------------------------------------------#
	$str_event_dataset_brief = "Success: SIS Import id " . $arrayParsed[0]["id"];
	$str_event_dataset_full  = "<strong>Date created_at: " . $arrayParsed[0]["created_at"] . "</strong><br />";

	if ($arrayErrorMessages) {
		$plural_letter = "";
		if (count($arrayErrorMessages) > 1) {
			$plural_letter = "s";
		}
		$str_event_dataset_brief = count($arrayErrorMessages) . " Error" . $plural_letter . "! (import id: " . $arrayParsed[0]["id"] . ")";
		$str_event_dataset_full .= "Error messages:<br />" . implode("\n<br />", $arrayErrorMessages) . "<br />";

		// send mail: for admins, send error notifications
		$to      = "dwk2@williams.edu,david@psychdata.com"; // avoid using spaces
		$subject = "Dashboard Alert: " . $str_event_dataset_brief . " (\"$str_event_action\")";
		$message = "Application: " . LTI_APP_NAME . "\nScript: $str_project_name (\"$str_event_action\")\n\nReports SIS Import Errors:\n" . implode("\n", $arrayErrorMessages) . "\n\nMore information:\n" . APP_FOLDER;
		$headers = "From: dashboard-no-reply@williams.edu" . "\r\n" .
			"Reply-To: dashboard-no-reply@williams.edu" . "\r\n" .
			"X-Mailer: PHP/" . phpversion();

		mail($to, $subject, $message, $headers);
	}

	if ($debug) {
		echo "error_messages:<br />" . $str_event_dataset_full;
	}


	#------------------------------------------------#
	# Record Event Log
	#------------------------------------------------#
	create_eventlog(
		$connString,
		$debug,
		mysqli_real_escape_string($connString, $str_event_action),
		$str_log_path_simple = "n/a",
		mysqli_real_escape_string($connString, $str_action_path_simple),
		$arrayParsed[0]["id"],
		$intCountAdds,
		$intCountEdits,
		$intCountRemoves,
		$intCountSkips,
		$intCountErrors = count($arrayErrorMessages),
		mysqli_real_escape_string($connString, $str_event_dataset_brief),
		mysqli_real_escape_string($connString, $str_event_dataset_full),
		$flag_success = (count($arrayErrorMessages) == 0) ? 1 : 0,
		$flag_is_cron_job
	);

	// final script status
	echo "done!";

	// save these notes
	//	$parsed_time_range = date_diff($parsed_0_ended_at, $parsed_0_created_at, TRUE);
	//	$parsed_0_created_at = date_format(new DateTime($arrayParsed[0]["created_at"]), "Y-m-d H:i:s");
	//	$parsed_0_ended_at = date_format(new DateTime($arrayParsed[0]["ended_at"]), "Y-m-d H:i:s");
	# If all you care about is seconds then you can use timestamp:
	//	$then = new DateTime('2000-01-01');
	//	$now = new DateTime('now');
	//	$diffInSeconds = $now->getTimestamp() - $then->getTimestamp();
	//	echo $diffInSeconds . "<br />";


