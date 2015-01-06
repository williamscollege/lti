<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $pageTitle . ' [' . LANG_APP_NAME . ']'; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo LANG_APP_NAME; ?>">
	<meta name="author" content="OIT Project Group">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo PATH_JQUERYUI_CSS; ?>" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/WMS_bootstrap_PATCH.css" type="text/css" media="all">
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/wms-custom.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERYUI_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<script src="<?php echo PATH_BOOTSTRAP_JS; ?>"></script>
	<script src="<?php echo PATH_BOOTSTRAP_BOOTBOX_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERY_VALIDATION_JS; ?>"></script>
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
			<!-- TODO - IF entering directly from web (not from LTI), then show BRAND icon, below-->
			<!--			<a class="navbar-brand" href="#" style="padding: 0">-->
			<!--				<img alt="--><?php //echo LANG_INSTITUTION_NAME; ?><!--" src="img/williams-college-avatar-50.png" class="" />-->
			<!--			</a>-->
		</div>

		<?php
			// get parent referer url and querystring params
			$http_referer = $_SERVER['REQUEST_URI'];
		?>
		<div class="collapse navbar-collapse" id="wms-primary-navbar-1">
			<ul class="nav navbar-nav">
				<li class="<?php if (strpos($http_referer, "available_openings.php")) {
					echo "active";
				} ?>">
					<a id="link-available-openings" href="<?php echo APP_ROOT_PATH; ?>/app_code/available_openings.php">
						<i class="glyphicon glyphicon-pencil"></i>
						<b><?php echo ucfirst(util_lang('available_openings')); ?></b>
					</a>
				</li>
				<li class="<?php if (strpos($http_referer, "my_signups.php")) {
					echo "active";
				} ?>">
					<a id="link-my-signups" href="<?php echo APP_ROOT_PATH; ?>/app_code/my_signups.php">
						<i class="glyphicon glyphicon-list-alt"></i>
						<b><?php echo ucfirst(util_lang('my_signups')); ?></b>
					</a>
				</li>
				<li class="<?php if (strpos($http_referer, "my_sheets.php")) {
					echo "active";
				} ?>">
					<a id="link-my-signups" href="<?php echo APP_ROOT_PATH; ?>/app_code/my_sheets.php">
						<i class="glyphicon glyphicon-wrench"></i>
						<b><?php echo ucfirst(util_lang('my_sheets')); ?></b>
					</a>
				</li>
				<li class="<?php if (strpos($http_referer, "help.php")) {
					echo "active";
				} ?>">
					<a id="link-my-signups" href="<?php echo APP_ROOT_PATH; ?>/app_code/help.php">
						<i class="glyphicon glyphicon-question-sign"></i>
						<b><?php echo ucfirst(util_lang('help')); ?></b>
					</a>
				</li>

				<?php
					if ($IS_AUTHENTICATED) {
						# is system admin?
						if ($USER->flag_is_system_admin) {
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
				if ($IS_AUTHENTICATED) {
					?>
					<form id="frmSignout" class="navbar-form pull-right" method="post" action="<?php echo APP_ROOT_PATH; ?>/index.php">
						<span class="muted">Signed in: <a href="account_management.php" title="My Account"><?php echo $_SESSION['userdata']['username']; ?></a></span>.
						<input type="submit" id="submit_signout" class="btn" name="submit_signout" value="Sign out" />
					</form>
				<?php
				}
				else {
					//util_prePrintR($LANGUAGE);
					?>
					<form id="frmSignin" class="navbar-form pull-right" method="post" action="">
						<input type="text" id="username" class="span2" name="username" placeholder="<?php echo util_lang('username'); ?>" value="" />
						<input type="password" id="password_login" class="span2" name="password" placeholder="<?php echo util_lang('password'); ?>" value="" />
						<input type="submit" id="submit_signin" class="btn" name="submit_signin" value="<?php echo util_lang('app_sign_in_action'); ?>" />
					</form>
				<?php
				}
			?>
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
</body>