<?php
	/***********************************************
	 ** Project:    Sync Canvas Users to Dashboard
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Regularly Sync Canvas Users (Amazon AWS) to Dashboard to create local database of users to make other operations more convenient
	 ** Requirements:
	 **  - Requires admin token to make curl requests against Canvas LMS API
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down folder contain these scripts to prevent any non-Williams admin from accessing files
	 **  - delay execution to not exceed Canvas limit of 3000 API requests per hour (http://www.instructure.com/policies/api-policy)
	 **  - extend the typical "max_execution_time" to require as much time as the script requires (without timing out)
	 **  - Run daily using cron job
	 ** Current features:
	 **  - fetch all Canvas user accounts using paged curl calls
	 **  - fetch all local Dashboard users
	 **  - compare Canvas to Dashboard and sync live users to local (insert, update, or skip)
	 **  - compare Dashboard to Canvas and sync live users to local (remove only)
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
	$str_project_name        = "Sync Canvas Users to Dashboard";
	$str_event_action        = "sync_canvas_users_to_dashboard";
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
	$str_log_file = date("Ymd-His") . "-log-report.txt";
	$str_log_path_simple = '/logs/' . $str_log_file;
	$str_log_path_full = dirname(__FILE__) . '/../logs/' . $str_log_file;
	$myLogFile = fopen($str_log_path_full, "w") or die("Unable to open file!");

	// set values dynamically
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		// script ran as web application
		$str_action_path_simple = '/app_code/' . basename($_SERVER['PHP_SELF']);
		$flag_is_cron_job     = 0; // FALSE
	}
	else {
		// script ran via server commandline, not as web application
		$str_action_path_simple = '/app_code/' . basename(__FILE__);
		$flag_is_cron_job     = 1; // TRUE
	}

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
		$canvas_u_id            = empty($canvas_usr["id"]) ? 0 : $canvas_usr["id"];
		$canvas_u_name          = empty($canvas_usr["name"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["name"]);
		$canvas_u_sortable_name = empty($canvas_usr["sortable_name"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["sortable_name"]);
		$canvas_u_short_name    = empty($canvas_usr["short_name"]) ? '' : mysqli_real_escape_string($connString, $canvas_usr["short_name"]);
		// check for undefined index and convert to integer before checking is_numeric (avoids PHP warnings when running via commandline)
		$canvas_u_sis_user_id = empty($canvas_usr["sis_user_id"]) ? 0 : $canvas_usr["sis_user_id"];
		if (!is_numeric($canvas_u_sis_user_id)) {
			$canvas_u_sis_user_id = 0;
		}
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
				// convert possible null values to empty string value to enable later string comparisons
				$local_usr_sis_login_id = empty($local_usr["sis_login_id"]) ? '' : mysqli_real_escape_string($connString, $local_usr["sis_login_id"]);

				if (
					$local_usr["canvas_user_id"] != $canvas_u_id
					|| $local_usr["name"] != $canvas_usr["name"]
					|| $local_usr["sortable_name"] != $canvas_usr["sortable_name"]
					|| $local_usr["short_name"] != $canvas_usr["short_name"]
					|| $local_usr["sis_user_id"] != $canvas_u_sis_user_id
					// || $local_usr["integration_id"] != $canvas_u_integration_id // ignore if changes exist
					|| $local_usr_sis_login_id != $canvas_u_sis_login_id
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
					if ($debug) {
						echo $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Updated local user (synced newer Canvas to local)<br />";
					}
					fwrite($myLogFile, $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Updated local user (synced newer Canvas to local)\n");
				}
				else {
					# increment counter
					$intCountUsersSkipped += 1;

					# Output to browser and txt file
					// decided to omit skipped output, as there is no need to fill log files daily with 500kb of skipped user info
					// if ($debug) {
					// echo $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Skipped User (Canvas matches local)<br />";
					// }
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
			if ($debug) {
				echo $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Inserted local user (synced newer Canvas to local)<br />";
			}
			fwrite($myLogFile, $canvas_u_id . " - " . $canvas_usr["sortable_name"] . " - Inserted local user (synced newer Canvas to local)\n");
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
	if ($debug) {
		echo "<hr />";
	}
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
			if ($debug) {
				echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Removed local user (synced newer Canvas to local)<br />";
			}
			fwrite($myLogFile, $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Removed local user (synced newer Canvas to local)\n");
		}
	}


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
	array_push($finalReport, "Count: Canvas LMS Users: " . $intCountUsersCanvas);
	array_push($finalReport, "Count: Users Inserted in Dashboard: " . $intCountUsersInserted);
	array_push($finalReport, "Count: Users Updated in Dashboard: " . $intCountUsersUpdated);
	array_push($finalReport, "Count: Users Skipped in Dashboard: " . $intCountUsersSkipped);
	array_push($finalReport, "Count: Users Removed in Dashboard: " . $intCountUsersRemoved);
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
	// create value
	$str_event_dataset_brief = $intCountUsersInserted . " inserts, " . $intCountUsersUpdated . " updates, " . $intCountUsersRemoved . " deletes";

	$flag_success = 0; // FALSE
	if ($intCountUsersCanvas > 0) {
		$flag_success = 1; // TRUE
	}

	create_eventlog(
		$connString,
		$debug,
		mysqli_real_escape_string($connString, $str_event_action),
		mysqli_real_escape_string($connString, $str_log_path_simple),
		mysqli_real_escape_string($connString, $str_action_path_simple),
		count($arrayCanvasUsers),
		($intCountUsersUpdated + $intCountUsersInserted + $intCountUsersRemoved),
		$intCountUsersErrors,
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
