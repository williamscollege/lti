<?php
	/***********************************************
	 ** Project:    Auto Enrollments: Canvas Course OC (do both adds and drops)
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Daily add/drop all entering/leaving faculty status employees into one specific Canvas course
	 ** Requirements:
	 **  - Requires admin token to make curl requests against Canvas LMS API
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down parent releases folder to only allow administrator to access/view/run files
	 **  - delay execution to not exceed Canvas limit of 3000 API requests per hour (http://www.instructure.com/policies/api-policy)
	 **  - extend the typical "max_execution_time" to require as much time as the script requires (without timing out)
	 **  - Run daily using cron job
	 ** Current features:
	 **  - enroll users who are faculty members to course "Open Classroom"
	 **  - remove users who no longer are faculty members from course "Open Classroom"
	 **  - maintain updated Dashboard records of who are teachers and members of the above course
	 **        by doing diff of current list of faculty (`dashboard_faculty_current`) vs users listed as teachers (`dashboard_users`)
	 **  - send mail: for admins, send list of course adds and drops
	 **  - send mail: for newly added faculty, send brief introduction and explanation of course
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
	# BEFORE RUNNING SCRIPT FOR THE VERY FIRST TIME, get list of already enrolled faculty in course,
	# and set those user flags as enrolled in course (that will avoid sending them unnecessary emails)
	$debug = FALSE;

	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name          = "Auto Enrollments: Canvas Course OC";
	$str_event_action          = "auto_enroll_canvas_course_oc";
	$intCourseID               = 1434076;
	$intSectionID              = 1642651;
	$strCourseTitle            = "Open Classroom";
	# NOTE: if updating primary contacts: update Canvas User ID in array at top of file AND text message at bottom of file
	$arrayNotifyAdminIDs       = [3755519, 2369101, 5086658]; // canvas_user_id: David Keiser-Clark, Adam Wang, Sarah Goh
	$arrayNotifyAdminUserNames = [];
	$arrayEnrollments          = [];
	$arrayDrops                = [];
	$boolValidResult           = TRUE;
	$strUIDsEnrolled           = "";
	$strUIDsDropped            = "";
	$intCountCurlAPIRequests   = 0;
	$intCountFacultyCurrent    = 0;
	$intCountAdds              = 0;
	$intCountEdits             = 0;
	$intCountRemoves           = 0;
	$intCountSkips             = 0;
	$intCountErrors            = 0;
	$strAdminEmails            = "";
	$strEnrolledEmails         = "";
	$strEnrollments            = "";
	$strDrops                  = "";
	$strErrors                 = "";

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


	#------------------------------------------------#
	# SQL Purpose: Fetch email usernames of admins who should be notified
	#------------------------------------------------#
	$queryNotifyAdmins = "
		SELECT usr.username
		FROM `dashboard_users` as usr
		WHERE
			usr.canvas_user_id IN (" . implode(',', $arrayNotifyAdminIDs) . ")
		ORDER BY usr.username;
	";
	if ($debug) {
		echo "<pre>queryNotifyAdmins = " . $queryNotifyAdmins . "</pre>";
	}
	$resultsNotifyAdmins = mysqli_query($connString, $queryNotifyAdmins) or
	die(mysqli_error($connString));

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsNotifyAdmins)) {
		array_push($arrayNotifyAdminUserNames, $usr);
	}
	if ($debug) {
		echo "<hr />arrayNotifyAdmins:<br />";
		echo "(example: arrayNotifyAdmins[0][\"username\"] is: " . $arrayNotifyAdminUserNames[0]["username"] . ")<br />";
		util_prePrintR($arrayNotifyAdminUserNames);
	}


	#------------------------------------------------#
	# SQL Purpose: Fetch users to enroll into course
	# Condition: Must be listed as teacher in `dashboard_faculty_current`
	# Condition: Must exist in `dashboard_users` as not yet enrolled in course
	#------------------------------------------------#
	$queryEnrollments = "
		SELECT usr.*
		FROM `dashboard_users` as usr
		INNER JOIN `dashboard_faculty_current` as fac_cur
		ON usr.sis_user_id = fac_cur.wms_user_id
		WHERE
			usr.flag_is_enrolled_course_oc = 0
		ORDER BY usr.sortable_name ASC;
	";
	if ($debug) {
		echo "<hr /><pre>queryEnrollments = " . $queryEnrollments . "</pre>";
	}
	$resultsEnrollments = mysqli_query($connString, $queryEnrollments) or
	die(mysqli_error($connString));

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsEnrollments)) {
		array_push($arrayEnrollments, $usr);
	}
	if ($debug) {
		echo "<hr />arrayEnrollments:<br />";
		echo "(example: arrayEnrollments[0][\"sis_user_id\"] is: " . $arrayEnrollments[0]["sis_user_id"] . ")<br />";
		util_prePrintR($arrayEnrollments);
	}


	#------------------------------------------------#
	# SQL Purpose: Fetch users to drop from course
	# Condition: Teacher does not exist in `dashboard_faculty_current`
	# Condition: User exists in `dashboard_users` and is listed as a teacher
	#------------------------------------------------#
	$queryDrops = "
		SELECT usr.*
		FROM `dashboard_users` AS usr
		LEFT JOIN `dashboard_faculty_current` AS fac_cur
		ON usr.sis_user_id = fac_cur.wms_user_id
		WHERE
			usr.flag_is_teacher = 1
			AND fac_cur.wms_user_id IS NULL
		ORDER BY usr.sortable_name ASC;
	";
	if ($debug) {
		echo "<hr /><pre>queryDrops = " . $queryDrops . "</pre>";
	}
	else {
		$resultsDrops = mysqli_query($connString, $queryDrops) or
		die(mysqli_error($connString));
	}

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsDrops)) {
		array_push($arrayDrops, $usr);
	}
	if ($debug) {
		echo "<hr />arrayDrops:<br />";
		echo "(example: arrayDrops[0][\"sis_user_id\"] is: " . $arrayDrops[0]["sis_user_id"] . ")<br />";
		util_prePrintR($arrayDrops);
	}


	#------------------------------------------------#
	# SQL Purpose: Fetch simple count of faculty in `dashboard_faculty_current`
	#------------------------------------------------#
	$queryItems = "
		SELECT fac_cur.*
		FROM `dashboard_faculty_current` AS fac_cur;
	";
	if ($debug) {
		echo "<hr /><pre>queryItems = " . $queryItems . "</pre>";
	}
	$resultsItems = mysqli_query($connString, $queryItems) or
	die(mysqli_error($connString));

	# Store value
	$intCountFacultyCurrent = mysqli_num_rows($resultsItems);
	if ($debug) {
		echo "<hr />intCountFacultyCurrent = " . $intCountFacultyCurrent . "<br />";
	}


	#------------------------------------------------#
	# Iterate Enrollments: add each user to course with curl
	#------------------------------------------------#
	foreach ($arrayEnrollments as $usr) {

		// reset flag for each user
		$boolValidResult = TRUE;

		# Auto enroll user into Canvas course section using curl call
		$arrayCurlResult = curlEnrollUserInCourse(
			$intCourseID,
			$intSectionID,
			$userID = $usr["canvas_user_id"],
			$type = "StudentEnrollment",
			$enrollment_state = "active",
			$limit_privileges_to_course_section = "true",
			$notify = "false",
			$apiPathPrefix = "api/v1/courses/",
			$apiPathEndpoint = "/enrollments"
		);

		if ($debug) {
			echo "<hr />curlEnrollUserInCourse results = ";
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
			# SQL Purpose: This user is a teacher: add teacher status and show is enrolled in course
			# Curl was successful. Update Dashboard db to reflect this action has been completed
			#------------------------------------------------#

			$queryEditUser = "
				UPDATE
					`dashboard_users`
				SET
					`flag_is_teacher` = TRUE,
					`flag_is_enrolled_course_oc` = TRUE
				WHERE
					`canvas_user_id` = " . $usr["canvas_user_id"] . "
			";

			if ($debug) {
				echo "<pre>queryEditUser = " . $queryEditUser . "</pre>";
			}
			else {
				$resultsEditUser = mysqli_query($connString, $queryEditUser) or
				die(mysqli_error($connString));
			}

			# increment counter
			$intCountAdds += 1;

			# Output to browser and txt file
			if ($debug) {
				echo $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Enrolled user into Open Classroom (OC) course (updated Canvas)<br />";
			}

			# Store list
			$strUIDsEnrolled .= empty($strUIDsEnrolled) ? $usr["canvas_user_id"] : ", " . $usr["canvas_user_id"];
			$strEnrollments .= $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Enrolled user into Open Classroom (OC) course (updated Canvas)\n";
			fwrite($myLogFile, $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Enrolled user into Open Classroom (OC) course (updated Canvas)\n");
		}
		else {
			# increment counter
			$intCountSkips += 1;
			$intCountErrors += 1;

			# Output to browser and txt file
			if ($debug) {
				echo $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to enroll this user into Open Classroom (OC) course (unable to update Canvas)<br />";
			}
			$strErrors .= $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to enroll this user into Open Classroom (OC) course (unable to update Canvas)\n";
			fwrite($myLogFile, $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to enroll this user into Open Classroom (OC) course (unable to update Canvas)\n");
		}
	}

	# formatting (last iteration)
	if ($debug) {
		echo "<hr />";
	}
	fwrite($myLogFile, "\n------------------------------\n\n");


	#------------------------------------------------#
	# Iterate Drops: add each user to course with curl
	#------------------------------------------------#
	foreach ($arrayDrops as $usr) {

		// reset flag for each user
		$boolValidResult = TRUE;

		# Fetch user enrollment_id from Canvas (filter by user_id, role, state) using curl call
		$arrayCurlResult = curlFetchUserEnrollmentID(
			$intSectionID,
			$userID = $usr["canvas_user_id"],
			$type = "StudentEnrollment",
			$role = "StudentEnrollment",
			$apiPathPrefix = "api/v1/sections/",
			$apiPathEndpoint = "/enrollments"
		);

		if ($debug) {
			echo "<hr />curlFetchUserEnrollmentID results = ";
			util_prePrintR($arrayCurlResult);
		}

		# increment counter
		$intCountCurlAPIRequests += 1;

		// check for curl error (must look inside the nested array)
		foreach ($arrayCurlResult as $item => $value) {
			foreach ($itm as $val) {
				if ($itm == "errors") {
					print_r($itm);
					echo "<br />val = " . $val . "<br />";
					$boolValidResult = FALSE;
				}
			}
		}

		if ($boolValidResult) {
			# fetch enrollment_id from returned curl output
			$intEnrollmentID = $arrayCurlResult[0]["id"]; // tease the id value out of array

			if ($debug) {
				echo "<br />intEnrollmentID = " . $intEnrollmentID . "<br />";
			}

			# Auto drop user into Canvas course section using curl call
			$arrayCurlResult = curlDropUserFromCourse(
				$intCourseID,
				$intEnrollmentID,
				$task = "delete",
				$apiPathPrefix = "api/v1/courses/",
				$apiPathEndpoint = "/enrollments/"
			);

			if ($debug) {
				echo "<hr />curlDropUserFromCourse results = ";
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
				# SQL Purpose: This user is no longer a teacher: remove teacher status and show is not in course
				# Curl was successful. Update Dashboard db to reflect this action has been completed
				#------------------------------------------------#

				$queryEditUser = "
					UPDATE
						`dashboard_users`
					SET
						`flag_is_teacher` = FALSE,
						`flag_is_enrolled_course_oc` = FALSE
					WHERE
						`canvas_user_id` = " . $usr["canvas_user_id"] . "
				";

				if ($debug) {
					echo "<pre>queryEditUser = " . $queryEditUser . "</pre>";
				}
				else {
					$resultsEditUser = mysqli_query($connString, $queryEditUser) or
					die(mysqli_error($connString));
				}

				# increment counter
				$intCountRemoves += 1;

				# Output to browser and txt file
				if ($debug) {
					echo $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Dropped user from Open Classroom (OC) course (updated Canvas)<br />";
				}

				# Store list
				$strUIDsDropped .= empty($strUIDsDropped) ? $usr["canvas_user_id"] : ", " . $usr["canvas_user_id"];
				$strDrops .= $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Dropped user from Open Classroom (OC) course (updated Canvas)\n";
				fwrite($myLogFile, $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Dropped user from Open Classroom (OC) course (updated Canvas)\n");
			}
			else {
				# increment counter
				$intCountSkips += 1;
				$intCountErrors += 1;

				# Output to browser and txt file
				if ($debug) {
					echo $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to drop this user from Open Classroom (OC) course (unable to update Canvas)<br />";
				}
				$strErrors .= $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to drop this user from Open Classroom (OC) course (unable to update Canvas)\n";
				fwrite($myLogFile, $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to drop this user from Open Classroom (OC) course (unable to update Canvas)\n");
			}
		}
		else {
			# increment counter
			$intCountSkips += 1;
			$intCountErrors += 1;

			# Output to browser and txt file
			if ($debug) {
				echo $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to fetch enrollment_id and drop this user from Open Classroom (OC) course (unable to update Canvas)<br />";
			}
			$strErrors .= $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to fetch enrollment_id and drop this user from Open Classroom (OC) course (unable to update Canvas)\n";
			fwrite($myLogFile, $usr["canvas_user_id"] . " - " . $usr["sortable_name"] . " - Skipped: curl failed to fetch enrollment_id and drop this user from Open Classroom (OC) course (unable to update Canvas)\n");
		}
	}

	# formatting (last iteration)
	if ($debug) {
		echo "<hr />";
	}
	fwrite($myLogFile, "\n------------------------------\n\n");


	#------------------------------------------------#
	# prepare values for eventlog (and also for notifications, if enrollments or drops exist)
	#------------------------------------------------#
	$str_event_dataset_brief = $intCountAdds . " enrolls, " . $intCountRemoves . " drops, " . $intCountErrors . " errors";
	$str_event_dataset_full  = "<strong>Date completed: " . $beginDateTimePretty . "</strong><br />";

	// prettify for output
	$strEnrollments = empty($strEnrollments) ? "none\n" : $strEnrollments;
	$strDrops       = empty($strDrops) ? "none\n" : $strDrops;
	$strErrors      = empty($strErrors) ? "none\n" : $strErrors;


	#------------------------------------------------#
	# for admins: notification of changes
	#------------------------------------------------#
	if ($intCountAdds >= 1 || $intCountRemoves >= 1 || $intCountErrors >= 1) {

		// configure mail settings (if multiple recipients: separate with commas, avoid spaces)
		$subject = "Dashboard Auto Enroll (OC): " . $str_event_dataset_brief . " (\"$str_event_action\")";
		$message = "Application: " . LTI_APP_NAME . "\nScript: $str_project_name (\"$str_event_action\")\n\nFaculty enrolled:\n" . $strEnrollments . "\nFaculty dropped:\n" . $strDrops . "\nErrors (skipped users):\n" . $strErrors . "\nMore information:\n" . APP_FOLDER;
		$headers = "From: dashboard-no-reply@williams.edu" . "\r\n" .
			"Reply-To: dashboard-no-reply@williams.edu" . "\r\n" .
			"X-Mailer: PHP/" . phpversion();

		// iterate array
		foreach ($arrayNotifyAdminUserNames as $admin) {
			$to = (stripos($admin["username"], "@")) ? $admin["username"] : $admin["username"] . "@williams.edu";

			if ($debug) {
				$strAdminEmails .= $to . ","; // create list for debug
			}
			else {
				// send mail: for admins, send list of course adds and drops
				mail($to, $subject, $message, $headers);
				sleep(2); // delay execution (prevent overwhelming mail fxn)
			}
		}

		if ($debug) {
			echo "<hr />To (all recipients) = " . $strAdminEmails . "<br />";
			echo "<hr />Subject = " . $subject . "<br />";
			echo "<hr />Message = " . $message . "<br />";
			echo "<hr />Headers = " . $headers . "<br />";
		}
	}

	#------------------------------------------------#
	# for faculty: introductory email
	#------------------------------------------------#
	if ($intCountAdds >= 1) {

		// configure mail settings (if multiple recipients: separate with commas, avoid spaces)
		$subject = "Glow Resource: " . $strCourseTitle;
		$message = "You have been invited to join the Glow course:\n\"" . $strCourseTitle . "\"\n\nYou may accept this enrollment within Glow:\nhttps://glow.williams.edu/\n\nGuidelines:\nWelcome to the NFD Open Classroom, an initiative that invites Williams College faculty members at any rank to visit a variety of classrooms on campus. A number of generous colleagues have made their courses available to us and there are ten different pedagogical settings from which to choose. Whether you are new to the college, about to teach in an unfamiliar classroom setting, looking to expand your horizons as an instructor, or simply curious about the myriad approaches to teaching on our campus, feel free to browse the list of options, consult the course syllabi, and to use the Google sign-up sheet to reserve any slots that suit your schedule.\n\nQuestions?\nIf you have any questions about this opportunity, please contact Adam Wang of OIT (jwang@williams.edu, x4534) or Sarah Goh (sgoh@williams.edu, x4223).";
		# NOTE: if updating primary contacts: update Canvas User ID in array at top of file AND text message at bottom of file
		$headers = "From: glow-no-reply@williams.edu" . "\r\n" .
			"Reply-To: glow-no-reply@williams.edu" . "\r\n" .
			"X-Mailer: PHP/" . phpversion();

		// iterate array
		foreach ($arrayEnrollments as $usr) {
			$to = (stripos($usr["username"], "@")) ? $usr["username"] : $usr["username"] . "@williams.edu";

			if ($debug) {
				$strEnrolledEmails .= $to . ","; // create list for debug
			}
			else {
				// send mail: for newly added faculty, send brief introduction and explanation of course
				mail($to, $subject, $message, $headers);
				sleep(2); // delay execution (prevent overwhelming mail fxn)
			}
		}

		if ($debug) {
			echo "<hr />To (all recipients) = " . $strEnrolledEmails . "<br />";
			echo "<hr />Subject = " . $subject . "<br />";
			echo "<hr />Message = " . $message . "<br />";
			echo "<hr />Headers = " . $headers . "<br />";
		}
		else {
			// quality assurance: send email to developer (remove when satisfied)
			$to = "dwk2@williams.edu,cph2@williams.edu";
			mail($to, $subject, $message, $headers);
			sleep(2); // delay execution (prevent overwhelming mail fxn)
		}
	}


	#------------------------------------------------#
	# Report: LOG SUMMARY
	#------------------------------------------------#
	// formatting
	if ($debug) {
		echo "<br /><hr />";
	}

	# store values
	$endDateTime       = date('YmdHis');
	$endDateTimePretty = date('Y-m-d H:i:s');

	$finalReport = array();
	array_push($finalReport, "Date begin: " . $beginDateTimePretty);
	array_push($finalReport, "Date end: " . $endDateTimePretty);
	array_push($finalReport, "Duration: " . convertSecondsToHMSFormat(strtotime($endDateTime) - strtotime($beginDateTime)) . " (hh:mm:ss)");
	array_push($finalReport, "Curl API Requests: " . $intCountCurlAPIRequests);
	array_push($finalReport, "Count: Faculty enrolled in OC: " . $intCountAdds);
	array_push($finalReport, "Count: Faculty dropped from OC: " . $intCountRemoves);
	array_push($finalReport, "Count: Faculty skipped due to errors: " . $intCountErrors);
	array_push($finalReport, "List Canvas UIDs: Faculty enrolled: " . $strUIDsEnrolled);
	array_push($finalReport, "List Canvas UIDs: Faculty dropped: " . $strUIDsDropped);
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
	create_eventlog(
		$connString,
		$debug,
		mysqli_real_escape_string($connString, $str_event_action),
		mysqli_real_escape_string($connString, $str_log_path_simple),
		mysqli_real_escape_string($connString, $str_action_path_simple),
		$intCountFacultyCurrent,
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
