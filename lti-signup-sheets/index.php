<?php
	/***********************************************
	 ** LTI Application: "Signup Sheets"
	 ** Purpose: This tool lets any user create a sheet with openings at specific times, and then allows other users to sign up for those openings.
	 ** Purpose: This is analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name:
	 ** Purpose: for example: signing up for a particular lab slot, scheduling office hours, picking a study group time, or more general things like planning a party.
	 ** Author: David Keiser-Clark, Williams College
	 ***********************************************/

	require_once(dirname(__FILE__) . '/app_setup.php');
	$pageTitle = ucfirst(util_lang('home'));
	require_once(dirname(__FILE__) . '/app_head.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated
		# redirect to signups page
		header('Location: ' . APP_ROOT_PATH . '/app_code/signups_all.php');

	}
	else {
		?>
		<div id="content_container"> <!-- begin: div#content_container -->

			<h1><?php echo LANG_APP_NAME; ?></h1>
			<br />

			<p><?php echo util_lang('app_short_description'); ?></p>

			<p><?php // echo util_lang('app_sign_in_msg'); ?></p>
		</div> <!-- end: div#content_container -->
	<?php
	}

	require_once(dirname(__FILE__) . '/foot.php');
?>