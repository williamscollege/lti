<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('help'));
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		echo "<div id=\"content_container\">"; // begin: div#content_container
		?>
		<h2>Frequently Asked Questions</h2>

		<p>&nbsp;</p>
<p>
	<strong>Q:</strong> <a href="http://oit.williams.edu/itech/glow/how-do-i-create-signup-sheet/" target="_blank">How do I create a new signup sheet?</a><br>
	<strong>Q:</strong> <a href="http://oit.williams.edu/itech/glow/how-do-i-delete-and-edit-openings/" target="_blank">How do I delete and edit openings?</a><br>
	<strong>Q:</strong> <a href="http://oit.williams.edu/itech/glow/how-do-i-sign-up-others/" target="_blank">How do I sign up others?</a><br>
	<strong>Q:</strong> <a href="http://oit.williams.edu/itech/glow/how-do-i-print-a-signup-list/" target="_blank">How do I print a signup list?</a><br>
	<strong>Q:</strong> <a href="http://oit.williams.edu/itech/glow/how-do-i-delete-a-signup-sheet/" target="_blank">How do I delete a signup sheet that I created?</a><br>
	<strong>Q:</strong> <a href="http://oit.williams.edu/itech/glow/how-to-sign-up/" target="_blank">How do my students sign up for openings?</a>
</p>

		<p>&nbsp;</p>

		<h2>Help</h2>
		<p>&nbsp;</p>
		<p><i class="glyphicon glyphicon-question-sign"></i> More questions?</p>
		<?php
		if (isset($managersList)) {
			# TODO - need to implement this list from somewhere
			// show list of managers for this group
			echo "<p>Please contact: " . $managersList . "</p>";
		}
		else {
			# show default suypport address
			echo "<p>Please contact: <a href=\"mailto:itech@" . INSTITUTION_DOMAIN . "?subject=SignupSheets_Help_Request\" target=\"_blank\"><i class=\"glyphicon glyphicon-envelope\"></i> itech@" . INSTITUTION_DOMAIN . "</a></p>";
		}


		// NOTE: END placeholder text from old Moodle Signup Sheets help file
		echo "</div>"; // end: div#content_container
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once(dirname(__FILE__) . '/../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/help.js"></script>
