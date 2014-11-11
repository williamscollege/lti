<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo LANG_APP_NAME . ': ' . $pageTitle; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo LANG_APP_NAME; ?>">
	<meta name="author" content="OIT Project Group">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo PATH_JQUERYUI_CSS; ?>" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/WMS_bootstrap_PATCH.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERYUI_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<script src="<?php echo PATH_BOOTSTRAP_JS; ?>"></script>
	<script src="<?php echo PATH_BOOTSTRAP_BOOTBOX_JS; ?>"></script>
	<!-- local JS -->
	<script src="/digitalfieldnotebooks/js/digitalfieldnotebooks_util.js"></script>
</head>
<body>

<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<div class="nav-collapse collapse">
				<ul class="nav">
                    <li class="active"><a id="home-link" href="<?php echo APP_ROOT_PATH; ?>"><i class="icon-home icon-white"></i> <b><?php echo LANG_APP_NAME; ?></b></a></li>


                    <li class="active dropdown">
                        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="caret"></span> <b><?php echo ucfirst(util_lang('go_to')); ?></b></a>
                        <ul class="dropdown-menu">
                            <li><a id="nav-notebooks" href="<?php echo APP_ROOT_PATH; ?>/app_code/notebook.php?action=list"><?php echo ucfirst(util_lang('notebooks')); ?></a></li>
                            <li><a id="nav-metadata-structures" href="<?php echo APP_ROOT_PATH; ?>/app_code/metadata_structure.php?action=list"><?php echo ucfirst(util_lang('all_metadata')); ?></a></li>
                            <li><a id="nav-metadata-values" href="<?php echo APP_ROOT_PATH; ?>/app_code/metadata_term_set.php?action=list"><?php echo ucfirst(util_lang('all_metadata_term_sets')); ?></a></li>
                        </ul>
                    </li>

                    <li class="active"><a id="search-link" href="<?php echo APP_ROOT_PATH; ?>/app_code/search.php"><i class="icon-search icon-white"></i> <b><?php echo ucfirst(util_lang('search')); ?></b></a></li>

					<?php
						if ($IS_AUTHENTICATED) {
							# is system admin?
							if ($USER->flag_is_system_admin) {
								?>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-wrench icon-white"></i>
										Admin Only <b class="caret"></b></a>
									<ul class="dropdown-menu">
										<li><a href="admin_manage_users.php"><i class="icon-pencil"></i> Manage Users</a>
										</li>
										<li><a href="admin_manage_groups_courses.php"><i class="icon-pencil"></i> Manage
												LDAP Groups/Courses</a></li>
										<li class="divider"></li>
										<li><a href="admin_reports.php">Reports</a></li>
									</ul>
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
</div>

<div class="container"> <!--div closed in the footer-->
	<?php
		// display screen message?
		if (isset($_REQUEST["success"])) {
            util_displayMessage('success',$_REQUEST["success"]);
		}
		elseif (isset($_REQUEST["failure"])) {
            util_displayMessage('error',$_REQUEST["failure"]);
		}
		elseif (isset($_REQUEST["info"])) {
            util_displayMessage('info',$_REQUEST["info"]);
		}
	?>
