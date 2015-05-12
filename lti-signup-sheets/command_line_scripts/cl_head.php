<?php

	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		echo 'no web access to this script';
		exit;
	}

	require_once(dirname(__FILE__) . '../institution.cfg.php');
	require_once(dirname(__FILE__) . '../lang.cfg.php');
	require_once(dirname(__FILE__) . '../util.php');

	require_once(dirname(__FILE__) . '../classes/db_linked.class.php');

	require_once(dirname(__FILE__) . '../term.class.php');
	require_once(dirname(__FILE__) . '../user.class.php');
	require_once(dirname(__FILE__) . '../course.class.php');
	require_once(dirname(__FILE__) . '../enrollment.class.php');
	require_once(dirname(__FILE__) . '../course_role.class.php');

	require_once(dirname(__FILE__) . '../sus_sheetgroup.class.php');
	require_once(dirname(__FILE__) . '../sus_sheet.class.php');
	require_once(dirname(__FILE__) . '../sus_opening.class.php');
	require_once(dirname(__FILE__) . '../sus_signup.class.php');
	require_once(dirname(__FILE__) . '../sus_access.class.php');

	require_once(dirname(__FILE__) . '../mailer_base.class.php');
	require_once(dirname(__FILE__) . '../mailer_php_standard.class.php');
	require_once(dirname(__FILE__) . '../mailer_testing.class.php');

	// Create database connection object
	$DB = util_createDbConnection();

