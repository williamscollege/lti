<?php
	/***********************************************
	 ** Project:    Dashboard for Automating Canvas Maintenance
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:
	 ** - Manage LTI tool consumers
	 ** - Regularly Sync Canvas Users (Amazon AWS) to Dashboard
	 ** - Push Avatar Uploads
	 ** - Set Notification Preferences
	 ** - SIS Imports (error report)
	 ** - Course: Auto-Enroll Faculty
	 ** -
	 ** Current features:
	 **  - something
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, curl, mbyte, dom
	 ***********************************************/

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/include/connDB.php');
	require_once(dirname(__FILE__) . '/util.php');

	// initialize variables
	$circleGraphic_js_builder = "";

	#------------------------------------------------#
	# SQL: fetch various column counts
	#	canvas_user_id (total count of Canvas users synced to dashboard_users)
	#	flag_is_set_avatar_image (set=1)
	#	flag_is_set_notification_preference (set=1)
	#------------------------------------------------#

	$queryUserFieldCounts = "
		SELECT
			 (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0) AS cnt_dashboard_users
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_avatar_image` = 1) AS cnt_avatars_exist
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_avatar_image` = 0) AS cnt_avatars_missing
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_notification_preference` = 1) AS cnt_notif_pref_exist
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_notification_preference` = 0) AS cnt_notif_pref_missing
			, (SELECT COUNT(*) FROM `lti_consumer` WHERE `enabled` = 1) AS cnt_lti_consumer_enabled
			, (SELECT COUNT(*) FROM `lti_consumer` WHERE `enabled` = 0) AS cnt_lti_consumer_disabled

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas') AS cnt_logs_verify_sis_imports
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_datetime
			, (SELECT `num_items` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_num_items
			, (SELECT `num_changes` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_num_changes
			, (SELECT `num_errors` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_num_errors
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_dataset_brief

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard') AS cnt_logs_sync_canvas_users
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard' ORDER BY `event_datetime` DESC LIMIT 1) AS log_sync_canvas_datetime
			, (SELECT `num_items` FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard' ORDER BY `event_datetime` DESC LIMIT 1) AS log_sync_canvas_num_items
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard' ORDER BY `event_datetime` DESC LIMIT 1) AS log_sync_canvas_dataset_brief
			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'set_canvas_notification_preferences') AS cnt_logs_notif_pref_users
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'set_canvas_notification_preferences' ORDER BY `event_datetime` DESC LIMIT 1) AS log_notif_pref_datetime
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'set_canvas_notification_preferences' ORDER BY `event_datetime` DESC LIMIT 1) AS log_notif_pref_dataset_brief
			, (SELECT `updated` FROM `lti_context` ORDER BY `updated` DESC LIMIT 1) AS lti_context_datetime
	";
	$resultsUserFieldCounts = mysqli_query($connString, $queryUserFieldCounts) or
	die(mysqli_error($connString));

	# begin debugging
	/* Get field information for all fields */
	/*	while ($finfo = mysqli_fetch_field($resultsUserFieldCounts)) {
			echo "field: " . $finfo->name  . "<br />";
		}
		while ($row = mysqli_fetch_assoc($resultsUserFieldCounts)) {
			echo $row["cnt_dashboard_users"] . "<br />";
			echo $row["cnt_avatars_exist"] . "<br />";
			echo $row["cnt_avatars_missing"] . "<br />";
			echo $row["cnt_notif_pref_exist"] . "<br />";
			echo $row["cnt_notif_pref_missing"] . "<br />";
			echo $row["cnt_lti_consumer_enabled"] . "<br />";
			echo $row["cnt_lti_consumer_disabled"] . "<br />";
		}*/
	# end debugging

	# Convert recordset to variables
	$rows = mysqli_fetch_array($resultsUserFieldCounts);
	if ($rows) {
		$cnt_dashboard_users         = $rows["cnt_dashboard_users"];
		$cnt_avatars_exist           = $rows["cnt_avatars_exist"];
		$cnt_avatars_missing         = $rows["cnt_avatars_missing"];
		$cnt_notif_pref_exist        = $rows["cnt_notif_pref_exist"];
		$cnt_notif_pref_missing      = $rows["cnt_notif_pref_missing"];
		$cnt_lti_consumer_enabled    = $rows["cnt_lti_consumer_enabled"];
		$cnt_lti_consumer_disabled   = $rows["cnt_lti_consumer_disabled"];
		$cnt_logs_verify_sis_imports = $rows["cnt_logs_verify_sis_imports"];
		$cnt_logs_sync_canvas_users  = $rows["cnt_logs_sync_canvas_users"];
		$cnt_logs_notif_pref_users   = $rows["cnt_logs_notif_pref_users"];

		// avoid null values
		$log_verify_sis_imports_datetime      = empty($rows["log_verify_sis_imports_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_verify_sis_imports_datetime"]), "M d, Y h:i:s a");
		$log_verify_sis_imports_num_items     = empty($rows["log_verify_sis_imports_num_items"]) ? 0 : $rows["log_verify_sis_imports_num_items"];
		$log_verify_sis_imports_num_changes   = empty($rows["log_verify_sis_imports_num_changes"]) ? 0 : $rows["log_verify_sis_imports_num_changes"];
		$log_verify_sis_imports_num_errors    = empty($rows["log_verify_sis_imports_num_errors"]) ? 0 : $rows["log_verify_sis_imports_num_errors"];
		$log_verify_sis_imports_dataset_brief = empty($rows["log_verify_sis_imports_dataset_brief"]) ? 0 : $rows["log_verify_sis_imports_dataset_brief"];
		$log_sync_canvas_datetime             = empty($rows["log_sync_canvas_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_sync_canvas_datetime"]), "M d, Y h:i:s a");
		$log_sync_canvas_num_items            = empty($rows["log_sync_canvas_num_items"]) ? 0 : $rows["log_sync_canvas_num_items"];
		$log_sync_canvas_dataset_brief        = empty($rows["log_sync_canvas_dataset_brief"]) ? 0 : $rows["log_sync_canvas_dataset_brief"];
		$log_notif_pref_datetime              = empty($rows["log_notif_pref_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_notif_pref_datetime"]), "M d, Y h:i:s a");
		$log_notif_pref_num_items             = empty($rows["log_notif_pref_num_items"]) ? 0 : $rows["log_notif_pref_num_items"];
		$log_notif_pref_dataset_brief         = empty($rows["log_notif_pref_dataset_brief"]) ? 0 : $rows["log_notif_pref_dataset_brief"];
		$lti_context_datetime                 = empty($rows["lti_context_datetime"]) ? 'n/a' : date_format(new DateTime($rows["lti_context_datetime"]), "M d, Y h:i:s a");

		// calculations (avoid division by zero or null values)
		// note for percentVerifySISImports: it is impossible to obtain a true percent for the complex action of SIS imports; instead create a convincing yet artificial percent
		$percentVerifySISImports  = ($log_verify_sis_imports_num_errors == 0) ? 100 : round(90 / $log_verify_sis_imports_num_errors, PHP_ROUND_HALF_UP); // why 90? because 100% / 1 error = 100% :)
		$percentSyncCanvasUsers   = ($cnt_dashboard_users == 0) ? 0 : round($cnt_dashboard_users / $cnt_dashboard_users * 100, PHP_ROUND_HALF_UP);
		$percentPushAvatarUploads = ($cnt_dashboard_users == 0) ? 0 : round($cnt_avatars_exist / $cnt_dashboard_users * 100, PHP_ROUND_HALF_UP);
		$percentSetNotifPrefs     = ($cnt_dashboard_users == 0) ? 0 : round($cnt_notif_pref_exist / $cnt_dashboard_users * 100, PHP_ROUND_HALF_UP);
		$percentLTIConsumers      = ($cnt_lti_consumer_enabled == 0) ? 0 : round($cnt_lti_consumer_enabled / ($cnt_lti_consumer_enabled) * 100, PHP_ROUND_HALF_UP);
		$percentAutoEnrollFaculty = 0; // TODO! ($cnt_lti_consumer_enabled == 0) ? 0 : round($cnt_lti_consumer_enabled / ($cnt_lti_consumer_enabled) * 100, PHP_ROUND_HALF_UP);
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo LTI_APP_NAME; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo LTI_APP_NAME; ?>">
	<meta name="author" content="<?php echo LANG_AUTHOR_NAME; ?>">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo PATH_JQUERYUI_CSS; ?>" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/wms-custom.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERYUI_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<script src="<?php echo PATH_BOOTSTRAP_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERY_CIRCLEGRAPHIC_JS; ?>"></script>
	<!-- local JS -->
	<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="page-header">
			<h1><?php echo LTI_APP_NAME; ?></h1>
			<h5><?php echo LANG_INSTITUTION_NAME; ?>: Dashboard of critical systems that update our LMS</h5>

			<div id="breadCrumbs" class="small"><?php require_once(dirname(__FILE__) . '/include/breadcrumbs.php'); ?></div>
		</div>
	</div>
	<div class="well well-sm">
		<p class="small">
			This dashboard steadily monitors critical systems that support and customize our Glow LMS.
			Error notifications are sent automatically, providing staff with an early opportunity to inspect and manually intervene.
			Each time a component of our SIS updating fails, we learn something and attempt to improve our data transfer processes and these monitoring
			tools.<br />
			What is monitored?
		</p>
		<ol class="small">
			<li>Hourly: data integrity checks of all SIS data imports into Instructure from PeopleSoft</li>
			<li>Daily: run custom scripts that create a custom and uniform default environment for all Glow users (includes: uploading profile images, setting
				notification preferences, and some auto-enrollments)
			</li>
			<li>LTI Management Console necessary for Glow applications such as &quot;Signup Sheets,&quot; and &quot;Course Email.&quot;</li>
		</ol>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>LTI Tool Consumers</h3>

				<div class="circleGraphic1 col-md-9 col-sm-9">
					<span class="circleIntegerValue">
						<?php
							// output circleGraphic value (hidden): build jQuery string for later $(window).load
							echo $percentLTIConsumers;
							if ($percentLTIConsumers == 100) {
								$circleGraphic_js_builder .= "$('.circleGraphic1').circleGraphic({'color': '#00B233'});"; // green
							}
							else {
								$circleGraphic_js_builder .= "$('.circleGraphic1').circleGraphic({'color': '#E53238'});"; // red
							}
						?>
					</span>
				</div>
				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Quantity</th>
							<td><code title="Count: live, enabled LTI applications"><?php echo number_format($cnt_lti_consumer_enabled); ?></code>
								<small>(LTI applications)</small>
							</td>
						</tr>
						<tr>
							<th class="small">Commits</th>
							<td>
								<small>
									<a href="https://github.com/williamscollege/lti" title="github (commits)" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;github
										repository</a></small>
							</td>
						</tr>
						<tr>
							<th class="small">Session</th>
							<td><code><?php echo $lti_context_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Most Usage</th>
							<td>
								<code>Signup Sheets, Course Email</code>
							</td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/lti_manage_tool_consumers.php" title="Manage LTI Tool Consumers (CRUD)"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span>&nbsp;Manage
										LTI Tool Consumers (CRUD)</a></small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Verify Integrity of SIS Imports</h3>

				<div class="circleGraphic2 col-md-9 col-sm-9">
					<span class="circleIntegerValue">
						<?php
							// output circleGraphic value (hidden): build jQuery string for later $(window).load
							echo $percentVerifySISImports;
							if ($percentVerifySISImports == 100) {
								$circleGraphic_js_builder .= "$('.circleGraphic2').circleGraphic({'color': '#00B233'});"; // green
							}
							else {
								$circleGraphic_js_builder .= "$('.circleGraphic2').circleGraphic({'color': '#E53238'});"; // red
							}
						?>
					</span>
				</div>
				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">SIS Import</th>
							<td><code title="">
									<?php echo ($log_verify_sis_imports_num_errors == 0) ? "Verified: " . $log_verify_sis_imports_num_changes . " data checks" : "Error notifications sent!"; ?>
								</code>
							</td>
						</tr>
						<tr>
							<th class="small">Status</th>
							<td><code><?php echo $log_verify_sis_imports_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php echo $log_verify_sis_imports_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: every 2 hours</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/verify_sis_imports_into_canvas.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=verify_sis_imports_into_canvas" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_verify_sis_imports; ?>)</a></small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Sync Canvas to Dashboard</h3>

				<div class="circleGraphic3 col-md-9 col-sm-9">
					<span class="circleIntegerValue">
						<?php
							// output circleGraphic value (hidden): build jQuery string for later $(window).load
							echo $percentSyncCanvasUsers;
							if ($percentSyncCanvasUsers == 100) {
								$circleGraphic_js_builder .= "$('.circleGraphic3').circleGraphic({'color': '#00B233'});"; // green
							}
							else {
								$circleGraphic_js_builder .= "$('.circleGraphic3').circleGraphic({'color': '#E53238'});"; // red
							}
						?>
					</span>
				</div>
				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Users</th>
							<td>
								<code title="Counts: Dashboard users / Total # Canvas users"><?php echo "Canvas: " . number_format($log_sync_canvas_num_items) . ", " . "Dashboard: " . number_format($cnt_dashboard_users); ?></code>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php echo $log_sync_canvas_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php echo $log_sync_canvas_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/sync_canvas_users_to_dashboard.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=sync_canvas_users_to_dashboard" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_sync_canvas_users; ?>)</a></small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Push Avatar Uploads</h3>

				<div class="circleGraphic4 col-md-9 col-sm-9">
					<span class="circleIntegerValue">
						<?php
							// output circleGraphic value (hidden): build jQuery string for later $(window).load
							echo $percentPushAvatarUploads;
							if ($percentPushAvatarUploads == 100) {
								$circleGraphic_js_builder .= "$('.circleGraphic4').circleGraphic({'color': '#00B233'});"; // green
							}
							else {
								$circleGraphic_js_builder .= "$('.circleGraphic4').circleGraphic({'color': '#E53238'});"; // red
							}
						?>
					</span>
				</div>
				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">AWS Cloud</th>
							<td>
								<code title="Canvas users with AWS Avatars / Total # Canvas users"><?php echo "Avatars: " . number_format($cnt_avatars_exist) . ", " . "Users: " . number_format($log_sync_canvas_num_items); ?></code>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php #echo $log_sync_canvas_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php #echo $log_sync_canvas_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/abc.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=XYZ" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php #echo $cnt_logs_sync_canvas_users; ?>)</a></small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Set Notification Preferences</h3>

				<div class="circleGraphic5 col-md-9 col-sm-9">
					<span class="circleIntegerValue">
						<?php
							// output circleGraphic value (hidden): build jQuery string for later $(window).load
							echo $percentSetNotifPrefs;
							if ($percentSetNotifPrefs == 100) {
								$circleGraphic_js_builder .= "$('.circleGraphic5').circleGraphic({'color': '#00B233'});"; // green
							}
							else {
								$circleGraphic_js_builder .= "$('.circleGraphic5').circleGraphic({'color': '#E53238'});"; // red
							}
						?>
					</span>
				</div>
				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Total</th>
							<td>
								<code title="Tallies of notification preferences updated for Canvas users"><?php echo number_format($cnt_notif_pref_exist) . " of " . number_format($log_sync_canvas_num_items) . " Canvas users updated"; ?></code>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php echo $log_notif_pref_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php echo $log_notif_pref_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/set_canvas_notification_preferences.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=set_canvas_notification_preferences" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_notif_pref_users; ?>)</a></small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Course: Auto-Enroll Faculty</h3>

				<div class="circleGraphic6 col-md-9 col-sm-9">
					<span class="circleIntegerValue">
						<?php
							// output circleGraphic value (hidden): build jQuery string for later $(window).load
							echo $percentAutoEnrollFaculty;
							if ($percentAutoEnrollFaculty == 100) {
								$circleGraphic_js_builder .= "$('.circleGraphic6').circleGraphic({'color': '#00B233'});"; // green
							}
							else {
								$circleGraphic_js_builder .= "$('.circleGraphic6').circleGraphic({'color': '#E53238'});"; // red
							}
						?>
					</span>
				</div>
				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Count</th>
							<td><code><?php #echo number_format($cnt_notif_pref_exist) . " / " . number_format($log_sync_canvas_num_items); ?></code>
								<small>("Faculty Funding Resources")</small>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php #echo $log_sync_canvas_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php #echo $log_sync_canvas_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/abc.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=XYZ" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php #echo $cnt_logs_sync_canvas_users; ?>)</a>&nbsp;&#124;
									<a href="https://glow.williams.edu/courses/1549176" title="Glow: Faculty Funding Resources" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Glow
										Course</a></small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Glow Statistics</h3>

				<!-- wmsStatistics class contains background image -->
				<a href="<?php echo APP_ROOT_PATH; ?>/glowstats/index.php" title="Glow Statistics">
					<div class="wmsStatistics col-md-9 col-sm-9">
					</div>
				</a>

				<div class="wms-after-circle">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">LMS's</th>
							<td>
								<code title="LMS's represented: Canvas, Moodle, Blackboard"><?php echo "3 (Canvas, Moodle, Blackboard)"; ?></code>
							</td>
						</tr>
						<tr>
							<th class="small">Canvas</th>
							<td><code>2013-present</code></td>
						</tr>
						<tr>
							<th class="small">Moodle</th>
							<td><code>2010-2013</code></td>
						</tr>
						<tr>
							<th class="small">Blackboard</th>
							<td><code>2003-2010</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="https://glow.williams.edu/accounts/98616/statistics" title="Instructure Canvas Statistics and Analytics"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Canvas</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/glowstats/index.php" title="Moodle Statistics" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;Moodle</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/glowstats/blackboard-statistics/index.htm" title="Blackboard Statistics" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;Blackboard</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
	</div> <!-- /.row -->

	<?php require_once(dirname(__FILE__) . '/include/foot.php'); ?>
</div> <!-- /.container -->
<script>
	$(window).load(function () {
		// Colors: rainbow roygbiv: #CC0000, #ED5F21, #FAE300, #5B9C0A, #0A0D9C, #500A9C, #990A9C
		// Colors: original green #00B233, red #E53238, orange #FF9900
		// Colors: williams purple #543192
		<?php echo $circleGraphic_js_builder; ?>
	});
</script>
</body>
</html>
