<?php

	# Purpose: Command line script to enable Cron jobs to send reminders once daily (this is a system state based cron job, not an event driven job)

	require_once('cl_head.php');

	/*
	 * 1. This script queues emails to all users who have >= 1 signup coming up in the next 2 days.
	 * Only one email is sent to a user, and it contains their own signups organized by date (ascending)
	 * 2. This script queues emails to all admins and managers that have openings with signups coming up in the next 2 days.
	 * Only one email is sent to each admin or manager, and it contains the signups on their openings, grouped by opening, date (ascending), and user last name (ascending)
	 *
	 * NOTE: since the look-ahead is 2 days and this runs 1/day, that means that people get 2 reminders about each signup
	 * TODO: support command line arg for date start (default to today) and range (default to 2 days)
	 */


	#------------------------------------------------#
	# SET VARIABLES
	#------------------------------------------------#
	$cur_date           = date('Y-m-d'); // today
	$lookahead_interval = 2; // Live = 2
	$debug              = 0; // Live = 0, Testing = 1. writes output data to screen.
	$sendToMeOnly       = 0; // Live = 0, Testing = 1. all emails sent ONLY to hardcoded 'dwk2@williams.edu' (for brief live testing)


	function getPrettyDateRanges($opening) {
		$start_dt = new DateTime($opening['begin_datetime']);
		$end_dt   = new DateTime($opening['end_datetime']);

		$time_range_string_pt1_base = $start_dt->format('m/d/Y, g:i');
		$time_range_string_pt1_ap   = '';
		$time_range_string_pt2      = $end_dt->format('g:i A');
		if (($start_dt->format('a') != $end_dt->format('a'))
			|| ($start_dt->format('m/d/Y') != $end_dt->format('m/d/Y'))
		) {
			$time_range_string_pt1_ap = ' ' . $start_dt->format('A');
		}
		if ($start_dt->format('Y') != $end_dt->format('Y')) {
			$time_range_string_pt2 = $end_dt->format('m/d/Y, g:i A');
		}
		elseif ($start_dt->format('m/d/Y') != $end_dt->format('m/d/Y')) {
			$time_range_string_pt2 = $end_dt->format('m/d/Y, g:i A');
		}
		$time_range_string = $time_range_string_pt1_base . $time_range_string_pt1_ap . '-' . $time_range_string_pt2;

		return $time_range_string;
	}

	function getPrettyInfo_Users($udata, $opening) {
		// Opening name, else Sheet name
		$pretty_info_string = (empty($opening['location'])) ? '' : ", at " . $opening['location'];
		$pretty_info_string .= ", for ";
		$pretty_info_string .= (empty($opening['name'])) ? $udata['sheets'][$opening['sheet_id']]['name'] : $opening['name'];

		return $pretty_info_string;
	}

	function getPrettyInfo_Managers($manager_sheet, $opening) {
		$pretty_info_string = (empty($opening['location'])) ? '' : ", at " . $opening['location'];
		$pretty_info_string .= ", for ";
		$pretty_info_string .= (empty($opening['name'])) ? $manager_sheet['name'] : $opening['name'];

		return $pretty_info_string;
	}

	function cmp_date_sort($a, $b) {
		$a = $a['begin_datetime'];
		$b = $b['begin_datetime'];
		// echo $a . "<br />" . $b . "<br />"; //debugging

		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}


	# 1.0 Users: Get all upcoming signups (cur time to cur time + 48 hours)
	#     For each signup, get the sheet, opening, and signup and user info of signup person; sort by sus_openings.begin_datetime ASC
	// TODO - some fields are not needed. cleanup for smaller recordset
	$signups_sql =
		"SELECT
			DISTINCT u.user_id, u.username, u.first_name, u.last_name, u.email
			, signups.signup_id, signups.opening_id, signups.signup_user_id, signups.admin_comment
			, openings.opening_id, openings.sheet_id, openings.begin_datetime, openings.end_datetime, openings.name, openings.description, openings.location
			, sheets.sheet_id, sheets.owner_user_id, sheets.name, sheets.description, sheets.type, sheets.begin_date, sheets.end_date, sheets.flag_alert_owner_imminent, sheets.flag_alert_admin_imminent
		FROM
			sus_signups AS signups
		INNER JOIN
			sus_openings as openings
			ON openings.opening_id = signups.opening_id
		INNER JOIN
			sus_sheets as sheets
			ON sheets.sheet_id = openings.sheet_id
		INNER JOIN
			users as u
			ON u.user_id = signups.signup_user_id
		WHERE
			openings.begin_datetime >= '$cur_date'
			AND openings.begin_datetime <= date_add('$cur_date', INTERVAL $lookahead_interval DAY) -- time interval
			AND signups.flag_delete = 0
			AND openings.flag_delete = 0
			AND sheets.flag_delete = 0
			AND u.flag_delete = 0
			AND u.flag_is_banned = 0
		ORDER BY
			signups.signup_user_id ASC,openings.begin_datetime ASC";

	if ($debug) {
		echo "signups_sql = " . $signups_sql . "\n<hr />\n";
	}

	$signups_stmt = $DB->prepare($signups_sql);
	$signups_stmt->execute();
	Db_Linked::checkStmtError($signups_stmt);


	# 1.1 Build up the users hash - cycle through the user signups data
	/*
	 * user_id :
	 *      user_id
	 *      username
	 *      first name
	 *      last name
	 *      email
	 *      sheets :
	 *            sheet_id
	 *            owner_user_id
	 *            name
	 *            description
	 *            type
	 *            begin_date
	 *            end_date
	 *            flag_alert_owner_imminent
	 *            flag_alert_admin_imminent
	 *      openings :
	 *            opening_id
	 *            sheet_id
	 *            begin_datetime
	 *            end_datetime
	 *            name
	 *            description
	 *            location
	 *      signups :
	 *            signup_id
	 *            opening_id
	 *            signup_user_id
	 *            admin_comment
	 */

	$users_hash = [];
	while ($row = $signups_stmt->fetch(PDO::FETCH_ASSOC)) {
		# initialize the users info data structure if need be
		if (!array_key_exists($row['user_id'], $users_hash)) {
			$users_hash[$row['user_id']] = [
				'user_id'      => $row['user_id']
				, 'username'   => $row['username']
				, 'first_name' => $row['first_name']
				, 'last_name'  => $row['last_name']
				, 'email'      => $row['email']
				, 'sheets'     => []
				, 'openings'   => []
				, 'signups'    => []
			];
		}

		# append the sheet data to the appropriate list in the user structure
		$users_hash[$row['user_id']]['sheets'][$row['sheet_id']] = [
			'sheet_id'                    => $row['sheet_id']
			, 'owner_user_id'             => $row['owner_user_id']
			, 'name'                      => $row['name']
			, 'description'               => $row['description']
			, 'begin_date'                => $row['begin_date']
			, 'end_date'                  => $row['end_date']
			, 'flag_alert_owner_imminent' => $row['flag_alert_owner_imminent']
			, 'flag_alert_admin_imminent' => $row['flag_alert_admin_imminent']
		];

		# append the opening data to the appropriate list in the user structure
		$users_hash[$row['user_id']]['openings'][$row['opening_id']] = [
			'opening_id'       => $row['opening_id']
			, 'sheet_id'       => $row['sheet_id']
			, 'begin_datetime' => $row['begin_datetime']
			, 'end_datetime'   => $row['end_datetime']
			, 'name'           => $row['name']
			, 'description'    => $row['description']
			, 'location'       => $row['location']
		];

		# append the signup data to the appropriate list in the user structure
		$users_hash[$row['user_id']]['signups'][$row['signup_id']] = [
			'signup_id'        => $row['signup_id']
			, 'opening_id'     => $row['opening_id']
			, 'signup_user_id' => $row['signup_user_id']
			, 'admin_comment'  => $row['admin_comment']
		];
	}


	# 1.2 Build and send daily reminder emails to "users"
	#     Cycle through hash ids; build each email from that hash entry; make the email; send it and sleep for a moment to avoid overwhelming the mail server

	$from    = 'signup_sheets-no-reply@' . INSTITUTION_DOMAIN;
	$subject = "[Glow Signup Sheets] Upcoming signups: " . date_format(new DateTime($cur_date), "m/d/Y");
	$headers = "From: $from";

	foreach ($users_hash as $user_key => $udata) {
		$body = "Hi " . $udata['first_name'] . ",";

		# add signups (count of openings equals signups)
		if (count($udata['openings']) > 0) {
			$is_plural = (count($udata['openings']) > 1) ? "s" : "";
			$body .= "\n\nThis is a reminder of your upcoming " . count($udata['openings']) . " signup" . $is_plural . ":\n";
			foreach ($udata['openings'] as $opening) {
				$pretty_date_range = getPrettyDateRanges($opening);
				$pretty_info       = getPrettyInfo_Users($udata, $opening);
				$body .= "\n" . $pretty_date_range . $pretty_info . "\n";
			}
		}

		// now queue the message (TODO - presently not used: $headers)
		// QueuedMessage::factory($db, $user_id, $target, $summary, $body, $opening_id = 0, $sheet_id = 0, $type = 'email' )
		if ($sendToMeOnly) {
			// testing: force all output emails to a single account
			$qm = QueuedMessage::factory($DB, $udata['user_id'], "dwk2@williams.edu", $subject, $body, 0, 0);
		}
		else {
			// normal procedures
			$qm = QueuedMessage::factory($DB, $udata['user_id'], $udata['email'], $subject, $body, 0, 0);
		}
		$qm->updateDb();

		if (!$qm->matchesDb) {
			// create record failed
			$results['notes'] = "database error: could not create queued message for USER daily reminder";
			error_log("QueuedMessage failed to insert db record (email subject: $subject)");
			echo json_encode($results);
			exit;
		}

		if ($debug) {
			echo $body . "<hr />\n";
		}
	}


	# 2.0 Owners and Admins: Get all upcoming signups (cur time to cur time + 48 hours) for Owners and Admins who opted to receive daily reminders
	#     For each user, get user info and sheet flags
	$owners_and_admins_sql =
		"SELECT
			DISTINCT u.user_id, u.username, u.first_name, u.last_name, u.email
			, sheets.sheet_id, sheets.owner_user_id, sheets.name, sheets.description, sheets.type, sheets.begin_date, sheets.end_date, sheets.flag_alert_owner_imminent, sheets.flag_alert_admin_imminent
		FROM
			users as u
		INNER JOIN
			sus_sheets as sheets
			ON u.user_id = sheets.owner_user_id
		WHERE
			sheets.flag_alert_owner_imminent = 1
			AND sheets.flag_delete = 0
			AND u.flag_delete = 0
			AND u.flag_is_banned = 0
		UNION
		SELECT
			DISTINCT u.user_id, u.username, u.first_name, u.last_name, u.email
			, sheets.sheet_id, sheets.owner_user_id, sheets.name, sheets.description, sheets.type, sheets.begin_date, sheets.end_date, sheets.flag_alert_owner_imminent, sheets.flag_alert_admin_imminent
		FROM
			users as u
		INNER JOIN
			sus_access as access
			ON u.username = access.constraint_data
			AND access.type = 'adminbyuser'
		INNER JOIN
			sus_sheets as sheets
			ON access.sheet_id = sheets.sheet_id
			AND sheets.flag_alert_admin_imminent = 1
		WHERE
			sheets.flag_delete = 0
			AND u.flag_delete = 0
			AND u.flag_is_banned = 0";

	if ($debug) {
		echo "owners_and_admins_sql = " . $owners_and_admins_sql . "\n<hr />\n";
	}

	$owners_and_admins_stmt = $DB->prepare($owners_and_admins_sql);
	$owners_and_admins_stmt->execute();
	Db_Linked::checkStmtError($owners_and_admins_stmt);


	# 2.1 Build up the owners_and_admins info hash - cycle through the owners_and_admins data
	/*
	 * user_id :
	 *      user_id
	 *      username
	 *      first name
	 *      last name
	 *      email
	 *      sheets :
	 *            sheet_id
	 *            owner_user_id
	 *            name
	 *            description
 	 *            type
	 *            begin_date
	 *            end_date
	 *            flag_alert_owner_imminent
	 *            flag_alert_admin_imminent
	 */

	$owners_and_admins_hash = [];
	while ($row = $owners_and_admins_stmt->fetch(PDO::FETCH_ASSOC)) {
		# add each distinct user to the owners_and_admins data structure
		if (!array_key_exists($row['user_id'], $owners_and_admins_hash)) {
			$owners_and_admins_hash[$row['user_id']] = [
				'user_id'      => $row['user_id']
				, 'username'   => $row['username']
				, 'first_name' => $row['first_name']
				, 'last_name'  => $row['last_name']
				, 'email'      => $row['email']
				, 'sheets'     => []
			];
		}

		# append the sheet data to the appropriate list in the user structure
		$owners_and_admins_hash[$row['user_id']]['sheets'][$row['sheet_id']] = [
			'sheet_id'                    => $row['sheet_id']
			, 'owner_user_id'             => $row['owner_user_id']
			, 'name'                      => $row['name']
			, 'description'               => $row['description']
			, 'begin_date'                => $row['begin_date']
			, 'end_date'                  => $row['end_date']
			, 'flag_alert_owner_imminent' => $row['flag_alert_owner_imminent']
			, 'flag_alert_admin_imminent' => $row['flag_alert_admin_imminent']
		];
	}


	# 2.2 Reorganize the users_hash such that it is now organized to show signups per opening, per sheet (ie from perspective of "owners_and_admins")
	$reorganized_sheets_hash = [];

	// iterate through each user
	foreach ($users_hash as $user_key => $udata) {

		// iterate through this user's sheets
		foreach ($udata['sheets'] as $udata_sheet_key => $udata_sheet) {
			if (!array_key_exists($udata_sheet_key, $reorganized_sheets_hash)) {
				$reorganized_sheets_hash[$udata_sheet['sheet_id']] = [
					'openings' => []
				];
			}
		}

		// iterate through this user's openings
		foreach ($udata['openings'] as $udata_opening_key => $udata_opening) {
			if (!array_key_exists($udata_opening_key, $reorganized_sheets_hash[$udata_opening['sheet_id']]['openings'])) {
				$reorganized_sheets_hash[$udata_opening['sheet_id']]['openings'][$udata_opening['opening_id']] = [
					'opening_id'       => $udata_opening['opening_id']
					, 'begin_datetime' => $udata_opening['begin_datetime']
					, 'end_datetime'   => $udata_opening['end_datetime']
					, 'name'           => $udata_opening['name']
					, 'description'    => $udata_opening['description']
					, 'location'       => $udata_opening['location']
					, 'signups'        => []
				];
			}
		}

		// iterate through 'sheets' of $reorganized_sheets_hash
		foreach ($reorganized_sheets_hash as $reorganized_sheet_key => $reorganized_sheet) {
			// iterate through 'openings' of $reorganized_sheets_hash
			foreach ($reorganized_sheet['openings'] as $reorganized_opening_key => $reorganized_opening) {
				// iterate through this user's 'signups'
				foreach ($udata['signups'] as $udata_signup_key => $udata_signup) {
					if ($udata_signup['opening_id'] == $reorganized_opening_key) {
						// add this user's signup to this $reorganized_sheets_hash sheet array element
						if (!array_key_exists($udata_signup_key, $reorganized_sheets_hash[$reorganized_sheet_key]['openings'][$reorganized_opening_key]['signups'])) {
							$reorganized_sheets_hash[$reorganized_sheet_key]['openings'][$reorganized_opening_key]['signups'][$udata_signup['signup_id']] = [
								'signup_id'              => $udata_signup['signup_id']
								, 'signup_admin_comment' => $udata_signup['admin_comment']
								, 'signup_user_id'       => $udata['user_id']
								, 'signup_username'      => $udata['username']
								, 'signup_first_name'    => $udata['first_name']
								, 'signup_last_name'     => $udata['last_name']
							];
						}
					}
				}
			}
		}
	}


	# 2.3 Build and send daily reminder emails to "owners_and_admins"
	#     Cycle through hash ids; build each email from that hash entry; make the email; send it and sleep for a moment to avoid overwhelming the mail server

	$from    = 'signup_sheets-no-reply@' . INSTITUTION_DOMAIN;
	$subject = "[Glow Signup Sheets] Upcoming signups: " . date_format(new DateTime($cur_date), "m/d/Y");
	$headers = "From: $from";

	// iterate through each manager (owner or admin)
	foreach ($owners_and_admins_hash as $manager_key => $manager) {
		$output_openings_hash = []; // reset for each manager
		$body                 = "Hi " . $manager['first_name'] . ",\n\nThis is a reminder of upcoming signups on sheets you own or manage:\n";

		// iterate through each of this manager's sheets
		foreach ($manager['sheets'] as $manager_sheet_key => $manager_sheet) {

			// check sheet preferences: does the owner or admin of this sheet want to be notified?
			if (($manager_key == $manager_sheet['owner_user_id'] && $manager_sheet['flag_alert_owner_imminent']) || ($manager_key != $manager_sheet['owner_user_id'] && $manager_sheet['flag_alert_admin_imminent'])) {

				// iterate through 'sheets' of reorganized_sheets_hash (user signups)
				foreach ($reorganized_sheets_hash as $reorganized_sheet_key => $reorganized_sheet) {

					// check for match: does this manager_sheet match the current reorganized_sheet?
					if ($manager_sheet_key == $reorganized_sheet_key) {

						// append openings and signups
						foreach ($reorganized_sheet['openings'] as $reorganized_opening_key => $reorganized_opening) {
							// check: if signups > 0 then create header
							if (count($reorganized_opening['signups']) > 0) {
								$pretty_info       = getPrettyInfo_Managers($manager_sheet, $reorganized_opening);
								$pretty_date_range = getPrettyDateRanges($reorganized_opening);
								// create hash to later sort all openings by begin_datetime ASC
								$output_openings_hash[$reorganized_opening_key] = [
									'begin_datetime' => $reorganized_opening['begin_datetime'],
									'message_text'   => "\n" . $pretty_date_range . $pretty_info . "\n",
									'signups_text'   => ""
								];
							}

							// append each signup
							foreach ($reorganized_opening['signups'] as $reorganized_signup_key => $reorganized_signup) {
								$output_openings_hash[$reorganized_opening_key]['signups_text'] .= "- " . $reorganized_signup['signup_first_name'] . " " . $reorganized_signup['signup_last_name'] . " (" . $reorganized_signup['signup_username'] . ")\n";
							}
						}
					}
				}
			}
		}

		// check: if hash has records, then signups exist, and we need to build the daily reminder email
		if (count($output_openings_hash)) {

			if ($debug) {
				echo "\n<hr />output_hash<br />\n";
				util_prePrintR($output_openings_hash);
			}

			// sort output (by begin_datetime ASC)
			usort($output_openings_hash, 'cmp_date_sort');

			// construct a single string element, including initial greeting text, for the QueuedMessage argument
			foreach ($output_openings_hash as $opening) {
				$body .= $opening['message_text'] . $opening['signups_text'];
			}

			// now queue the message (TODO - presently not used: $headers)
			// QueuedMessage::factory($db, $user_id, $target, $summary, $body, $opening_id = 0, $sheet_id = 0, $type = 'email' )
			if ($sendToMeOnly) {
				// testing: force all output emails to a single account
				$qm = QueuedMessage::factory($DB, $manager['user_id'], "dwk2@williams.edu", $subject, $body, 0, 0);
			}
			else {
				// normal procedures
				$qm = QueuedMessage::factory($DB, $manager['user_id'], $manager['email'], $subject, $body, 0, 0);
			}
			$qm->updateDb();

			if (!$qm->matchesDb) {
				// create record failed
				$results['notes'] = "database error: could not create queued message for MANAGER daily reminder";
				error_log("QueuedMessage failed to insert db record (email subject: $subject)");
				echo json_encode($results);
				exit;
			}

			if ($debug) {
				echo $body . "<hr />\n";
			}
		}
	}

	if ($debug) {
		echo "\n<hr />users_hash<br />\n";
		util_prePrintR($users_hash);
		echo "\n<hr />reorganized_sheets_hash<br />\n";
		util_prePrintR($reorganized_sheets_hash);
		echo "\n<hr />owners_and_admins_hash<br />\n";
		util_prePrintR($owners_and_admins_hash);
	}
