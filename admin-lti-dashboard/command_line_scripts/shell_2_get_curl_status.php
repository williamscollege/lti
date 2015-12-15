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

	#------------------------------------------------#
	# Fetch commandline argument passed from shell script
	#------------------------------------------------#
	if (isset($argv)) {
		$curl_response = $argv[1];
	}

	if (!isset($argv) || !$argv[1]) {
		// argument failed to be passed. log error.
		create_eventlog(
			$connString,
			$debug,
			$str_event_action = "error_commandline_stage_2",
			$str_log_file_path = "n/a",
			$str_action_file_path = "shell_2_get_curl_status.php",
			$items = 0,
			$changes = 0,
			$errors = 1,
			$str_event_dataset_brief = "argument failed to pass correctly from from shell script",
			$str_event_dataset_full = "argument failed to pass correctly from from shell script",
			$flag_success = 0,
			$flag_is_cron_job = 0);
		exit;
	}

	if ($debug) {
		// to use this as a test in place of argument, must also comment out "exit" statement above...
		$curl_response = '{"created_at":"2015-12-07T18:55:05Z","started_at":"2015-12-07T18:55:16Z","ended_at":"2015-12-07T19:09:32Z","updated_at":"2015-12-07T19:09:32Z","progress":100,"id":9138772,"workflow_state":"imported_with_messages","data":{"import_type":"instructure_csv","supplied_batches":["term","course","section","user","enrollment"],"counts":{"accounts":0,"terms":19,"abstract_courses":0,"courses":1534,"sections":1534,"xlists":0,"users":4372,"enrollments":12884,"groups":0,"group_memberships":0,"grade_publishing_results":0}},"batch_mode":null,"batch_mode_term_id":null,"override_sis_stickiness":null,"add_sis_stickiness":null,"clear_sis_stickiness":null,"diffing_data_set_identifier":null,"diffed_against_import_id":null,"processing_warnings":[["users_20151207-135501.csv","user 1683415 has already claimed 1117865\'s requested login information, skipping"],["enrollments_20151207-135501.csv","User 1130947 didn\'t exist for user enrollment"]]}';
		util_prePrintR($curl_response);
		echo "<hr />";
	}

	// put returned json into object
	$obj_curl_response = json_decode($curl_response);
	if ($debug) {
		util_prePrintR($obj_curl_response);
	}

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

	if ($debug) {
		// some output
		echo "import_type = " . $obj_data_import_type . "<br />";
		echo "created_at=" . $obj_created_at . "<br />";
		echo "started_at=" . $obj_started_at . "<br />";
		echo "ended_at=" . $obj_ended_at . "<br />";
		echo "updated_at=" . $obj_updated_at . "<br />";
		echo "data_supplied_batches= " . $obj_data_supplied_batches . "<br />";
		echo "enrollments= " . $obj_data_counts_enrollments . "<br />";
		echo "processing_warnings= " . $obj_processing_warnings . "<br />";
	}


	#------------------------------------------------#
	# SQL: UPDATE to reflect retrieval of Curl final response
	#------------------------------------------------#
	$queryEditRawData = "
		UPDATE
			`dashboard_sis_imports_raw`
		SET
			`ended_at` = '" . mysqli_real_escape_string($connString, $obj_ended_at) . "'
			,`curl_final_import_status` = '" . mysqli_real_escape_string($connString, $curl_response) . "'
		WHERE
			`curl_import_id` = " . $obj_id . "
	";

	if ($debug) {
		echo "<pre>queryEditRawData = " . $queryEditRawData . "</pre>";
	}
	else {
		$resultsEditRawData = mysqli_query($connString, $queryEditRawData) or
		die(mysqli_error($connString));
	}

	#------------------------------------------------#
	# SQL: Check if this `curl_import_id` already exists
	#------------------------------------------------#
	$queryCheckExists = "
		SELECT * FROM `dashboard_sis_imports_parsed` WHERE `id` = " . $obj_id . ";
	";
	$resultsCheckExists = mysqli_query($connString, $queryCheckExists) or
	die(mysqli_error($connString));

	$check_existence = mysqli_num_rows($resultsCheckExists);

	if ($check_existence) {
		#------------------------------------------------#
		# SQL: UPDATE existing record using captured data
		#------------------------------------------------#
		$queryEditData = "
			UPDATE
				`dashboard_sis_imports_parsed`
			SET
				`cronjob_datetime` = now()
				,`created_at` = '" . mysqli_real_escape_string($connString, $obj_created_at) . "'
				,`started_at` = '" . mysqli_real_escape_string($connString, $obj_started_at) . "'
				,`ended_at` = '" . mysqli_real_escape_string($connString, $obj_ended_at) . "'
				,`updated_at` = '" . mysqli_real_escape_string($connString, $obj_updated_at) . "'
				,`progress` = '" . mysqli_real_escape_string($connString, $obj_progress) . "'
				,`id` = '" . mysqli_real_escape_string($connString, $obj_id) . "'
				,`workflow_state` = '" . mysqli_real_escape_string($connString, $obj_workflow_state) . "'
				,`data_import_type` = '" . mysqli_real_escape_string($connString, $obj_data_import_type) . "'
				,`data_supplied_batches` = '" . mysqli_real_escape_string($connString, $obj_data_supplied_batches) . "'
				,`data_counts_accounts` = '" . mysqli_real_escape_string($connString, $obj_data_counts_accounts) . "'
				,`data_counts_terms` = '" . mysqli_real_escape_string($connString, $obj_data_counts_terms) . "'
				,`data_counts_abstract_courses` = '" . mysqli_real_escape_string($connString, $obj_data_counts_abstract_courses) . "'
				,`data_counts_courses` = '" . mysqli_real_escape_string($connString, $obj_data_counts_courses) . "'
				,`data_counts_sections` = '" . mysqli_real_escape_string($connString, $obj_data_counts_sections) . "'
				,`data_counts_xlists` = '" . mysqli_real_escape_string($connString, $obj_data_counts_xlists) . "'
				,`data_counts_users` = '" . mysqli_real_escape_string($connString, $obj_data_counts_users) . "'
				,`data_counts_enrollments` = '" . mysqli_real_escape_string($connString, $obj_data_counts_enrollments) . "'
				,`data_counts_groups` = '" . mysqli_real_escape_string($connString, $obj_data_counts_groups) . "'
				,`data_counts_group_memberships` = '" . mysqli_real_escape_string($connString, $obj_data_counts_group_memberships) . "'
				,`data_counts_grade_publishing_results` = '" . mysqli_real_escape_string($connString, $obj_data_counts_grade_publishing_results) . "'
				,`batch_mode` = '" . mysqli_real_escape_string($connString, $obj_batch_mode) . "'
				,`batch_mode_term_id` = '" . mysqli_real_escape_string($connString, $obj_batch_mode_term_id) . "'
				,`override_sis_stickiness` = '" . mysqli_real_escape_string($connString, $obj_override_sis_stickiness) . "'
				,`add_sis_stickiness` = '" . mysqli_real_escape_string($connString, $obj_add_sis_stickiness) . "'
				,`clear_sis_stickiness` = '" . mysqli_real_escape_string($connString, $obj_clear_sis_stickiness) . "'
				,`diffing_data_set_identifier` = '" . mysqli_real_escape_string($connString, $obj_diffing_data_set_identifier) . "'
				,`diffed_against_import_id` = '" . mysqli_real_escape_string($connString, $obj_diffed_against_import_id) . "'
				,`processing_warnings` = '" . mysqli_real_escape_string($connString, $obj_processing_warnings) . "'
			WHERE
				`id` = " . $obj_id . "
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
		# SQL: INSERT captured data into `dashboard_sis_imports_parsed`
		#------------------------------------------------#
		$queryCaptureParsedData = "
			INSERT INTO
				`dashboard_sis_imports_parsed`
				(
					`cronjob_datetime`
					,`created_at`
					,`started_at`
					,`ended_at`
					,`updated_at`
					,`progress`
					,`id`
					,`workflow_state`
					,`data_import_type`
					,`data_supplied_batches`
					,`data_counts_accounts`
					,`data_counts_terms`
					,`data_counts_abstract_courses`
					,`data_counts_courses`
					,`data_counts_sections`
					,`data_counts_xlists`
					,`data_counts_users`
					,`data_counts_enrollments`
					,`data_counts_groups`
					,`data_counts_group_memberships`
					,`data_counts_grade_publishing_results`
					,`batch_mode`
					,`batch_mode_term_id`
					,`override_sis_stickiness`
					,`add_sis_stickiness`
					,`clear_sis_stickiness`
					,`diffing_data_set_identifier`
					,`diffed_against_import_id`
					,`processing_warnings`
				)
				VALUES
				(
					now()
					'" . mysqli_real_escape_string($connString, $obj_created_at) . "'
					,'" . mysqli_real_escape_string($connString, $obj_started_at) . "'
					,'" . mysqli_real_escape_string($connString, $obj_ended_at) . "'
					,'" . mysqli_real_escape_string($connString, $obj_updated_at) . "'
					,'" . mysqli_real_escape_string($connString, $obj_progress) . "'
					,'" . mysqli_real_escape_string($connString, $obj_id) . "'
					,'" . mysqli_real_escape_string($connString, $obj_workflow_state) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_import_type) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_supplied_batches) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_accounts) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_terms) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_abstract_courses) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_courses) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_sections) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_xlists) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_users) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_enrollments) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_groups) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_group_memberships) . "'
					,'" . mysqli_real_escape_string($connString, $obj_data_counts_grade_publishing_results) . "'
					,'" . mysqli_real_escape_string($connString, $obj_batch_mode) . "'
					,'" . mysqli_real_escape_string($connString, $obj_batch_mode_term_id) . "'
					,'" . mysqli_real_escape_string($connString, $obj_override_sis_stickiness) . "'
					,'" . mysqli_real_escape_string($connString, $obj_add_sis_stickiness) . "'
					,'" . mysqli_real_escape_string($connString, $obj_clear_sis_stickiness) . "'
					,'" . mysqli_real_escape_string($connString, $obj_diffing_data_set_identifier) . "'
					,'" . mysqli_real_escape_string($connString, $obj_diffed_against_import_id) . "'
					,'" . mysqli_real_escape_string($connString, $obj_processing_warnings) . "'
				)
		";

		if ($debug) {
			echo "<pre>queryCaptureParsedData = " . $queryCaptureParsedData . "</pre>";
		}
		else {
			$resultsCaptureParsedData = mysqli_query($connString, $queryCaptureParsedData) or
			die(mysqli_error($connString));
		}
	}
