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
	$sanitized_id = is_numeric($_REQUEST["id"]) ? mysqli_real_escape_string($connString, $_REQUEST["id"]) : 0;


	#------------------------------------------------#
	# SQL Purpose: fetch log file summaries for requested "event_action"
	#------------------------------------------------#
	$queryFetchLogs = "
		SELECT
			*
		FROM
			`dashboard_sis_imports_parsed`
		WHERE
			`id` = '" . $sanitized_id . "'
		ORDER BY
			`cronjob_datetime` DESC LIMIT 1;
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
			<h5><?php echo LANG_INSTITUTION_NAME . ": View one parsed import id"; ?></h5>

			<div id="breadCrumbs" class="small"><?php require_once(dirname(__FILE__) . '/../include/breadcrumbs.php'); ?></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<h3>SIS Import id: <?php echo $sanitized_id; ?></h3>

			<?php
				// show link to Canvas (requires admin authentication via live login session)
				echo "<p class=\"small wms_indent\"><a href=\"https://glow.williams.edu/api/v1/accounts/98616/sis_imports/" . $sanitized_id . "\" title=\"Canvas: View import id\" target=\"_blank\"><span class=\"glyphicon glyphicon-new-window\" aria-hidden=\"true\"></span>&nbsp;Canvas: View import id: " . $sanitized_id . "</a></p>";
				echo "<p class=\"small wms_indent\"><a href=\"https://glow.williams.edu/api/v1/accounts/98616/sis_imports\" title=\"Canvas: View most current 10 import id reports\" target=\"_blank\"><span class=\"glyphicon glyphicon-new-window\" aria-hidden=\"true\"></span>&nbsp;Canvas: View most current 10 import id reports</a></p>";

				while ($row = mysqli_fetch_assoc($resultsFetchLogs)) {
					util_prePrintR($row);
					// echo "<table><tbody>";
					// foreach ($row as $field => $value) {
					// 	 echo "<tr><th>" . $field . "</th><td>" . $value . "</td></tr>";
					// }
					// echo "</tbody></table>";
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
