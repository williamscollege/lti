<?php
	/***********************************************
	 ** Project:    Dashboard for Automating Canvas Maintenance
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:
	 ** - Manage LTI tool consumers
	 ** - Regularly Sync Canvas Users (Amazon AWS) to Dashboard
	 ** - Push Avatar Uploads
	 ** - Set Notification Preferences
	 ** - SIS Uploads (error report)
	 ** - Course: Auto-Enroll Faculty
	 ** -
	 ** Current features:
	 **  - something
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, curl, mbyte, dom
	 ***********************************************/

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/include/connDB.php');
	require_once(dirname(__FILE__) . '/util.php');


	#------------------------------------------------#
	# SQL: fetch various column counts
	#	canvas_user_id (total count of Canvas users synced to dashboard_users)
	#	flag_is_set_avatar_image (set=1)
	#	flag_is_set_notification_preference (set=1)
	#------------------------------------------------#

	$queryUserFieldCounts = "
		SELECT
			 (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0) AS cnt_canvas_users
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_avatar_image` = 1) AS cnt_avatars_exist
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_avatar_image` = 0) AS cnt_avatars_missing
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_notification_preference` = 1) AS cnt_notif_pref_exist
			, (SELECT COUNT(*) FROM `dashboard_users` WHERE `flag_delete` = 0 AND `flag_is_set_notification_preference` = 0) AS cnt_notif_pref_missing
			, (SELECT COUNT(*) FROM `dashboard_eventlogs` WHERE `event_action` = 'sync_canvas_users_to_dashboard') AS cnt_logs_sync_canvas_users
			, (SELECT COUNT(*) FROM `lti_consumer` WHERE `enabled` = 1) AS cnt_lti_consumer_enabled
			, (SELECT COUNT(*) FROM `lti_consumer` WHERE `enabled` = 0) AS cnt_lti_consumer_disabled
		-- FROM
		-- `dashboard_users`;
	";
	$resultsUserFieldCounts = mysqli_query($connString, $queryUserFieldCounts) or
	die(mysqli_error($connString));

	# begin debugging
	/* Get field information for all fields */
	/*	while ($finfo = mysqli_fetch_field($resultsUserFieldCounts)) {
			echo "field: " . $finfo->name  . "<br />";
		}
		while ($row = mysqli_fetch_assoc($resultsUserFieldCounts)) {
			echo $row["cnt_canvas_users"] . "<br />";
			echo $row["cnt_avatars_exist"] . "<br />";
			echo $row["cnt_avatars_missing"] . "<br />";
			echo $row["cnt_notif_pref_exist"] . "<br />";
			echo $row["cnt_notif_pref_missing"] . "<br />";
			echo $row["cnt_lti_consumer_enabled"] . "<br />";
			echo $row["cnt_lti_consumer_disabled"] . "<br />";
		}*/
	# end debugging

	# Convert recordset to variables
	$rowCounts = mysqli_fetch_array($resultsUserFieldCounts);
	if ($rowCounts) {
		$cnt_canvas_users           = $rowCounts["cnt_canvas_users"];
		$cnt_avatars_exist          = $rowCounts["cnt_avatars_exist"];
		$cnt_avatars_missing        = $rowCounts["cnt_avatars_missing"];
		$cnt_notif_pref_exist       = $rowCounts["cnt_notif_pref_exist"];
		$cnt_notif_pref_missing     = $rowCounts["cnt_notif_pref_missing"];
		$cnt_logs_sync_canvas_users = $rowCounts["cnt_logs_sync_canvas_users"];
		$cnt_lti_consumer_enabled   = $rowCounts["cnt_lti_consumer_enabled"];
		$cnt_lti_consumer_disabled  = $rowCounts["cnt_lti_consumer_disabled"];

		// calculations (carefully avoid division by zero)
		if ($cnt_canvas_users == 0) {
			$percentSyncCanvasUsers   = 0;
			$percentPushAvatarUploads = 0;
			$percentSetNotifPrefs  = 0;
		}
		else {
			$percentSyncCanvasUsers   = round($cnt_canvas_users / $cnt_canvas_users * 100, PHP_ROUND_HALF_UP);
			$percentPushAvatarUploads = round($cnt_avatars_exist / $cnt_canvas_users * 100, PHP_ROUND_HALF_UP);
			$percentSetNotifPrefs  = round($cnt_notif_pref_exist / $cnt_canvas_users * 100, PHP_ROUND_HALF_UP);
		}
		// more calculations (carefully avoid division by zero)
		if ($cnt_lti_consumer_enabled == 0) {
			$percentLTIConsumers = 0;
		}
		else {
			$percentLTIConsumers = round($cnt_lti_consumer_enabled / ($cnt_lti_consumer_enabled) * 100, PHP_ROUND_HALF_UP);
		}
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
			<h1>
				<?php echo LANG_INSTITUTION_NAME_SHORT . " Glow: " . LTI_APP_NAME; ?>
				<small><br />Dashboard of processes that update Canvas LMS</small>
			</h1>
			<div id="breadCrumbs"><?php require_once(dirname(__FILE__) . '/include/breadcrumbs.php'); ?></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>LTI Tool Consumers</h3>

				<div class="circleGraphic1 col-md-9 col-sm-9">
					<span class="circleIntegerValue"><?php echo $percentLTIConsumers; ?></span>
				</div>
				<div class="wms-after-circle">
					<p>
						<strong>Count:</strong> <?php echo number_format($cnt_lti_consumer_enabled); ?>
						<small>(LTI applications)</small>
						<br />
						<strong>Last updated:</strong>
						<a href="https://github.com/williamscollege/lti" title="github (commits)" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;github
							(commits)</a><br />
						<strong>Schedule:</strong> manual code releases<br />
						<strong>Tools: </strong><a href="<?php echo APP_ROOT_PATH; ?>/lti_manage_tool_consumers.php" title="Manage LTI Tool Consumers (CRUD)">Manage
							LTI Tool Consumers (CRUD)</a>
					</p>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>SIS Uploads (success rate)</h3>

				<div class="circleGraphic2 col-md-9 col-sm-9">
					<span class="circleIntegerValue"><?php #echo  $percentXYZ; ?>0</span>
				</div>
				<div class="wms-after-circle">
					<p>
						<strong>Count:</strong> X / Y
						<small>(SIS uploads success ratio)</small>
						<br />
						<strong>Last updated:</strong> mm/dd/yyyy<br />
						<strong>Schedule:</strong> updated daily (cron)<br />
						<strong>Tools: </strong>
						<a href="<?php echo APP_ROOT_PATH; ?>/abc.php" title="Run now">Run now</a>&nbsp;&#124;
						<a href="<?php echo APP_ROOT_PATH; ?>/view_logs.php?action=XYZ" title="View logs">View logs (<?php #echo $cnt_logs_XYZ; ?>)</a>
					</p>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Sync Canvas to Dashboard</h3>

				<div class="circleGraphic3 col-md-9 col-sm-9">
					<span class="circleIntegerValue"><?php echo $percentSyncCanvasUsers; ?></span>
				</div>
				<div class="wms-after-circle">
					<p>
						<strong>Count:</strong> <?php echo number_format($cnt_canvas_users) . " / " . $cnt_canvas_users; ?>
						<small>(Canvas LMS users)</small>
						<br />
						<strong>Last updated:</strong> mm/dd/yyyy<br />
						<strong>Schedule:</strong> updated daily (cron)<br />
						<strong>Tools: </strong>
						<a href="<?php echo APP_ROOT_PATH; ?>/sync_canvas_users_to_dashboard.php" title="Run now">Run now</a>&nbsp;&#124;
						<a href="<?php echo APP_ROOT_PATH; ?>/view_logs.php?action=sync_canvas_users_to_dashboard" title="View logs">View logs
							(<?php echo $cnt_logs_sync_canvas_users; ?>)</a>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Push Avatar Uploads</h3>

				<div class="circleGraphic4 col-md-9 col-sm-9">
					<span class="circleIntegerValue"><?php echo $percentPushAvatarUploads; ?></span>
				</div>
				<div class="wms-after-circle">
					<p>
						<strong>Count:</strong> <?php echo number_format($cnt_avatars_exist) . " / " . number_format($cnt_canvas_users); ?>
						<small>(users have avatars in AWS Cloud)</small>
						<br />
						<strong>Last updated:</strong> mm/dd/yyyy<br />
						<strong>Schedule:</strong> updated daily (cron)<br />
						<strong>Tools: </strong>
						<a href="<?php echo APP_ROOT_PATH; ?>/abc.php" title="Run now">Run now</a>&nbsp;&#124;
						<a href="<?php echo APP_ROOT_PATH; ?>/view_logs.php?action=XYZ" title="View logs">View logs (<?php #echo $cnt_logs_XYZ; ?>)</a>
					</p>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Set Notification Preferences</h3>

				<div class="circleGraphic5 col-md-9 col-sm-9">
					<span class="circleIntegerValue"><?php echo $percentSetNotifPrefs; ?></span>
				</div>
				<div class="wms-after-circle">
					<p>
						<strong>Count:</strong> <?php echo number_format($cnt_notif_pref_exist) . " / " . number_format($cnt_canvas_users); ?>
						<small>(users had custom preferences set)</small>
						<br />
						<strong>Last updated:</strong> mm/dd/yyyy<br />
						<strong>Schedule:</strong> updated daily (cron)<br />
						<strong>Tools: </strong>
						<a href="<?php echo APP_ROOT_PATH; ?>/abc.php" title="Run now">Run now</a>&nbsp;&#124;
						<a href="<?php echo APP_ROOT_PATH; ?>/view_logs.php?action=XYZ" title="View logs">View logs (<?php #echo $cnt_logs_XYZ; ?>)</a>
					</p>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4">
			<div class="wmsBoxFull col-md-12 col-sm-12">
				<h3>Course: Auto-Enroll Faculty</h3>

				<div class="circleGraphic6 col-md-9 col-sm-9">
					<span class="circleIntegerValue"><?php #echo  $percentXYZ; ?>0</span>
				</div>
				<div class="wms-after-circle">
					<p>
						<strong>Count:</strong> X / Y
						<small>(Faculty in: "Faculty Funding Resources")</small>
						<br />
						<strong>Last updated:</strong> mm/dd/yyyy<br />
						<strong>Schedule:</strong> updated daily (cron)<br />
						<strong>Tools: </strong>
						<a href="<?php echo APP_ROOT_PATH; ?>/abc.php" title="Run now">Run now</a>&nbsp;&#124;
						<a href="<?php echo APP_ROOT_PATH; ?>/view_logs.php?action=XYZ" title="View logs">View logs (<?php #echo $cnt_logs_XYZ; ?>)</a>&nbsp;&#124;
						<a href="https://glow.williams.edu/courses/1549176" title="Glow: Faculty Funding Resources" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Glow
							Course</a>
					</p>
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
		// ROYGBIV: #CC0000, #ED5F21, #FAE300, #5B9C0A, #0A0D9C, #500A9C, #990A9C
		// (Original green #00B233, red #E53238, orange #FF9900)
		// (Williams Purple #543192)
		$('.circleGraphic1').circleGraphic({'color': '#CC0000'});
		$('.circleGraphic2').circleGraphic({'color': '#ED5F21'});
		$('.circleGraphic3').circleGraphic({'color': '#FAE300'});
		$('.circleGraphic4').circleGraphic({'color': '#5B9C0A'});
		$('.circleGraphic5').circleGraphic({'color': '#0A0D9C'});
		$('.circleGraphic6').circleGraphic({'color': '#500A9C'});
		$('.circleGraphic7').circleGraphic({'color': '#990A9C'});
	});
</script>
</body>
</html>
