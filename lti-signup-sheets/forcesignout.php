<?php
//phpinfo();

	# DEBUGGING FILE -- SCRAP THIS FILE LATER

	require_once('app_setup.php');
	$pageTitle =  ucfirst(util_lang('home'));
	require_once('app_head.php');


	echo "hello signup sheets users.<br />";

	# Output an object wrapped with HTML PRE tags for pretty output
	function util_prePrintR($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
		return TRUE;
	}

	util_prePrintR($_POST);

	echo APP_ROOT_PATH; // dkc testing. remove
?>

<form id="frmSignout" class="navbar-form pull-right" method="post" action="<?php echo APP_ROOT_PATH; ?>/index.php">
	<span class="muted">Signed in: <a href="account_management.php" title="My Account"><?php echo $_SESSION['userdata']['username']; ?></a></span>.
	<input type="submit" id="submit_signout" class="btn" name="submit_signout" value="Sign out" />
</form>


<!--
<h2><?php /*echo LANG_INSTITUTION_NAME; */?></h2>

<h1><?php /*echo LANG_APP_NAME; */?></h1>

<br />

<p><?php /*echo util_lang('app_short_description'); */?></p>

<p><?php /*echo util_lang('app_sign_in_msg'); */?></p>-->