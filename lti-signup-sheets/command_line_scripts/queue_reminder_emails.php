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

	# TODO: support command line arg for date start (default to today) and range (default to 2 days)

	$cur_date           = date('Y-m-d');
	$lookahead_interval = 42; // TODO - live value should be: 2

	function getReservationTimeRangeInfo($rsv) {
		$start_dt = new DateTime($rsv['time_block_start_datetime']);
		$end_dt   = new DateTime($rsv['time_block_end_datetime']);

		$time_range_string_pt1_base = $start_dt->format('Y/n/j g:i');
		$time_range_string_pt1_ap   = '';
		$time_range_string_pt2      = $end_dt->format('g:i A');
		if (($start_dt->format('a') != $end_dt->format('a'))
			|| ($start_dt->format('Y/n/j') != $end_dt->format('Y/n/j'))
		) {
			$time_range_string_pt1_ap = ' ' . $start_dt->format('A');
		}
		if ($start_dt->format('Y') != $end_dt->format('Y')) {
			$time_range_string_pt2 = $end_dt->format('Y/n/j g:i A');
		}
		elseif ($start_dt->format('Y/n/j') != $end_dt->format('Y/n/j')) {
			$time_range_string_pt2 = $end_dt->format('n/j g:i A');
		}
		$time_range_string = $time_range_string_pt1_base . $time_range_string_pt1_ap . ' - ' . $time_range_string_pt2;

		return $time_range_string;
	}

	function getReservationItemInfo($rsv) {
		return $rsv['item_name'] . ' (in ' . $rsv['group_name'] . ' : ' . $rsv['subgroup_name'] . ')';
	}

	function getReservationUserInfo($rsv) {
		return 'reserved by ' . $rsv['user_fname'] . ' ' . $rsv['user_lname'] . ' (' . $rsv['username'] . ')';
	}

	# 1. Get all the upcoming signups (cur time to cur time + 48 hours); for each signup, get the opening, sheet, and signup's user info
	$signups_sql =
		"SELECT
			* -- filter for smaller recordset
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
		ORDER BY
			signups.signup_user_id ASC,openings.begin_datetime ASC";

	$signups_stmt = $DB->prepare($signups_sql);
	$signups_stmt->execute();
	Db_Linked::checkStmtError($signups_stmt);


	# 1.1. build up the user signups info hash - cycle through the user signups data
	/*
	 * user_id :
	 *      user_id
	 *      username
	 *      first name
	 *      last name
	 *      email
	 *      signups :
	 *          * signup_id
	              opening_id
	 *            signup_user_id
	 *            admin_comment
	 *      openings :
	 *          * opening_id
	 *            sheet_id
	 *            begin_datetime
	 *            end_datetime
	 *            name
	 *            description
	 *            location
	 *      sheets :
	 *          * sheet_id
	 *            owner_user_id
	 *            name
	 *            description
	 *            type
	 *            begin_date
	 *            end_date
	 *            flag_alert_owner_imminent
	 *            flag_alert_admin_imminent
	 */

	$signups_hash = [];
	$users_info_hash    = [];
	$last_user_data     = ['user_id' => 0];
	while ($row = $signups_stmt->fetch(PDO::FETCH_ASSOC)) {

		# initialize the users info data structure if need be
		if (!array_key_exists($row['user_id'], $users_info_hash)) {
			$users_info_hash[$row['user_id']] = [
				'user_id'    => $row['user_id']
				, 'username' => $row['username']
				, 'fname'    => $row['first_name']
				, 'lname'    => $row['last_name']
				, 'email'    => $row['email']
				, 'signups'  => []
				, 'openings' => []
				, 'sheets'   => []
			];

			# get the list of the groups that the user manages
			$u = User::getOneFromDb(['user_id' => $row['user_id']], $DB);
			$u->loadEqGroups();
			foreach ($u->eq_groups AS $u_eqg) {
				if ($u->canManageEqGroup($u_eqg)) {
					array_push($users_info_hash[$row['user_id']]['managed_group_ids'], $u_eqg->eq_group_id);
				}
			}
		}

		# append the eq reservation data to the appropriate list in the user structure
		if ($row['schedule_type'] == 'manager') {
			array_push($users_info_hash[$row['user_id']]['manager_reservations'], $row);
		}
		else {
			array_push($users_info_hash[$row['user_id']]['consumer_reservations'], $row);
		}

		# initialize the groups info data structure if need be
		if (!array_key_exists('g' . $row['group_id'], $signups_hash)) {
			$signups_hash['g' . $row['group_id']] = [];
		}

		# append the eq reservation data to the appropriate group list
		array_push($signups_hash['g' . $row['group_id']], $row);
	}

	# match the group reservation data up with the users that manage those groups
	foreach (array_keys($users_info_hash) as $uid) {
		foreach ($users_info_hash[$uid]['managed_group_ids'] as $managed_gid) {
			if (array_key_exists('g' . $managed_gid, $signups_hash)) {
				foreach ($signups_hash['g' . $managed_gid] as $grdata) {
					if ($grdata['user_id'] != $uid) {
						array_push($users_info_hash[$uid]['reservations_on_managed_groups'], $grdata);
					}
				}
			}
		}

	}


	# 2. Get all the owners and admins who opted in to receive daily reminders of upcoming signups for their openings; for each user, get user info and sheet flags
	$owners_and_admins_sql =
		"SELECT
			DISTINCT u.*
		FROM
			users as u
		INNER JOIN
			sus_sheets as sheets
			ON u.user_id = sheets.owner_user_id
		WHERE
			sheets.flag_alert_owner_imminent = 1
		UNION
		SELECT
			DISTINCT u.*
		FROM
			users as u
		INNER JOIN
			sus_access as access
			ON u.username = access.constraint_data
			AND access.type = 'adminbyuser'
		INNER JOIN
			sus_sheets as sheets
			ON access.sheet_id = sheets.sheet_id
			AND sheets.flag_alert_admin_imminent = 1";


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
	 *          * sheet_id
	 *            owner_user_id
	 *            flag_alert_owner_imminent
	 *            flag_alert_admin_imminent
	 */

	// TODO implement hash



	# 3. build and send the emails
	/*
	 * cycle through hash ids; build each email from that hash entry; sort each reservation group by begin time; make the email; send it and sleep for a moment to avoid overwhelming the mail server
	 */
	$from    = 'equipment_reservation-no-reply@' . INSTITUTION_DOMAIN;
	$subject = "[EqReserve] $cur_date upcoming equipment reservations";
	$headers = "From: $from";


	foreach ($users_info_hash as $uid => $udata) {
		$body = "
Hello " . $udata['fname'] . ",

This is a reminder about upcoming equipment reservations for the next $lookahead_interval days.";
		# add consumer reservation section if needed
		if (count($udata['consumer_reservations']) > 0) {
			$body .= "

        Equipment you have reserved for your use:";
			$prev_rsv_stamp = '';
			foreach ($udata['consumer_reservations'] as $rsv) {
				$cur_rsv_stamp = getReservationTimeRangeInfo($rsv);
				if ($prev_rsv_stamp != $cur_rsv_stamp) {
					$body .= "

                    $cur_rsv_stamp";
					$prev_rsv_stamp = $cur_rsv_stamp;
				}
				$body .= "
                            " . getReservationItemInfo($rsv);
			}
		}

		# add manager reservation section if needed
		if (count($udata['manager_reservations']) > 0) {
			$body .= "


        Equipment you have reserved for management/maintenance purposes:";
			$prev_rsv_stamp = '';
			foreach ($udata['manager_reservations'] as $rsv) {
				$cur_rsv_stamp = getReservationTimeRangeInfo($rsv);
				if ($prev_rsv_stamp != $cur_rsv_stamp) {
					$body .= "

                    $cur_rsv_stamp";
					$prev_rsv_stamp = $cur_rsv_stamp;
				}
				$body .= "
                            " . getReservationItemInfo($rsv);
			}
		}

		# add section for reservations in managed groups if needed
		if (count($udata['reservations_on_managed_groups']) > 0) {
			$body .= "


        Reservations other people have on equipment that you manage:";
			$prev_rsv_stamp = '';
			foreach ($udata['reservations_on_managed_groups'] as $rsv) {
				$cur_rsv_stamp = getReservationTimeRangeInfo($rsv);
				if ($prev_rsv_stamp != $cur_rsv_stamp) {
					$body .= "

                    $cur_rsv_stamp " . getReservationUserInfo($rsv);
					$prev_rsv_stamp = $cur_rsv_stamp;
				}
				$body .= "
                            " . getReservationItemInfo($rsv);
			}
		}

		// now queue the message
		// mail($udata['email'], $subject, $body, $headers);
		// ORIGINAL CODE: $qm = QueuedMessage::factory($DB,$udata['email'],$subject,$body);
		$qm = QueuedMessage::factory($DB, $udata['email'], $subject, $body, $USER->user_id, $sheet_id, $opening_id);
		$qm->updateDb();
		//    echo $body; // for testing - use above line for actually sending the email
	}
