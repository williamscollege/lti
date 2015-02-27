<?php
	require_once('app_setup.php');
	$pageTitle = ucfirst(util_lang('home'));
	require_once('app_head.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

		echo '<br /><h3>You are Authenticated.</h3><br />';

		# is system admin?
		if ($USER->flag_is_system_admin) {
			// TODO: show special admin-only stuff
			echo '<br />You are a system admin.<br />';
		}

		util_prePrintR($USER);
		# TODO - if authenticated, then redirect to available openings?

	}
	else {
		?>
		<div class="hero-unit">
			<h1><?php echo LANG_APP_NAME; ?></h1>
			<br />

			<p><?php echo util_lang('app_short_description'); ?></p>

			<p><?php echo util_lang('app_sign_in_msg'); ?></p>
		</div>
	<?php
	}

	require_once('foot.php');
?>