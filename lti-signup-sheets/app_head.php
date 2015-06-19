<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $pageTitle . ' [' . LANG_APP_NAME . ']'; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo LANG_APP_NAME; ?>">
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
	<script src="<?php echo PATH_BOOTSTRAP_BOOTBOX_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERY_VALIDATION_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERY_PRINTAREA_JS; ?>"></script>
	<!-- local JS -->
	<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
</head>
<body>

<div class="navbar navbar-inverse navbar-default" role="navigation">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#wms-primary-navbar-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>

		<?php
			// get parent referrer url and querystring params
			$http_referrer = $_SERVER['REQUEST_URI'];
		?>
		<div class="collapse navbar-collapse" id="wms-primary-navbar-1">
			<ul class="nav navbar-nav">
				<?php
					if ($IS_AUTHENTICATED) {
						?>
						<li class="<?php if (strpos($http_referrer, "signups_all.php")) {
							echo "active";
						} ?>">
							<a id="nav-link-my-signups" href="<?php echo APP_ROOT_PATH; ?>/app_code/signups_all.php">
								<i class="glyphicon glyphicon-list-alt"></i>
								<b><?php echo ucfirst(util_lang('signups_all')); ?></b>
							</a>
						</li>
						<li class="<?php if ((strpos($http_referrer, "sheet_openings_all.php")) || (strpos($http_referrer, "sheet_openings_signup.php"))) {
							echo "active";
						} ?>">
							<a id="nav-link-available-openings" href="<?php echo APP_ROOT_PATH; ?>/app_code/sheet_openings_all.php">
								<b><?php echo ucfirst(util_lang('sheet_openings_all')); ?></b>
							</a>
						</li>
						<li class="<?php if ((strpos($http_referrer, "sheets_all.php")) || (strpos($http_referrer, "sheets_edit_one.php"))) {
							echo "active";
						} ?>">
							<a id="nav-link-my-sheets" href="<?php echo APP_ROOT_PATH; ?>/app_code/sheets_all.php">
								<b><?php echo ucfirst(util_lang('sheets_all')); ?></b>
							</a>
						</li>
						<li class="<?php if (strpos($http_referrer, "help.php")) {
							echo "active";
						} ?>">
							<a id="nav-link-help" href="<?php echo APP_ROOT_PATH; ?>/app_code/help.php">
								<b><?php echo ucfirst(util_lang('help')); ?></b>
							</a>
						</li>

						<?php
						if ($USER->flag_is_system_admin) {
							// show special admin-only stuff
							?>
							<li class="">
								<a href="#" class="">Admin Only</a>
							</li>
						<?php
						}
					}
				?>
			</ul>
			<?php
				/*	// NOTE: Form based method of firing the app_setup.php and manually passing username and pwd values
					// NOTE: Use for LDAP authentication with auth_LDAP.class.php
					if ($IS_AUTHENTICATED) {
						*/ ?><!--
				<form id="frmSignout" class="navbar-form pull-right" method="post" action="<?php /*echo APP_ROOT_PATH; */ ?>/index.php">
					<a href="<?php /*echo APP_ROOT_PATH; */ ?>/app_code/my_account.php" title="My Account" class="wms_white"><?php /*echo htmlentities($_SESSION['userdata']['username'], ENT_QUOTES, 'UTF-8'); */ ?></a>&nbsp;
					<input type="submit" id="submit_signout" class="btn btn-default btn-sm" name="submit_signout" value="Sign out" />
				</form>
			<?php
				/*	}
					else {
						//util_prePrintR($LANGUAGE);
						*/ ?>
				<form id="frmSignin" class="navbar-form pull-right" method="post" action="">
					<input type="text" id="username" class="span2" name="username" maxlength="255" placeholder="<?php /*echo util_lang('username'); */ ?>" value="" />
					<input type="password" id="password_login" class="span2" name="password" maxlength="255" placeholder="<?php /*echo util_lang('password'); */ ?>" value="" />
					<input type="submit" id="submit_signin" class="btn btn-default" name="submit_signin" value="<?php /*echo util_lang('app_sign_in_action'); */ ?>" />
				</form>
			--><?php
				/*	}
				*/ ?>
		</div>
	</div>
</div>

<div class="container"> <!--div closed in the footer-->
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

