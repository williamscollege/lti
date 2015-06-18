<?php
	/***********************************************
	 ** LTI Application: "Signup Sheets"
	 ** Purpose: This tool lets any user create a sheet with openings at specific times, and then allows other users to sign up for those openings.
	 ** Purpose: This is analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name:
	 ** Purpose: for example: signing up for a particular lab slot, scheduling office hours, picking a study group time, or more general things like planning a party.
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	require_once(dirname(__FILE__) . '/institution.cfg.php');
	require_once(dirname(__FILE__) . '/lang.cfg.php');
	require_once(dirname(__FILE__) . '/util.php');

	$pageTitle = ucfirst(util_lang('alert_error'));
?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?php echo $pageTitle . ' [' . LANG_APP_NAME . ']'; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="<?php echo LANG_APP_NAME; ?>">
		<meta name="author" content="Williams College OIT Project Group">
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
		<script src="<?php echo PATH_BOOTSTRAP_BOOTBOX_JS; ?>"></script>
		<script src="<?php echo PATH_JQUERY_VALIDATION_JS; ?>"></script>
		<script src="<?php echo PATH_JQUERY_PRINTAREA_JS; ?>"></script>
		<!-- local JS -->
		<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
	</head>
<body>

<div class="container"> <!--div closed in the footer-->

	<div id="content_container"> <!-- begin: div#content_container -->

		<h1><?php echo LANG_APP_NAME; ?></h1>
		<br />
		<?php
			// display screen message?
			if (isset($_REQUEST["success"])) {
				util_displayMessage('success', $_REQUEST["success"]);
			}
			elseif (isset($_REQUEST["failure"])) {
				util_displayMessage('error', $_REQUEST["failure"]);
			}
			elseif (isset($_REQUEST["info"])) {
				util_displayMessage('info', $_REQUEST["info"]);
			}
		?>

	</div>
	<!-- end: div#content_container -->
<?php
	require_once(dirname(__FILE__) . '/foot.php');
