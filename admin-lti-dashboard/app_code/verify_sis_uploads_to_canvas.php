<?php
	/***********************************************
	 ** Project:    Sync Canvas Users to Dashboard
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Verify Integrity of SIS Uploads to Canvas
	 ** Requirements:
	 **  - Requires populated database tables containing parsed data for analysis in this file
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down folder contain these scripts to prevent any non-Williams admin from accessing files
	 **  - Run every two hours using cron job
	 ** Current features:
	 **  - verify integrity of data by checking recorded values with expected values or ranges
	 **  - report: Log Summary output to browser and written to text file
	 **  - error reporting: send notification to admin upon finding of any soft or hard errors
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, curl, mbyte, dom
	 ***********************************************/


	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../include/connDB.php');
	require_once(dirname(__FILE__) . '/../util.php');
	require_once(dirname(__FILE__) . '/curl_functions.php');

	#------------------------------------------------#
	# IMPORTANT STEPS TO REMEMBER
	#------------------------------------------------#
	# Run PHP file: (1) daily from server via cron job, or (2) manually from browser as web application
	# PHP File currently at: https://apps.williams.edu/admin-lti-dashboard

	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = TRUE;

	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name  = "Verify Integrity of SIS Uploads";
	$str_event_action  = "verify_sis_uploads_to_canvas";
	$flag_hard_error   = FALSE;
	$flag_soft_error   = FALSE;
	$now_datetime      = new DateTime();
	$cronjob_frequency = 7200; // 2 hours = 7200 seconds
	$cronjob_buffer    = 800; // allow buffer of 800 seconds (@13 minutes)
	$error_msg_brief   = "";
	$error_msg_full    = "";

	// set values dynamically
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		// script ran as web application
		$str_action_file_path = $_SERVER['PHP_SELF'];
		$flag_is_cron_job     = 0; // FALSE
	}
	else {
		// script ran as cron job (triggered from server, not web app)
		$str_action_file_path = __FILE__;
		$flag_is_cron_job     = 1; // TRUE
	}

	# ---------------------------------------------------------------------------

	#------------------------------------------------#
	# SQL: fetch the top 1 record from `dashboard_sis_imports_raw`
	#------------------------------------------------#
	$queryRawMostRecent = "
		SELECT * FROM `dashboard_sis_imports_raw` ORDER BY `created_at` DESC LIMIT 1;
	";
	$resultsRawMostRecent = mysqli_query($connString, $queryRawMostRecent) or
	die(mysqli_error($connString));

	# Store all in permanent array
	$arrayRawMostRecent = [];
	while ($row = mysqli_fetch_assoc($resultsRawMostRecent)) {
		array_push($arrayRawMostRecent, $row);
	}
	if ($debug) {
		echo "<hr/>arrayRawMostRecent: (example: arrayRawMostRecent[0][\"created_at\"] is: " . $arrayRawMostRecent[0]["created_at"] . ")";
		util_prePrintR($arrayRawMostRecent);
	}

	#------------------------------------------------#
	# SQL: fetch the top 1 record from `dashboard_sis_imports_parsed`
	#------------------------------------------------#
	$queryParsedMostRecent = "
		SELECT * FROM `dashboard_sis_imports_parsed` ORDER BY `created_at` DESC LIMIT 1;
	";
	$resultsParsedMostRecent = mysqli_query($connString, $queryParsedMostRecent) or
	die(mysqli_error($connString));

	# Store all in permanent array
	$arrayParsedMostRecent = [];
	while ($row = mysqli_fetch_assoc($resultsParsedMostRecent)) {
		array_push($arrayParsedMostRecent, $row);
	}
	if ($debug) {
		echo "<hr/>arrayParsedMostRecent: (example: arrayParsedMostRecent[0][\"created_at\"] is: " . $arrayParsedMostRecent[0]["created_at"] . ")<br />";
		util_prePrintR($arrayParsedMostRecent);
	}


	#------------------------------------------------#
	# logical checks
	#------------------------------------------------#

	// 1. Error (cronjob_frequency): now - most_recent_parsed > 2 hours
	$parsed_created_at = new DateTime($arrayParsedMostRecent[0]["created_at"]);
	if (($now_datetime->getTimestamp() - $parsed_created_at->getTimestamp()) > ($cronjob_frequency + $cronjob_buffer)) {
		// set error values
		$error_msg_brief = "Error (cronjob_frequency): now - most_recent_parsed > 2 hours";
		$flag_hard_error = TRUE;

		// create event log
		create_eventlog(
			$connString,
			$debug,
			$str_event_action,
			$str_log_file_path = "n/a",
			$str_action_file_path,
			$items = 0,
			$changes = 0,
			$errors = 0,
			$error_msg_brief,
			$error_msg_full,
			$flag_success = 0,
			$flag_is_cron_job);
		// TODO send notification
		// queuemail() or sendmail();
	}

	// 2. Error (cronjob_frequency): now - most_recent_raw > 2 hours
	$raw_created_at    = new DateTime($arrayRawMostRecent[0]["created_at"]);
	if (($now_datetime->getTimestamp() - $raw_created_at->getTimestamp()) > ($cronjob_frequency + $cronjob_buffer)) {
		// set error values
		$error_msg_brief = "Error (cronjob_frequency): now - most_recent_raw > 2 hours";
		$flag_hard_error = TRUE;

		// create event log
		create_eventlog(
				$connString,
				$debug,
				$str_event_action,
				$str_log_file_path = "n/a",
				$str_action_file_path,
				$items = 0,
				$changes = 0,
				$errors = 0,
				$error_msg_brief,
				$error_msg_full,
				$flag_success = 0,
				$flag_is_cron_job);
		// TODO send notification
		// queuemail() or sendmail();
	}

	// 3. last_insert_parsed: last parsed id has match of corresponding raw id?




//	$raw_ended_at      = new DateTime($arrayRawMostRecent[0]["ended_at"]);
//	$parsed_ended_at   = new DateTime($arrayParsedMostRecent[0]["ended_at"]);
	// last_insert_raw: parsed id matches corresponding raw id?

	//	$diff_seconds_raw    = $raw_ended_at->getTimestamp() - $raw_created_at->getTimestamp();
	//	$diff_seconds_parsed = $parsed_ended_at->getTimestamp() - $parsed_created_at->getTimestamp();
	//	if ($debug) {
	//		echo "diff_seconds_raw: " . $diff_seconds_raw . "<br />";
	//		echo "diff_seconds_parsed: " . $diff_seconds_parsed . "<br />";
	//	}
	//	$parsed_time_range = date_diff($parsed_ended_at, $parsed_created_at, TRUE);
	//	$parsed_created_at = date_format(new DateTime($arrayParsedMostRecent[0]["created_at"]), "Y-m-d H:i:s");
	//	$parsed_ended_at = date_format(new DateTime($arrayParsedMostRecent[0]["ended_at"]), "Y-m-d H:i:s");
	# If all you care about is seconds then you can use timestamp:
	//	$then = new DateTime('2000-01-01');
	//	$now = new DateTime('now');
	//	$diffInSeconds = $now->getTimestamp() - $then->getTimestamp();
	//	echo $diffInSeconds . "<br />";


	exit;
	# ---------------------------------------------------------------------------

	# TODO -- finish
	$arrayCanvasUsers        = [];
	$arrayLocalUsers         = [];
	$arrayRevisedLocalUsers  = [];
	$boolValidResult         = TRUE;
	$boolUserMatchExists     = FALSE;
	$intCountCurlAPIRequests = 0;
	$intCountPages           = 0; // CAREFUL! for debugging, set to 63. otherwise, set to 0 for live use
	$intCountUsersCanvas     = 0;
	$intCountUsersSkipped    = 0;
	$intCountUsersUpdated    = 0;
	$intCountUsersInserted   = 0;
	$intCountUsersRemoved    = 0;
	$intCountUsersErrors     = 0;

	# Set timezone to keep php from complaining
	date_default_timezone_set(DEFAULT_TIMEZONE);

	# Save initial values for "LOG SUMMARY"
	$beginDateTime       = date('YmdHis');
	$beginDateTimePretty = date('Y-m-d H:i:s');

	# Create new archival log file
	$str_log_file_path = "/logs/" . date("Ymd-His") . "-log-report.txt";
	$myLogFile = fopen(".." . $str_log_file_path, "w") or die("Unable to open file!");

	#------------------------------------------------#
	# Fetch all Canvas user accounts using paged curl calls
	#------------------------------------------------#
	if ($debug) {
		// for testing, always set artificially high initial page count for fewer curl calls (total pages = approx 67)
		$intCountPages = 65;
	}
	while ($boolValidResult) {
		# increment counter
		$intCountPages += 1;

		# Fetch all "Account Users" (store in temporary array)
		$arrayPagedResults = curlFetchUsers($intCountPages, $apiPathPrefix = "api/v1/accounts/98616/", $apiPathEndpoint = "users");

		# increment counter
		$intCountCurlAPIRequests += 1;

		# Store all in permanent array
		foreach ($arrayPagedResults as $usr) {
			array_push($arrayCanvasUsers, $usr);

			# increment counter
			$intCountUsersCanvas += 1;
		}

		// paged results contain values; abort upon reaching the first empty results page (no more pages exist)
		if (count($arrayPagedResults) == 0) {
			$boolValidResult = FALSE;
		}
	}
	if ($debug) {
		util_prePrintR($arrayCanvasUsers);
		echo "<hr/>";
	}


	#------------------------------------------------#
	# SQL: fetch all local `dashboard_users`
	# flag_delete: include all users (deleted or active)
	#------------------------------------------------#
	$queryLocalUsers = "
		SELECT * FROM `dashboard_users`;
	";
	$resultsLocalUsers = mysqli_query($connString, $queryLocalUsers) or
	die(mysqli_error($connString));

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsLocalUsers)) {
		array_push($arrayLocalUsers, $usr);
	}
	if ($debug) {
		echo "<hr/>arrayLocalUsers:<br />";
		echo "(example: arrayLocalUsers[1][\"canvas_user_id\"] is: " . $arrayLocalUsers[1]["canvas_user_id"] . ")<br />";
		util_prePrintR($arrayLocalUsers);
		echo "<hr/>";
	}

	foreach ($arrayCanvasUsers as $canvas_usr) {

		// reset boolean flag
		$boolUserMatchExists = FALSE;

		// set normalized values
		// (new Canvas users are created with sis_user_id having a varchar value; for our local dashboard db purposes, force the varchar to an integer 0)
		$canvas_u_id             = empty($canvas_usr["id"]) ? 0 : $canvas_usr["id"];
		$canvas_u_name           = empty($canvas_usr["name"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["name"]);
		$canvas_u_sortable_name  = empty($canvas_usr["sortable_name"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["sortable_name"]);
		$canvas_u_short_name     = empty($canvas_usr["short_name"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["short_name"]);
		$canvas_u_sis_user_id    = (!is_numeric($canvas_usr["sis_user_id"]) | (empty($canvas_usr["sis_user_id"]))) ? 0 : $canvas_usr["sis_user_id"];
		$canvas_u_integration_id = empty($canvas_usr["integration_id"]) ? 0 : $canvas_usr["integration_id"];
		$canvas_u_sis_login_id   = empty($canvas_usr["sis_login_id"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["sis_login_id"]);
		$canvas_u_sis_import_id  = empty($canvas_usr["sis_import_id"]) ? 0 : $canvas_usr["sis_import_id"];
		$canvas_u_login_id       = empty($canvas_usr["login_id"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["login_id"]);

		// iterate all Local Users, looking for existence of specific Canvas User
		foreach ($arrayLocalUsers as $local_usr) {
			if ($local_usr["canvas_user_id"] == $canvas_u_id) {

				// reset boolean flag
				$boolUserMatchExists = TRUE;

				if ($debug) {
					echo "local_usr[\"canvas_user_id\"]" . $local_usr["canvas_user_id"] . " MATCHES canvas_usr[\"id\"]: " . $canvas_u_id . "<br />";
				}

				// user already exists! now check if it needs updating (local user record matches live Canvas user record)
				// and if local user was deleted previously but now matches Canvas user, then restore that local user
				// compare like values (do not compare local varchar values with Canvas mysqli_real_escape_string values)
				if (
					$local_usr["canvas_user_id"] != $canvas_u_id
					|| $local_usr["name"] != $canvas_usr["name"]
					|| $local_usr["sortable_name"] != $canvas_usr["sortable_name"]
					|| $local_usr["short_name"] != $canvas_usr["short_name"]
					|| $local_usr["sis_user_id"] != $canvas_u_sis_user_id
					// || $local_usr["integration_id"] != $canvas_u_integration_id // ignore if changes exist
					|| $local_usr["sis_login_id"] != $canvas_usr["sis_login_id"]
					// || $local_usr["sis_import_id"] != $canvas_u_sis_import_id // ignore if changes exist
					|| $local_usr["username"] != $canvas_usr["login_id"]
					|| $local_usr["flag_delete"] == 1
				) {
					#------------------------------------------------#
					# UPDATE SQL Record
					# new values exist: update Local User with newer Canvas User values
					# explicitly set: `flag_delete` = FALSE
					#------------------------------------------------#

					$queryEditLocalUser = "
						UPDATE
							`dashboard_users`
						SET
							`canvas_user_id`	= " . $canvas_u_id . "
							,`name`				= '" . $canvas_u_name . "'
							,`sortable_name`	= '" . $canvas_u_sortable_name . "'
							,`short_name`		= '" . $canvas_u_short_name . "'
							,`sis_user_id`		= " . $canvas_u_sis_user_id . "
							,`integration_id`	= " . $canvas_u_integration_id . "
							,`sis_login_id`		= '" . $canvas_u_sis_login_id . "'
							,`sis_import_id`	= " . $canvas_u_sis_import_id . "
							,`username`			= '" . $canvas_u_login_id . "'
							,`updated_at`		= now()
							,`flag_delete`		= FALSE
						WHERE
							dash_id				= " . $local_usr["dash_id"] . "
					";

					if ($debug) {
						echo "<pre>queryEditLocalUser = " . $queryEditLocalUser . "</pre>";
					}
					else {
						$resultsEditLocalUser = mysqli_query($connString, $queryEditLocalUser) or
						die(mysqli_error($connString));
					}

					# increment counter
					$intCountUsersUpdated += 1;

					# Output to browser and txt file
					echo $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Updated User (synced newer Canvas to local)<br />";
					fwrite($myLogFile, $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Updated User (synced newer Canvas to local)\n");
				}
				else {
					# increment counter
					$intCountUsersSkipped += 1;

					# Output to browser and txt file
					// decided to omit skipped output, as there is no need to fill log files daily with 500kb of skipped user info
					// echo $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Skipped User (Canvas matches local)<br />";
					// fwrite($myLogFile, $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Skipped User (Canvas matches local)\n");
				}

				// skip to next Canvas User
				break;
			}
		}
		if (!$boolUserMatchExists) {
			#------------------------------------------------#
			# INSERT SQL Record
			# no match exists. insert new record into db
			#------------------------------------------------#

			$queryAddLocalUser = "
				INSERT INTO
					`dashboard_users`
					(
						`canvas_user_id`
						, `name`
						, `sortable_name`
						, `short_name`
						, `sis_user_id`
						, `integration_id`
						, `sis_login_id`
						, `sis_import_id`
						, `username`
						, `updated_at`
						, `flag_delete`
					)
					VALUES
					(
						" . $canvas_u_id . "
						, '" . $canvas_u_name . "'
						, '" . $canvas_u_sortable_name . "'
						, '" . $canvas_u_short_name . "'
						, " . $canvas_u_sis_user_id . "
						, " . $canvas_u_integration_id . "
						, '" . $canvas_u_sis_login_id . "'
						, " . $canvas_u_sis_import_id . "
						, '" . $canvas_u_login_id . "'
						, now()
						, FALSE
					)
			";

			if ($debug) {
				echo "<pre>queryAddLocalUser = " . $queryAddLocalUser . "</pre>";
			}
			else {
				$resultsAddLocalUser = mysqli_query($connString, $queryAddLocalUser) or
				die(mysqli_error($connString));
			}

			# increment counter
			$intCountUsersInserted += 1;

			# Output to browser and txt file
			echo $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Inserted User (synced newer Canvas to local)<br />";
			fwrite($myLogFile, $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Inserted User (synced newer Canvas to local)\n");
		}
	}

	#------------------------------------------------#
	# SQL: fetch `dashboard_users` (newly updated!)
	#	flag_delete (only fetch active users: `flag_delete` = FALSE)
	#------------------------------------------------#
	$queryRevisedLocalUsers = "
		SELECT * FROM `dashboard_users` WHERE `flag_delete` = FALSE;
	";
	$resultsRevisedLocalUsers = mysqli_query($connString, $queryRevisedLocalUsers) or
	die(mysqli_error($connString));

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsRevisedLocalUsers)) {
		array_push($arrayRevisedLocalUsers, $usr);
	}

	if ($debug) {
		echo "<hr/>arrayRevisedLocalUsers:<br />";
		util_prePrintR($arrayRevisedLocalUsers);
	}

	# formatting (last iteration)
	echo "<hr />";
	fwrite($myLogFile, "\n------------------------------\n\n");

	// iterate all Local Users, looking for local Users not found in Canvas User array
	foreach ($arrayRevisedLocalUsers as $local_usr) {
		// reset boolean flag
		$boolUserMatchExists = FALSE;

		foreach ($arrayCanvasUsers as $canvas_usr) {
			if ($canvas_usr["id"] == $local_usr["canvas_user_id"]) {
				// reset boolean flag
				$boolUserMatchExists = TRUE;
			}
		}
		if (!$boolUserMatchExists) {
			#------------------------------------------------#
			# UPDATE SQL Record
			# no match exists
			# explicitly set: `flag_delete` = TRUE
			#------------------------------------------------#
			$queryRemoveLocalUser = "
				UPDATE
					`dashboard_users`
				SET
					`updated_at`		= now()
					,`flag_delete`		= TRUE
				WHERE
					`dash_id`			= " . $local_usr["dash_id"] . "
			";

			if ($debug) {
				echo "<pre>queryRemoveLocalUser = " . $queryRemoveLocalUser . "</pre>";
			}
			else {
				$resultsRemoveLocalUser = mysqli_query($connString, $queryRemoveLocalUser) or
				die(mysqli_error($connString));
			}

			# increment counter
			$intCountUsersRemoved += 1;

			# Output to browser and txt file
			echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Removed Local User (synced newer Canvas to local)<br />";
			fwrite($myLogFile, $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Removed Local User (synced newer Canvas to local)\n");
		}
	}


	#------------------------------------------------#
	# Report: LOG SUMMARY
	#------------------------------------------------#
	// formatting
	echo "<br /><hr />";

	# Store values
	$endDateTime       = date('YmdHis');
	$endDateTimePretty = date('Y-m-d H:i:s');

	$finalReport = array();
	array_push($finalReport, "Date begin: " . $beginDateTimePretty);
	array_push($finalReport, "Date end: " . $endDateTimePretty);
	array_push($finalReport, "Duration: " . convertSecondsToHMSFormat(strtotime($endDateTime) - strtotime($beginDateTime)) . " (hh:mm:ss)");
	array_push($finalReport, "Curl API Requests: " . $intCountCurlAPIRequests);
	array_push($finalReport, "Count: Canvas LMS Users: " . $intCountUsersCanvas);
	array_push($finalReport, "Count: Users Inserted in Dashboard: " . $intCountUsersInserted);
	array_push($finalReport, "Count: Users Updated in Dashboard: " . $intCountUsersUpdated);
	array_push($finalReport, "Count: Users Skipped in Dashboard: " . $intCountUsersSkipped);
	array_push($finalReport, "Count: Users Removed in Dashboard: " . $intCountUsersRemoved);
	array_push($finalReport, "Archived file: " . $str_log_file_path);
	array_push($finalReport, "Project: " . $str_project_name);

	# Stringify for browser, output to txt file
	$firstTimeFlag          = TRUE;
	$str_event_dataset_full = "";
	foreach ($finalReport as $obj) {
		if ($firstTimeFlag) {
			# formatting (first iteration)
			echo "LOG SUMMARY<br />";
			fwrite($myLogFile, "\n\n------------------------------\nLOG SUMMARY\n\n");

			# formatting: first row of db entry will be bolded for later web use
			$str_event_dataset_full .= "<strong>" . $obj . "</strong><br />";
			fwrite($myLogFile, $obj . "\n");
		}
		else {
			$str_event_dataset_full .= $obj . "<br />";
			fwrite($myLogFile, $obj . "\n");
		}

		# reset flag
		$firstTimeFlag = FALSE;
	}
	# formatting (last iteration)
	echo "<hr />";
	fwrite($myLogFile, "\n------------------------------\n\n");

	# Output for browser
	echo $str_event_dataset_full;

	# Close log file
	fclose($myLogFile);


	#------------------------------------------------#
	# Record Event Log
	#------------------------------------------------#

	// set values dynamically
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		// script ran as web application
		$str_action_file_path = $_SERVER['PHP_SELF'];
		$flag_is_cron_job     = 0; // FALSE
	}
	else {
		// script ran as cron job (triggered from server, not web app)
		$str_action_file_path = __FILE__;
		$flag_is_cron_job     = 1; // TRUE
	}

	$str_event_dataset_brief = $intCountUsersInserted . " inserts, " . $intCountUsersUpdated . " updates, " . $intCountUsersRemoved . " deletes";

	$flag_success = 0; // FALSE
	if ($intCountUsersCanvas > 0) {
		$flag_success = 1; // TRUE
	}

	$queryEventLog = "
		INSERT INTO
			`dashboard_eventlogs`
			(
				`event_action`
				, `event_datetime`
				, `event_log_filepath`
				, `event_action_filepath`
				, `num_items`
				, `num_changes`
				, `num_errors`
				, `event_dataset_brief`
				, `event_dataset_full`
				, `flag_success`
				, `flag_cron_job`
			)
			VALUES
			(
				'" . mysqli_real_escape_string($connString, $str_event_action) . "'
				, now()
				, '" . mysqli_real_escape_string($connString, $str_log_file_path) . "'
				, '" . mysqli_real_escape_string($connString, $str_action_file_path) . "'
				, " . count($arrayCanvasUsers) . "
				, " . ($intCountUsersUpdated + $intCountUsersInserted + $intCountUsersRemoved) . "
				, " . ($intCountUsersErrors) . "
				, '" . mysqli_real_escape_string($connString, $str_event_dataset_brief) . "'
				, '" . mysqli_real_escape_string($connString, $str_event_dataset_full) . "'
				, $flag_success
				, $flag_is_cron_job
			)
	";

	if ($debug) {
		echo "<pre>queryEventLog = " . $queryEventLog . "</pre>";
	}
	else {
		$resultsEventLog = mysqli_query($connString, $queryEventLog) or
		die(mysqli_error($connString));
	}

