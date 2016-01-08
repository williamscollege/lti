<?php
	/***********************************************
	 ** Project:    Set Canvas Notification Preferences
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Reset Canvas User "Notification Preferences" with custom values using curl PUT calls (do only once per user account)
	 ** Requirements:
	 **  - Requires admin token to make curl requests against Canvas LMS API
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down folder contain these scripts to prevent any non-Williams admin from accessing files
	 **  - delay execution to not exceed Canvas limit of 3000 API requests per hour (http://www.instructure.com/policies/api-policy)
	 **  - extend the typical "max_execution_time" to require as much time as the script requires (without timing out)
	 **  - Run daily using cron job
	 ** Current features:
	 **  - fetch all local Dashboard users where flag_is_set_notification_preference = 0 (these users have not yet had their notif prefs updated)
	 **  - attempt to update Canvas user values using individual curl calls
	 **  - determine if curl PUT was success or failure
	 **  - show script start and end times to help document that this script is keeping within Canvas number of API requests/hour
	 **  - report: Log Summary output to browser and written to text file
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, curl, mbyte, dom
	 ***********************************************/

	# Extend default script timeout to be unlimited (typically default is 300 seconds, from php.ini settings)
	ini_set('MAX_EXECUTION_TIME', -1);
	ini_set('MAX_INPUT_TIME', -1);
	if (ob_get_level() == 0) {
		ob_start();
	}

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
	$debug = FALSE;

	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name        = "Set Canvas Notification Preferences";
	$str_event_action        = "set_canvas_notification_preferences";
	$arrayLocalUsers         = [];
	$boolValidResult         = TRUE;
	$intCountCurlAPIRequests = 0;
	$intCountUsersCanvas     = 0;
	$intCountUsersSkipped    = 0;
	$intCountUsersUpdated    = 0;
	$intCountUsersErrors     = 0;

	# Set timezone to keep php from complaining
	date_default_timezone_set(DEFAULT_TIMEZONE);

	# Save initial values for "LOG SUMMARY"
	$beginDateTime       = date('YmdHis');
	$beginDateTimePretty = date('Y-m-d H:i:s');

	# Create new archival log file
	$str_log_file_path = dirname(__FILE__) . '/../logs/' . date("Ymd-His") . "-log-report.txt";
	$myLogFile = fopen($str_log_file_path, "w") or die("Unable to open file!");


	#------------------------------------------------#
	# SQL: fetch all local `dashboard_users`
	# requirement: flag_is_set_notification_preference = 0 (not set)
	# requirement: flag_delete = 0 (active)
	#------------------------------------------------#
	$queryLocalUsers = "
		SELECT * FROM `dashboard_users` WHERE `flag_is_set_notification_preference` = FALSE AND `flag_delete` = FALSE ORDER BY `canvas_user_id` ASC;
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

	# count users needing notification preferences updated
	$intCountUsersCanvas = count($arrayLocalUsers);

	// iterate all Local Users, reset notification_preference for each Canvas User
	foreach ($arrayLocalUsers as $local_usr) {

		// reset flag for each user
		$boolValidResult = TRUE;

		if ($debug) {
			if ($intCountCurlAPIRequests == 1) {
				// for testing, set a tiny max limiter for quick tests (total users = approx 6600)
				echo "<br />DEBUGGING NOTE: Script forced to stop after curl request # " . $intCountCurlAPIRequests . " completed.";
				break;
				exit;
			}
		}

		#------------------------------------------------#
		# Set Canvas "Notification Preferences" values for one "Account User" using curl call
		#------------------------------------------------#

		# Set Canvas "Notification Preferences" values for one "Account User" using curl call
		$arrayCurlPutResult = curlSetUserNotificationPreferences($local_usr["canvas_user_id"], $local_usr["username"], $apiPathPrefix = "api/v1/users/self/communication_channels/email/", $apiPathEndpoint = "/notification_preferences?as_user_id=");

		if ($debug) {
			echo "<hr/>";
			util_prePrintR($arrayCurlPutResult);
		}

		# increment counter
		$intCountCurlAPIRequests += 1;

		# Store all in permanent array

		// check for curl error
		foreach ($arrayCurlPutResult as $item => $value) {
			if ($item == "errors") {
				$boolValidResult = FALSE;
			}
		}

		if ($boolValidResult) {
			#------------------------------------------------#
			# UPDATE SQL Record
			# Curl was successful. Update Dashboard local db to reflect this action has been completed
			# requirement: `flag_is_set_notification_preference` = 1 (set)
			#------------------------------------------------#

			$queryEditLocalUser = "
				UPDATE
					`dashboard_users`
				SET
					`flag_is_set_notification_preference` = TRUE
				WHERE
					`dash_id` = " . $local_usr["dash_id"] . "
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
			if ($debug) {
				echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Updated notification preferences (reset Canvas LMS values)<br />";
			}
			fwrite($myLogFile, $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Updated notification preferences (reset Canvas LMS values)\n");
		}
		else {
			# note: any non-williams primary_email address values will fail; there are very few of these.
			# consider doing a curl call to get profile, capture primary_email, then re-do the above curl PUT but to the non-williams email address

			# increment counter
			$intCountUsersSkipped += 1;

			# Output to browser and txt file
			if ($debug) {
				echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Skipped: lacks institutional email (unable to match username with expected Canvas profile primary_email)<br />";
			}
			fwrite($myLogFile, $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Skipped: lacks institutional email (unable to match username with expected Canvas profile primary_email)\n");
		}
	}

	# formatting (last iteration)
	if ($debug) {
		echo "<hr />";
	}
	fwrite($myLogFile, "\n------------------------------\n\n");


	#------------------------------------------------#
	# Report: LOG SUMMARY
	#------------------------------------------------#
	// formatting
	if ($debug) {
		echo "<br /><hr />";
	}

	# Store values
	$endDateTime       = date('YmdHis');
	$endDateTimePretty = date('Y-m-d H:i:s');

	$finalReport = array();
	array_push($finalReport, "Date begin: " . $beginDateTimePretty);
	array_push($finalReport, "Date end: " . $endDateTimePretty);
	array_push($finalReport, "Duration: " . convertSecondsToHMSFormat(strtotime($endDateTime) - strtotime($beginDateTime)) . " (hh:mm:ss)");
	array_push($finalReport, "Curl API Requests: " . $intCountCurlAPIRequests);
	array_push($finalReport, "Count: Canvas LMS Users needing updates: " . $intCountUsersCanvas);
	array_push($finalReport, "Count: Users Updated in Dashboard: " . $intCountUsersUpdated);
	array_push($finalReport, "Count: Users Skipped in Dashboard: " . $intCountUsersSkipped);
	array_push($finalReport, "Archived file: " . $str_log_file_path);
	array_push($finalReport, "Project: " . $str_project_name);

	# Stringify for browser, output to txt file
	$firstTimeFlag          = TRUE;
	$str_event_dataset_full = "";
	foreach ($finalReport as $obj) {
		if ($firstTimeFlag) {
			# formatting (first iteration)
			if ($debug) {
				echo "LOG SUMMARY<br />";
			}
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
	if ($debug) {
		echo "<hr />";
	}
	fwrite($myLogFile, "\n------------------------------\n\n");

	# Output for browser
	if ($debug) {
		echo $str_event_dataset_full;
	}

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

	$str_event_dataset_brief = $intCountUsersCanvas . " users: " . $intCountUsersUpdated . " updates, " . $intCountUsersSkipped . " skips";

	// $flag_success = 0; // FALSE
	$flag_success = 1; // TRUE

	create_eventlog(
		$connString,
		$debug,
		mysqli_real_escape_string($connString, $str_event_action),
		mysqli_real_escape_string($connString, $str_log_file_path),
		mysqli_real_escape_string($connString, $str_action_file_path),
		$intCountUsersCanvas,
		$intCountUsersUpdated,
		$intCountUsersSkipped,
		mysqli_real_escape_string($connString, $str_event_dataset_brief),
		mysqli_real_escape_string($connString, $str_event_dataset_full),
		$flag_success,
		$flag_is_cron_job
	);

	// final script status
	echo "done!";

	#------------------------------------------------#
	# End: Avoid hitting the default script timeout of 300 or 720 seconds (depending on default php.ini settings)
	#------------------------------------------------#
	ob_end_flush();
