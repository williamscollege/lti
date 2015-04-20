<?php
	/**
	 * Created by JetBrains PhpStorm.
	 * User: cwarren, dwk2
	 * Date: 11/13/14
	 * Time: 10:20 AM
	 * To change this template use File | Settings | File Templates.
	 */
	require_once(dirname(__FILE__) . '/auth_base.class.php');
	require_once(dirname(__FILE__) . '/auth_LDAP.class.php');


	require_once(dirname(__FILE__) . '/term.class.php');
	require_once(dirname(__FILE__) . '/user.class.php');
	require_once(dirname(__FILE__) . '/course.class.php');
	require_once(dirname(__FILE__) . '/enrollment.class.php');
	require_once(dirname(__FILE__) . '/course_role.class.php');

	require_once(dirname(__FILE__) . '/sus_sheetgroup.class.php');
	require_once(dirname(__FILE__) . '/sus_sheet.class.php');
	require_once(dirname(__FILE__) . '/sus_opening.class.php');
	require_once(dirname(__FILE__) . '/sus_signup.class.php');
	require_once(dirname(__FILE__) . '/sus_access.class.php');

	require_once(dirname(__FILE__) . '/queued_message.class.php');

	require_once(dirname(__FILE__) . '/mailer_base.class.php');
	require_once(dirname(__FILE__) . '/mailer_php_standard.class.php');
	require_once(dirname(__FILE__) . '/mailer_testing.class.php');

