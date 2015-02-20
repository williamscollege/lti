<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_account'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div id=\"parent_container\">"; // start: div#parent_container
		echo "<h3>" . $pageTitle . "</h3>";
		echo "<p>&nbsp;</p>";

		// ***************************
		// do something
		// ***************************

		echo "TBD: Hook for preference settings...";

	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/my_available_openings.js"></script>
