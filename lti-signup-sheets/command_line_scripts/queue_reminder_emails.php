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
	 */

	function getPrettyDateRanges($opening) {
		$start_dt = new DateTime($opening['begin_datetime']);
		$end_dt   = new DateTime($opening['end_datetime']);

		$time_range_string_pt1_base = $start_dt->format('F d, g:i');
		$time_range_string_pt1_ap   = '';
		$time_range_string_pt2      = $end_dt->format('g:i A');
		if (($start_dt->format('a') != $end_dt->format('a'))
			|| ($start_dt->format('F d, Y') != $end_dt->format('F d, Y'))
		) {
			$time_range_string_pt1_ap = ' ' . $start_dt->format('A');
		}
		if ($start_dt->format('Y') != $end_dt->format('Y')) {
			$time_range_string_pt2 = $end_dt->format('F d, g:i A');
		}
		elseif ($start_dt->format('F d, Y') != $end_dt->format('F d, Y')) {
			$time_range_string_pt2 = $end_dt->format('F d, g:i A');
		}
		$time_range_string = $time_range_string_pt1_base . $time_range_string_pt1_ap . ' - ' . $time_range_string_pt2;

		return $time_range_string;
	}

	# TODO: support command line arg for date start (default to today) and range (default to 2 days)

	$cur_date           = date('Y-m-d');
	$lookahead_interval = 42; // TODO - live value should be: 2


	# 1. Get all the upcoming signups (cur time to cur time + 48 hours); for each signup, get the opening, sheet, and signup's user info
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

	$signups_stmt = $DB->prepare($signups_sql);
	$signups_stmt->execute();
	Db_Linked::checkStmtError($signups_stmt);


	# 1.1. build up the users hash - cycle through the user signups data
	/*
	 * user_id :
	 *      user_id
	 *      username
	 *      first name
	 *      last name
	 *      email
	 *      signups :
	 *            signup_id
	              opening_id
	 *            signup_user_id
	 *            admin_comment
	 *      openings :
	 *            opening_id
	 *            sheet_id
	 *            begin_datetime
	 *            end_datetime
	 *            name
	 *            description
	 *            location
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
				, 'signups'    => []
				, 'openings'   => []
				, 'sheets'     => []
			];
		}

		# append the signup data to the appropriate list in the user structure
		$users_hash[$row['user_id']]['signups'][$row['signup_id']] = [
			'signup_id'        => $row['signup_id']
			, 'opening_id'     => $row['opening_id']
			, 'signup_user_id' => $row['signup_user_id']
			, 'admin_comment'  => $row['admin_comment']
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
	}


	# 2. Get all the owners and admins who opted in to receive daily reminders of upcoming signups for their openings; for each user, get user info and sheet flags
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

	$owners_and_admins_stmt = $DB->prepare($owners_and_admins_sql);
	$owners_and_admins_stmt->execute();
	Db_Linked::checkStmtError($owners_and_admins_stmt);


	# 2.1. build up the owners_admins info hash - cycle through the owners_admins data
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
		# initialize the owners_and_admins info data structure if need be
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


	# 3. users: build and send the emails
	/*
	 * cycle through hash ids; build each email from that hash entry; sort signups by opening.begin_datetime; make the email; send it and sleep for a moment to avoid overwhelming the mail server
	 */
	$from    = 'signup_sheets-no-reply@' . INSTITUTION_DOMAIN;
	$subject = "[Signup Sheets] $cur_date upcoming signups";
	$headers = "From: $from";

	foreach ($users_hash as $uid => $udata) {
		$body = "Hi " . $udata['first_name'] . ",\n\nThis is a reminder about upcoming signups for the next $lookahead_interval days.";
		# add signups (via their corresponding openings)
		if (count($udata['openings']) > 0) {
			$body .= "\n\nYou have signed up for:\n\n";
			foreach ($udata['openings'] as $opening) {
				$pretty_date_range = getPrettyDateRanges($opening);
				$pretty_name     = (empty($opening['name'])) ? '' : "\nEvent name: " . $opening['name'] . ")";
// TODO finish this
//				$pretty_name     = (empty($udata['sheets'][$opening['sheet_id']])) ? '' : "\nEvent name: " . $opening['name'] . ")";
				$pretty_location = (empty($opening['location'])) ? '' : " (" . $opening['location'] . ")";

				$body .= $pretty_date_range . $pretty_name . $pretty_location . "\n";
			}
		}
		// now queue the message
		// mail($udata['email'], $subject, $body, $headers);
		// ORIGINAL CODE: $qm = QueuedMessage::factory($DB,$udata['email'],$subject,$body);
		//		$qm = QueuedMessage::factory($DB, $udata['email'], $subject, $body, $USER->user_id, $sheet_id, $opening_id);
		//		$qm->updateDb();
		//    echo $body; // for testing - use above line for actually sending the email
		echo $body . "<br /><br />";
	}

	util_prePrintR($users_hash);
	echo "<hr />";
	util_prePrintR($owners_and_admins_hash);
	exit;


	# 4. owners_and_admins: build and send the emails


