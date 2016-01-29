<?php
	/***********************************************
	 ** Project:    Set Canvas Notification Preferences
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Reset Canvas User "Notification Preferences" with custom values using curl calls
	 ** Requirements:
	 **  - Requires admin token to make curl requests against Canvas LMS API
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down parent releases folder to only allow administrator to access/view/run files
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
	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = FALSE;

	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name        = "Set Canvas Notification Preferences";
	$str_event_action        = "set_canvas_notification_preferences";
	$arrayLocalUsers         = [];
	$boolValidResult         = TRUE;
	$strUIDsUpdated          = "";
	$intCountCurlAPIRequests = 0;
	$intCountNeedsUpdate     = 0;
	$intCountAdds            = 0;
	$intCountEdits           = 0;
	$intCountRemoves         = 0;
	$intCountSkips           = 0;
	$intCountErrors          = 0;

	# Set timezone to keep php from complaining
	date_default_timezone_set(DEFAULT_TIMEZONE);

	# Save initial values for "LOG SUMMARY"
	$beginDateTime       = date('YmdHis');
	$beginDateTimePretty = date('Y-m-d H:i:s');

	# Create new archival log file
	$str_log_file        = date("Ymd-His") . "-log-report.txt";
	$str_log_path_simple = '/logs/' . $str_log_file;
	$str_log_path_full   = dirname(__FILE__) . '/../logs/' . $str_log_file;
	$myLogFile = fopen($str_log_path_full, "w") or die("Unable to open file!");


	#------------------------------------------------#
	# SQL Purpose: fetch all local `dashboard_users`
	# requirement: flag_is_set_notification_preference = 0 (not set)
	# requirement: flag_delete = 0 (active)
	#------------------------------------------------#
	$queryLocalUsers = "
		SELECT *
		FROM `dashboard_users`
		WHERE
			`flag_is_set_notification_preference` = FALSE
		AND `flag_delete` = FALSE
		ORDER BY `canvas_user_id` ASC;
	";
	$resultsLocalUsers = mysqli_query($connString, $queryLocalUsers) or
	die(mysqli_error($connString));

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsLocalUsers)) {
		array_push($arrayLocalUsers, $usr);
	}
	if ($debug) {
		echo "<hr/>arrayLocalUsers:<br />";
		echo "(example: arrayLocalUsers[0][\"canvas_user_id\"] is: " . $arrayLocalUsers[0]["canvas_user_id"] . ")<br />";
		util_prePrintR($arrayLocalUsers);
		echo "<hr/>";
	}

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
		$arrayCurlResult = curlSetUserNotificationPreferences(
			$local_usr["canvas_user_id"],
			$local_usr["username"],
			$apiPathPrefix = "api/v1/users/self/communication_channels/email/",
			$apiPathEndpoint = "/notification_preferences?as_user_id="
		);

		if ($debug) {
			echo "<hr/>";
			util_prePrintR($arrayCurlResult);
		}

		# increment counter
		$intCountCurlAPIRequests += 1;

		// check for curl error
		foreach ($arrayCurlResult as $item => $value) {
			if ($item == "errors" || $item == "unauthorized") {
				$boolValidResult = FALSE;
			}
		}

		if ($boolValidResult) {
			#------------------------------------------------#
			# SQL Purpose: Curl was successful. Update Dashboard local db to reflect this action has been completed
			# requirement: `flag_is_set_notification_preference` = 1 (set)
			#------------------------------------------------#

			$queryEditLocalUser = "
				UPDATE
					`dashboard_users`
				SET
					`flag_is_set_notification_preference` = TRUE
				WHERE
					`canvas_user_id` = " . $local_usr["canvas_user_id"] . "
			";

			if ($debug) {
				echo "<pre>queryEditLocalUser = " . $queryEditLocalUser . "</pre>";
			}
			else {
				$resultsEditLocalUser = mysqli_query($connString, $queryEditLocalUser) or
				die(mysqli_error($connString));
			}

			# increment counter
			$intCountEdits += 1;

			# Store list
			$strUIDsUpdated .= empty($strUIDsUpdated) ? $local_usr["canvas_user_id"] : ", " . $local_usr["canvas_user_id"];

			# Output to browser and txt file
			if ($debug) {
				echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Updated notification preferences (updated Canvas)<br />";
			}
			fwrite($myLogFile, $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Updated notification preferences (updated Canvas)\n");
		}
		else {
			# note: any non-williams primary_email address values will fail; there are very few of these.
			# consider doing a curl call to get profile, capture primary_email, then re-do the above curl PUT but to the non-williams email address

			# increment counter
			$intCountSkips += 1;
			$intCountErrors += 1;

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

	# store values
	$endDateTime         = date('YmdHis');
	$endDateTimePretty   = date('Y-m-d H:i:s');
	$intCountNeedsUpdate = count($arrayLocalUsers);

	$finalReport = array();
	array_push($finalReport, "Date begin: " . $beginDateTimePretty);
	array_push($finalReport, "Date end: " . $endDateTimePretty);
	array_push($finalReport, "Duration: " . convertSecondsToHMSFormat(strtotime($endDateTime) - strtotime($beginDateTime)) . " (hh:mm:ss)");
	array_push($finalReport, "Curl API Requests: " . $intCountCurlAPIRequests);
	array_push($finalReport, "Count: Canvas users needing updates: " . $intCountNeedsUpdate);
	array_push($finalReport, "Count: Canvas users updated: " . $intCountEdits);
	array_push($finalReport, "Count: Canvas users skipped due to errors: " . $intCountSkips);
	array_push($finalReport, "List Canvas UIDs: Updated preferences: " . $strUIDsUpdated);
	array_push($finalReport, "Archived file: " . $str_log_path_simple);
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
		$str_action_path_simple = '/app_code/' . basename($_SERVER['PHP_SELF']);
		$flag_is_cron_job       = 0; // FALSE
	}
	else {
		// script ran via server commandline, not as web application
		$str_action_path_simple = '/app_code/' . basename(__FILE__);
		$flag_is_cron_job       = 1; // TRUE
	}

	$str_event_dataset_brief = $intCountEdits . " updates, " . $intCountSkips . " skips";

	create_eventlog(
		$connString,
		$debug,
		mysqli_real_escape_string($connString, $str_event_action),
		mysqli_real_escape_string($connString, $str_log_path_simple),
		mysqli_real_escape_string($connString, $str_action_path_simple),
		$intCountNeedsUpdate,
		$intCountAdds,
		$intCountEdits,
		$intCountRemoves,
		$intCountSkips,
		$intCountErrors,
		mysqli_real_escape_string($connString, $str_event_dataset_brief),
		mysqli_real_escape_string($connString, $str_event_dataset_full),
		$flag_success = ($intCountErrors == 0) ? 1 : 0,
		$flag_is_cron_job
	);

	// final script status
	echo "done!";

	#------------------------------------------------#
	# End: Avoid hitting the default script timeout of 300 or 720 seconds (depending on default php.ini settings)
	#------------------------------------------------#
	ob_end_flush();
