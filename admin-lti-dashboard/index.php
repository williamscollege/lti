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
	# SQL Purpose: fetch a variety of column counts
	#------------------------------------------------#

	$queryUserFieldCounts = "
		SELECT
			(SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0) AS cnt_dashboard_users
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_enrolled_course_ffr` = 1) AS cnt_dashboard_users_course_ffr
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_enrolled_course_oc` = 1) AS cnt_dashboard_users_course_oc
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_avatar_image` = 1) AS cnt_dashboard_users_with_avatars
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_notification_preference` = 1) AS cnt_notif_pref_exist
			, (SELECT COUNT(*) FROM `dashboard_faculty_current`) AS cnt_dashboard_faculty_current
			, (SELECT COUNT(*) FROM `lti_consumer` WHERE `enabled` = 1) AS cnt_lti_consumer_enabled
			, (SELECT COUNT(*) FROM `lti_consumer` WHERE `enabled` = 0) AS cnt_lti_consumer_disabled

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas') AS cnt_logs_verify_sis_imports
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_datetime
			, (SELECT `num_items` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_num_items
			, (SELECT `num_edits` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_num_edits
			, (SELECT `num_errors` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_num_errors
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'verify_sis_imports_into_canvas' ORDER BY `event_datetime` DESC LIMIT 1) AS log_verify_sis_imports_dataset_brief

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'upload_avatars_to_canvas_aws_cloud') AS cnt_logs_avatars
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'upload_avatars_to_canvas_aws_cloud' ORDER BY `event_datetime` DESC LIMIT 1) AS log_avatars_datetime
			, (SELECT `num_items` FROM `dashboard_eventlogs` WHERE `event_action` = 'upload_avatars_to_canvas_aws_cloud' ORDER BY `event_datetime` DESC LIMIT 1) AS log_avatars_num_items
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'upload_avatars_to_canvas_aws_cloud' ORDER BY `event_datetime` DESC LIMIT 1) AS log_avatars_dataset_brief

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard') AS cnt_logs_sync_canvas_users
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard' ORDER BY `event_datetime` DESC LIMIT 1) AS log_sync_canvas_datetime
			, (SELECT `num_items` FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard' ORDER BY `event_datetime` DESC LIMIT 1) AS log_sync_canvas_num_items
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard' ORDER BY `event_datetime` DESC LIMIT 1) AS log_sync_canvas_dataset_brief

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'auto_enroll_canvas_course_ffr') AS cnt_logs_auto_enroll_ffr
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'auto_enroll_canvas_course_ffr' ORDER BY `event_datetime` DESC LIMIT 1) AS log_auto_enroll_ffr_datetime
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'auto_enroll_canvas_course_ffr' ORDER BY `event_datetime` DESC LIMIT 1) AS log_auto_enroll_ffr_dataset_brief

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'auto_enroll_canvas_course_oc') AS cnt_logs_auto_enroll_oc
			, (SELECT `event_datetime` FROM `dashboard_eventlogs` WHERE `event_action` = 'auto_enroll_canvas_course_oc' ORDER BY `event_datetime` DESC LIMIT 1) AS log_auto_enroll_oc_datetime
			, (SELECT `event_dataset_brief` FROM `dashboard_eventlogs` WHERE `event_action` = 'auto_enroll_canvas_course_oc' ORDER BY `event_datetime` DESC LIMIT 1) AS log_auto_enroll_oc_dataset_brief

			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'set_canvas_notification_preferences') AS cnt_logs_notif_pref
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
			echo $row["cnt_dashboard_users_with_avatars"] . "<br />";
			echo $row["cnt_notif_pref_exist"] . "<br />";
			echo $row["cnt_lti_consumer_enabled"] . "<br />";
			echo $row["cnt_lti_consumer_disabled"] . "<br />";
		}*/
	# end debugging

	# Convert recordset to variables
	$rows = mysqli_fetch_array($resultsUserFieldCounts);
	if ($rows) {
		$cnt_dashboard_users              = $rows["cnt_dashboard_users"];
		$cnt_dashboard_users_course_ffr   = $rows["cnt_dashboard_users_course_ffr"];
		$cnt_dashboard_users_course_oc   = $rows["cnt_dashboard_users_course_oc"];
		$cnt_dashboard_faculty_current    = $rows["cnt_dashboard_faculty_current"];
		$cnt_dashboard_users_with_avatars = $rows["cnt_dashboard_users_with_avatars"];
		$cnt_notif_pref_exist             = $rows["cnt_notif_pref_exist"];
		$cnt_lti_consumer_enabled         = $rows["cnt_lti_consumer_enabled"];
		$cnt_lti_consumer_disabled        = $rows["cnt_lti_consumer_disabled"];
		$cnt_logs_verify_sis_imports      = $rows["cnt_logs_verify_sis_imports"];
		$cnt_logs_avatars                 = $rows["cnt_logs_avatars"];
		$cnt_logs_sync_canvas_users       = $rows["cnt_logs_sync_canvas_users"];
		$cnt_logs_auto_enroll_ffr         = $rows["cnt_logs_auto_enroll_ffr"];
		$cnt_logs_auto_enroll_oc         = $rows["cnt_logs_auto_enroll_oc"];
		$cnt_logs_notif_pref              = $rows["cnt_logs_notif_pref"];

		// avoid null values
		$log_verify_sis_imports_datetime      = empty($rows["log_verify_sis_imports_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_verify_sis_imports_datetime"]), "M d, Y h:i:s a");
		$log_verify_sis_imports_num_items     = empty($rows["log_verify_sis_imports_num_items"]) ? 0 : $rows["log_verify_sis_imports_num_items"];
		$log_verify_sis_imports_num_edits     = empty($rows["log_verify_sis_imports_num_edits"]) ? 0 : $rows["log_verify_sis_imports_num_edits"];
		$log_verify_sis_imports_num_errors    = empty($rows["log_verify_sis_imports_num_errors"]) ? 0 : $rows["log_verify_sis_imports_num_errors"];
		$log_verify_sis_imports_dataset_brief = empty($rows["log_verify_sis_imports_dataset_brief"]) ? 0 : $rows["log_verify_sis_imports_dataset_brief"];
		$log_avatars_datetime                 = empty($rows["log_avatars_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_avatars_datetime"]), "M d, Y h:i:s a");
		$log_avatars_num_items                = empty($rows["log_avatars_num_items"]) ? 0 : $rows["log_avatars_num_items"];
		$log_avatars_dataset_brief            = empty($rows["log_avatars_dataset_brief"]) ? 0 : $rows["log_avatars_dataset_brief"];
		$log_sync_canvas_datetime             = empty($rows["log_sync_canvas_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_sync_canvas_datetime"]), "M d, Y h:i:s a");
		$log_sync_canvas_num_items            = empty($rows["log_sync_canvas_num_items"]) ? 0 : $rows["log_sync_canvas_num_items"];
		$log_sync_canvas_dataset_brief        = empty($rows["log_sync_canvas_dataset_brief"]) ? 0 : $rows["log_sync_canvas_dataset_brief"];
		$log_auto_enroll_ffr_datetime         = empty($rows["log_auto_enroll_ffr_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_auto_enroll_ffr_datetime"]), "M d, Y h:i:s a");
		$log_auto_enroll_ffr_dataset_brief    = empty($rows["log_auto_enroll_ffr_dataset_brief"]) ? 0 : $rows["log_auto_enroll_ffr_dataset_brief"];
		$log_auto_enroll_oc_datetime         = empty($rows["log_auto_enroll_oc_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_auto_enroll_oc_datetime"]), "M d, Y h:i:s a");
		$log_auto_enroll_oc_dataset_brief    = empty($rows["log_auto_enroll_oc_dataset_brief"]) ? 0 : $rows["log_auto_enroll_oc_dataset_brief"];
		$log_notif_pref_datetime              = empty($rows["log_notif_pref_datetime"]) ? 'n/a' : date_format(new DateTime($rows["log_notif_pref_datetime"]), "M d, Y h:i:s a");
		$log_notif_pref_dataset_brief         = empty($rows["log_notif_pref_dataset_brief"]) ? 0 : $rows["log_notif_pref_dataset_brief"];
		$lti_context_datetime                 = empty($rows["lti_context_datetime"]) ? 'n/a' : date_format(new DateTime($rows["lti_context_datetime"]), "M d, Y h:i:s a");

		// calculations (avoid division by zero or null values)
		// note for percentVerifySISImports: it is impossible to obtain a true percent for the complex action of SIS imports; instead create a convincing yet artificial percent
		$progressVerifySISImports  = ($log_verify_sis_imports_num_errors == 0) ? 100 : round(90 / $log_verify_sis_imports_num_errors, PHP_ROUND_HALF_UP); // why 90? because 100% / 1 error = 100% :)
		$progressSyncCanvasUsers   = ($cnt_dashboard_users == 0) ? 0 : round($cnt_dashboard_users / $cnt_dashboard_users * 100, PHP_ROUND_HALF_UP);
		$progressAutoEnrollFFR     = ($cnt_dashboard_users_course_ffr == 0) ? 0 : round($cnt_dashboard_users_course_ffr / $cnt_dashboard_faculty_current * 100, PHP_ROUND_HALF_UP);
		$progressAutoEnrollOC     = ($cnt_dashboard_users_course_oc == 0) ? 0 : round($cnt_dashboard_users_course_oc / $cnt_dashboard_faculty_current * 100, PHP_ROUND_HALF_UP);
		$progressPushAvatarUploads = ($cnt_dashboard_users == 0) ? 0 : round($cnt_dashboard_users_with_avatars / $cnt_dashboard_users * 100, PHP_ROUND_HALF_UP);
		$progressSetNotifPrefs     = ($cnt_dashboard_users == 0) ? 0 : round($cnt_notif_pref_exist / $cnt_dashboard_users * 100, PHP_ROUND_HALF_UP);
		$progressLTIConsumers      = ($cnt_lti_consumer_enabled == 0) ? 0 : round($cnt_lti_consumer_enabled / ($cnt_lti_consumer_enabled) * 100, PHP_ROUND_HALF_UP);
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
			<h5><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>&nbsp;<?php echo LANG_INSTITUTION_NAME; ?>: Providing 24/7/365 monitoring
				of Glow critical systems</h5>

			<!--<div id="breadCrumbs" class="small"><?php /*require_once(dirname(__FILE__) . '/include/breadcrumbs.php'); */ ?></div>-->
		</div>
	</div>
	<div class="well well-sm">
		<ol class="small">
			<li>
				<strong>Purpose:</strong> Provide 24/7/365 monitoring of critical systems that support data exchange
				between <?php echo LANG_INSTITUTION_NAME; ?> and Instructure Canvas
			</li>
			<li>
				<strong>Actions:</strong> Verify completion of SIS data imports, update user accounts, auto-enroll custom courses, provide usage statistics,
				administer "LTI Management Console" for custom apps
			</li>
			<li>
				<strong>Notifications:</strong> Automatically send near-realtime error notifications to staff to enable the earliest opportunity for issue
				review and correction
			</li>
		</ol>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Verify SIS Imports to Canvas</h3>

				<div class="circleGraphic1 col-md-3 col-xs-3">
					<?php
						// build jQuery string for later $(window).load
						if ($progressVerifySISImports == 100) {
							$circleGraphic_js_builder .= "$('.circleGraphic1').circleGraphic({'color': '#00B233','progressvalue': " . $progressVerifySISImports . "});"; // green
						}
						else {
							$circleGraphic_js_builder .= "$('.circleGraphic1').circleGraphic({'color': '#E53238','progressvalue': " . $progressVerifySISImports . "});"; // red
						}
					?>
				</div>
				<div class="col-md-9 col-xs-9">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Import</th>
							<td><code title="">
									<?php echo $log_verify_sis_imports_dataset_brief; ?>
								</code>
							</td>
						</tr>
						<tr>
							<th class="small">Status</th>
							<td><code>
									<?php echo ($log_verify_sis_imports_num_errors == 0) ? "Verified: " . $log_verify_sis_imports_num_edits . " data checks" : "Error notifications sent!"; ?>
								</code>
							</td>
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
										(<?php echo $cnt_logs_verify_sis_imports; ?>)</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Sync Canvas to Dashboard database</h3>

				<div class="circleGraphic2 col-md-3 col-xs-3">
					<?php
						// build jQuery string for later $(window).load
						if ($progressSyncCanvasUsers == 100) {
							$circleGraphic_js_builder .= "$('.circleGraphic2').circleGraphic({'color': '#00B233','progressvalue': " . $progressSyncCanvasUsers . "});"; // green
						}
						else {
							$circleGraphic_js_builder .= "$('.circleGraphic2').circleGraphic({'color': '#E53238','progressvalue': " . $progressSyncCanvasUsers . "});"; // red
						}
					?>
				</div>
				<div class="col-md-9 col-xs-9">
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
							<td><code>cron: 05:00 am daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/sync_canvas_users_to_dashboard.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=sync_canvas_users_to_dashboard" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_sync_canvas_users; ?>)</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Set User Notification Preferences</h3>

				<div class="circleGraphic3 col-md-3 col-xs-3">
					<?php
						// build jQuery string for later $(window).load
						if ($progressSetNotifPrefs == 100) {
							$circleGraphic_js_builder .= "$('.circleGraphic3').circleGraphic({'color': '#00B233','progressvalue': " . $progressSetNotifPrefs . "});"; // green
						}
						else {
							$circleGraphic_js_builder .= "$('.circleGraphic3').circleGraphic({'color': '#E53238','progressvalue': " . $progressSetNotifPrefs . "});"; // red
						}
					?>
				</div>
				<div class="col-md-9 col-xs-9">
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
							<td><code>cron: 05:15 am daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/set_canvas_notification_preferences.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=set_canvas_notification_preferences" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_notif_pref; ?>)</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Upload Avatars to AWS Cloud</h3>

				<div class="circleGraphic4 col-md-3 col-xs-3">
					<?php
						// build jQuery string for later $(window).load
						if ($progressPushAvatarUploads > 85) {
							$circleGraphic_js_builder .= "$('.circleGraphic4').circleGraphic({'color': '#00B233','progressvalue': " . $progressPushAvatarUploads . "});"; // green
						}
						else {
							$circleGraphic_js_builder .= "$('.circleGraphic4').circleGraphic({'color': '#E53238','progressvalue': " . $progressPushAvatarUploads . "});"; // red
						}
					?>
				</div>
				<div class="col-md-9 col-xs-9">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">AWS Cloud</th>
							<td>
								<code title="Canvas users with AWS Avatars"><?php echo number_format($cnt_dashboard_users_with_avatars) . " of " . number_format($log_sync_canvas_num_items) . " users have avatars"; ?></code>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php echo $log_avatars_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php echo $log_avatars_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: 05:45 am daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<span class="text-muted" title="Run via command line only"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run command line</span>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=upload_avatars_to_canvas_aws_cloud" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_avatars; ?>)</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Enroll Faculty: &quot;Faculty Funding Resources&quot;</h3>

				<div class="circleGraphic5 col-md-3 col-xs-3">
					<?php
						// build jQuery string for later $(window).load
						if ($progressAutoEnrollFFR == 100) {
							$circleGraphic_js_builder .= "$('.circleGraphic5').circleGraphic({'color': '#00B233','progressvalue': " . $progressAutoEnrollFFR . "});"; // green
						}
						else {
							$circleGraphic_js_builder .= "$('.circleGraphic5').circleGraphic({'color': '#E53238','progressvalue': " . $progressAutoEnrollFFR . "});"; // red
						}
					?>
				</div>
				<div class="col-md-9 col-xs-9">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Enrolled</th>
							<td><code><?php echo number_format($cnt_dashboard_users_course_ffr); ?>: Faculty Funding Resources</code>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php echo $log_auto_enroll_ffr_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php echo $log_auto_enroll_ffr_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: 05:30 am daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/auto_enroll_canvas_course_ffr.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=auto_enroll_canvas_course_ffr" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_auto_enroll_ffr; ?>)</a>&nbsp;&#124;
									<a href="https://glow.williams.edu/courses/1549176" title="Glow: Faculty Funding Resources" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Glow
										Course</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Enroll Faculty: &quot;Open Classroom&quot;</h3>

				<div class="circleGraphic6 col-md-3 col-xs-3">
					<?php
						// build jQuery string for later $(window).load
						if ($progressAutoEnrollOC == 100) {
							$circleGraphic_js_builder .= "$('.circleGraphic6').circleGraphic({'color': '#00B233','progressvalue': " . $progressAutoEnrollOC . "});"; // green
						}
						else {
							$circleGraphic_js_builder .= "$('.circleGraphic6').circleGraphic({'color': '#E53238','progressvalue': " . $progressAutoEnrollOC . "});"; // red
						}
					?>
				</div>
				<div class="col-md-9 col-xs-9">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Enrolled</th>
							<td><code><?php echo number_format($cnt_dashboard_users_course_oc); ?>: Open Classroom</code>
							</td>
						</tr>
						<tr>
							<th class="small">Changes</th>
							<td><code><?php echo $log_auto_enroll_oc_dataset_brief; ?></code></td>
						</tr>
						<tr>
							<th class="small">Last run</th>
							<td><code><?php echo $log_auto_enroll_oc_datetime; ?></code></td>
						</tr>
						<tr>
							<th class="small">Schedule</th>
							<td><code>cron: 05:30 am daily</code></td>
						</tr>
						<tr>
							<th class="small">Tools</th>
							<td>
								<small>
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/auto_enroll_canvas_course_oc.php" title="Run now" target="_blank"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Run
										now</a>&nbsp;&#124;
									<a href="<?php echo APP_ROOT_PATH; ?>/app_code/view_logs.php?action=auto_enroll_canvas_course_oc" title="View logs" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>&nbsp;View
										logs
										(<?php echo $cnt_logs_auto_enroll_oc; ?>)</a>&nbsp;&#124;
									<a href="https://glow.williams.edu/courses/1434076" title="Glow: Open Classroom" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Glow
										Course</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Manage LTI Tool Consumers</h3>

				<div class="col-md-3 col-xs-3">
					<div class="squareExterior">
						<div class="squareInterior">
							<?php
								// output integer value
								echo $cnt_lti_consumer_enabled;
							?>
						</div>
					</div>
				</div>
				<div class="col-md-9 col-xs-9">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">Quantity</th>
							<td>
								<code title="Count: live, enabled LTI applications"><?php echo number_format($cnt_lti_consumer_enabled); ?>: LTI
									applications</code>
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
										LTI Tool Consumers (CRUD)</a>
								</small>
							<td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-6">
			<div class="wmsBoxBorder col-md-12 col-xs-12">
				<h3>Glow Statistics</h3>

				<!-- wmsStatistics class contains background image -->
				<div class="col-md-3 col-xs-3">
					<a href="<?php echo APP_ROOT_PATH; ?>/glowstats/index.php" title="Glow Statistics">
						<img src="<?php echo APP_ROOT_PATH; ?>/img/statistics2-small.png" alt="Glow Statistics">
					</a>
				</div>

				<div class="col-md-9 col-xs-9">
					<table class="table-hover">
						<tbody>
						<tr>
							<th class="small">LMS's</th>
							<td>
								<code title="LMS's represented: Canvas, Moodle, Blackboard"><?php echo "3: Canvas, Moodle, Blackboard"; ?></code>
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
	// Colors: green #00B233, red #E53238, orange #FF9900; wms purple #543192, ROYGBIV: #CC0000, #ED5F21, #FAE300, #5B9C0A, #0A0D9C, #500A9C, #990A9C
	<?php echo $circleGraphic_js_builder; ?>
</script>
</body>
</html>
