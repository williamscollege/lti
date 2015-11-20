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
	 **  - Enable PHP modules: PDO, curl, mbyte, dom
	 ***********************************************/

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/include/connDB.php');
	require_once(dirname(__FILE__) . '/util.php');


	#------------------------------------------------#
	# SQL: fetch log file summaries for requested "event_action"
	#------------------------------------------------#

	$queryFetchLogs = "
		SELECT
			*
		FROM
			`dashboard_eventlogs`
		WHERE
			`event_action` = '" . $_REQUEST["action"] . "'
		ORDER BY
			`event_datetime` DESC;
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
			<h1>
				<?php echo LANG_INSTITUTION_NAME_SHORT . " Glow: " . LTI_APP_NAME; ?>
				<small><br />View Logs: &quot;<?php echo $_REQUEST["action"]; ?>&quot;</small>
			</h1>
			<div id="breadCrumbs"><?php require_once(dirname(__FILE__) . '/include/breadcrumbs.php'); ?></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<h3>Log Summary (descending)</h3><br />

			<?php
				while ($row = mysqli_fetch_assoc($resultsFetchLogs)) {
					echo "<p class=\"small\">";
					echo $row["event_dataset"];
					echo "<a href=\"" . APP_ROOT_PATH . "/" . $row["event_log_filepath"] . "\" title=\"View complete log file\" target=\"_blank\"><span class=\"glyphicon glyphicon-new-window\" aria-hidden=\"true\"></span>&nbsp;View complete log file</a>";
					echo "</p><br />";
				}
			?>
		</div>
	</div>
	<div class="row">
	</div> <!-- /.row -->

	<?php require_once(dirname(__FILE__) . '/include/foot.php'); ?>
</div> <!-- /.container -->
</body>
</html>
