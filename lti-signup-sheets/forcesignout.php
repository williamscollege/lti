<?php
//phpinfo();

	# DEBUGGING FILE -- SCRAP THIS FILE LATER

	require_once('app_setup.php');
	$pageTitle =  ucfirst(util_lang('home'));
	require_once('app_head.php');


	echo "hello signup sheets users.<br />";

	util_prePrintR($_POST); // remove DEBUGGING

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


<li class="active">
	<a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="caret"></span> <b><?php echo ucfirst(util_lang('go_to')); ?></b></a>
	<ul class="dropdown-menu">
		<li>
			<a id="nav-notebooks" href="<?php echo APP_ROOT_PATH; ?>/app_code/notebook.php?action=list"><?php echo ucfirst(util_lang('available_openings')); ?></a>
		</li>
		<li>
			<a id="nav-metadata-structures" href="<?php echo APP_ROOT_PATH; ?>/app_code/metadata_structure.php?action=list"><?php echo ucfirst(util_lang('my_signups')); ?></a>
		</li>
		<li>
			<a id="nav-metadata-values" href="<?php echo APP_ROOT_PATH; ?>/app_code/metadata_term_set.php?action=list"><?php echo ucfirst(util_lang('sheet_admin')); ?></a>
		</li>
	</ul>
</li>