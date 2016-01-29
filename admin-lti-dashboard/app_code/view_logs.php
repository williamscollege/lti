<?php
	/***********************************************
	 ** Project:    Dashboard for Automating Canvas Maintenance
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:
	 ** - View log files from various processs
	 ** Current features:
	 **  - something
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, curl, mbyte, dom
	 ***********************************************/

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../include/connDB.php');
	require_once(dirname(__FILE__) . '/../util.php');


	#------------------------------------------------#
	# fetch variables for later use
	#------------------------------------------------#
	$limit_records    = 250;
	$sanitized_action = mysqli_real_escape_string($connString, $_REQUEST["action"]);


	#------------------------------------------------#
	# SQL Purpose: fetch log file summaries for requested "event_action"
	#------------------------------------------------#
	$queryFetchLogs = "
		SELECT
			*
		FROM
			`dashboard_eventlogs`
		WHERE
			`event_action` = '" . $sanitized_action . "'
		ORDER BY
			`event_datetime` DESC LIMIT " . $limit_records . ";
	";

	$resultsFetchLogs = mysqli_query($connString, $queryFetchLogs) or
	die(mysqli_error($connString));
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
	<!-- local JS -->
	<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="page-header">
			<h1><?php echo LTI_APP_NAME; ?></h1>
			<h5><?php echo LANG_INSTITUTION_NAME . ": View top " . $limit_records . " log records"; ?></h5>

			<div id="breadCrumbs" class="small"><?php require_once(dirname(__FILE__) . '/../include/breadcrumbs.php'); ?></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<h3>Log Summary: &quot;<?php echo $sanitized_action ?>&quot;</h3><br />

			<?php
				// iterate and show log results
				while ($row = mysqli_fetch_assoc($resultsFetchLogs)) {
					echo "<p class=\"small\">";
					echo $row["event_dataset_full"];
					echo "<strong>Status: " . $row["event_dataset_brief"] . "</strong><br />";
					if ($sanitized_action == "verify_sis_imports_into_canvas") {
						echo "<a href=\"" . APP_ROOT_PATH . "/app_code/view_parsed_import_id.php?id=" . $row["num_items"] . "\" title=\"View parsed import id\" target=\"_blank\"><span class=\"glyphicon glyphicon-eye-open\" aria-hidden=\"true\"></span>&nbsp;View parsed import id: " . $row["num_items"] . "</a>";
					}
					elseif ($sanitized_action == "upload_avatars_to_canvas_aws_cloud") {
						// provide no additional link
					}
					else {
						echo "<a href=\"" . APP_ROOT_PATH . $row["event_log_filepath"] . "\" title=\"View complete log file\" target=\"_blank\"><span class=\"glyphicon glyphicon-eye-open\" aria-hidden=\"true\"></span>&nbsp;View complete log file</a>";
					}
					echo "</p><br />";
				}
			?>
		</div>
	</div>
	<div class="row">
	</div> <!-- /.row -->

	<?php require_once(dirname(__FILE__) . '/../include/foot.php'); ?>
</div> <!-- /.container -->
</body>
</html>
