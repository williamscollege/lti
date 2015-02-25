<?php
	require_once dirname(__FILE__) . '/../classes/auth_base.class.php';
	require_once dirname(__FILE__) . '/../classes/auth_LDAP.class.php';

	require_once dirname(__FILE__) . '/../classes/ALL_CLASS_INCLUDES.php';
	/*
	This file contains a series of methods for creating known test data in a target database
	*/

	// NOTE !!!!!!!!!!!!!!!!!!!!
	// Actions and Roles are pre-populated and fixed - there is no creation nor removal of test data for those tables

	/*
	function createTestData_XXXX($dbConn) {
		// 1100 series ids
		# XXXX: user_id, username, email, first_name, last_name, created_at, updated_at, flag_is_system_admin, flag_is_banned, flag_delete
		$addTestSql  = "INSERT INTO " . XXXX::$dbTable . " VALUES
			(1,NOW(),NOW())
		";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test XXXX data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}
	*/
	$ACTIONS = array();


	function createTestData_Terms($dbConn) {
		// 1-20 series ids
		# term: 'term_id', 'term_idstr', 'name', 'start_date', 'end_date', 'flag_delete'
		$addTestSql  = "INSERT INTO " . Term::$dbTable . " VALUES
			(1, '14F', 'Fall 2013', '2013-09-05T00:00:00-05:00', '2013-12-16T00:00:00-05:00', 0),
			(2, '14W', 'Winter Study 2014', '2014-01-06T00:00:00-05:00', '2014-01-30T00:00:00-05:00', 0),
			(3, '14S', 'Spring 2014', '2014-02-06T00:00:00-05:00', '2014-05-26T00:00:00-05:00', 0),
			(4, '15F', 'Fall 2014', '2014-09-04T00:00:00-05:00', '2014-12-15T00:00:00-05:00', 0),
			(5, '15W', 'Winter Study 2015', '2015-01-05T00:00:00-05:00', '2015-01-29T00:00:00-05:00', 0),
			(6, '15S', 'Spring 2015', '2015-02-05T00:00:00-05:00', '2015-05-25T00:00:00-05:00', 0),
			(7, '16F', 'Fall 2015', '2015-09-10T00:00:00-05:00', '2015-12-21T00:00:00-05:00', 0),
			(8, '16W', 'Winter Study 2016', '2016-01-04T00:00:00-05:00', '2016-01-28T00:00:00-05:00', 0),
			(9, '16S', 'Spring 2016', '2016-02-04T00:00:00-05:00', '2016-05-23T00:00:00-05:00', 0),
			(10, '17F', 'Fall 2016', '2016-09-08T00:00:00-05:00', '2016-12-19T00:00:00-05:00', 0),
			(11, '17S', 'Spring 2017', '2017-02-02T00:00:00-05:00', '2017-05-22T00:00:00-05:00', 0),
			(12, '18F', 'Fall 2017', '2017-09-07T00:00:00-05:00', '2017-12-18T00:00:00-05:00', 0),
			(13, '18S', 'Spring 2018', '2018-02-01T00:00:00-05:00', '2018-05-21T00:00:00-05:00', 0),
			(14, '19F', 'Fall 2018', '2018-09-06T00:00:00-05:00', '2018-12-17T00:00:00-05:00', 0),
			(15, '19S', 'Spring 2019', '2019-01-31T00:00:00-05:00', '2019-05-20T00:00:00-05:00', 0)
        ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Terms data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Users($dbConn) {
		// 100 series ids
		# user: 'user_id', 'username', 'email', 'first_name', 'last_name', 'created_at', 'updated_at', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete'
		// 101-104 are
		// 105 is
		// 106-108 are
		// 109 has no roles (implicit 'public' role)
		// 110 is admin?
		$addTestSql  = "INSERT INTO " . User::$dbTable . " VALUES
            (101,'" . Auth_Base::$TEST_USERNAME . "','" . Auth_Base::$TEST_EMAIL . "','" . Auth_Base::$TEST_FNAME . "','" . Auth_Base::$TEST_LNAME . "',NOW(),NOW(),0,0,0),
            (102,'tusr2','tusr2@williams.edu','Tu2F','Tu2L',NOW(),NOW(),0,0,0),
            (103,'tusr3','tusr3@williams.edu','Tu3F','Tu3L',NOW(),NOW(),0,0,0),
            (104,'tusr4','tusr4@williams.edu','Tu4F','Tu4L',NOW(),NOW(),0,0,0),
            (105,'tusr5','tusr5@williams.edu','Tu5F','Tu5L',NOW(),NOW(),0,0,0),
            (106,'tusr6','tusr6@williams.edu','Tu6F','Tu6L',NOW(),NOW(),1,0,0),
            (107,'tusr7','tusr7@williams.edu','Tu7F','Tu7L',NOW(),NOW(),0,1,0),
            (108,'tusr8','tusr8@williams.edu','Tu8F','Tu8L',NOW(),NOW(),0,0,1),
            (109,'tusr9','tusr9@williams.edu','Tu9F','Tu9L',NOW(),NOW(),0,0,0),
            (110,'tusr10','tusr10@williams.edu','Tu10F','TuL',NOW(),NOW(),0,0,0)
        ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Users data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Courses($dbConn) {
		// 200 series ids
		# course: 'course_id', 'course_idstr', 'short_name', 'long_name', 'account_idstr', 'term_idstr', 'flag_delete'
		$addTestSql  = "INSERT INTO " . Course::$dbTable . " VALUES
			(201, '15F-ARTH-101-01', '15F-ARTH-101-01 - Art History (Degas)', '15F-ARTH-101-01 - Art History (Degas)', 'courses', '15F', 0),
			(202, '15F-BIOL-101-01', '15F-BIOL-101-01 - Biology Intro (Fall Organisms)', '15F-BIOL-101-01 - Biology Intro (Fall Organisms)', 'courses', '15F', 0),
			(203, '15F-CHEM-101-01', '15F-CHEM-101-01 - Chemistry Compounds', '15F-CHEM-101-01 - Chemistry Compounds', 'courses', '15F', 0),
			(204, '15F-ECON-101-01', '15F-ECON-101-01 - Economy Introduction', '15F-ECON-101-01 - Economy Introduction', 'courses', '15F', 0),
			(205, '15F-ECON-201-01', '15F-ECON-201-01 - Economy: Depression Era to WW II', '15F-ECON-201-01 - Economy: Depression Era to WW II', 'courses', '15F', 0),
			(206, '15F-ECON-301-01', '15F-ECON-301-01 - Economy: Fed Chair Greenspan', '15F-ECON-301-01 - Economy: Fed Chair Greenspan', 'courses', '15F', 0),
			(207, '15F-ECON-301-02', '15F-ECON-301-02 - Economy: Fed Chair Volcker', '15F-ECON-301-02 - Economy: Fed Chair Volcker', 'courses', '15F', 0),
			(208, '15F-HIST-101-01', '15F-HIST-101-01 - History of the Revolutionary War', '15F-HIST-101-01 - History of the Revolutionary War', 'courses', '15F', 0),
			(209, '15F-MATH-101-01', '15F-MATH-101-01 - Math - Calculus Intro', '15F-MATH-101-01 - Math - Calculus Intro', 'courses', '15F', 0),
			(210, '15W-CHIN-101-01', '15W-CHIN-101-01 - Chinese in 30 Days', '15W-CHIN-101-01 - Chinese in 30 Days', 'courses', '15W', 0),
			(211, '15W-CSCI-101-01', '15W-CSCI-101-01 - Learn Python in 30 Days', '15W-CSCI-101-01 - Learn Python in 30 Days', 'courses', '15W', 0),
			(212, '15W-JAPN-101-01', '15W-JAPN-101-01 - Japanese in 30 Days', '15W-JAPN-101-01 - Japanese in 30 Days', 'courses', '15W', 0),
			(213, '15W-RELI-101-01', '15W-RELI-101-01 - Non-violent Religious Groups', '15W-RELI-101-01 - Non-violent Religious Groups', 'courses', '15W', 0),
			(214, '15W-UGDN-101-01', '15W-UGDN-101-01 - Trip to Uganda', '15W-UGDN-101-01 - Trip to Uganda', 'courses', '15W', 0),
			(215, '15S-ARTH-101-01', '15S-ARTH-101-01 - Art History (Hopper)', '15S-ARTH-101-01 - Art History (Hopper)', 'courses', '15S', 0),
			(216, '15S-BIOL-101-01', '15S-BIOL-101-01 - Biology Intro (Spring Organisms)', '15S-BIOL-101-01 - Biology Intro (Spring Organisms)', 'courses', '15S', 0),
			(217, '15S-CHEM-101-01', '15S-CHEM-101-01 - Chemistry Compounds', '15S-CHEM-101-01 - Chemistry Compounds', 'courses', '15S', 0),
			(218, '15S-ECON-101-01', '15S-ECON-101-01 - Economy Introduction', '15S-ECON-101-01 - Economy Introduction', 'courses', '15S', 0),
			(219, '15S-ECON-201-01', '15S-ECON-201-01 - Economy: Post WW II', '15S-ECON-201-01 - Economy: Post WW II', 'courses', '15S', 0),
			(220, '15S-ECON-301-01', '15S-ECON-301-01 - Economy: Fed Chair Yellen', '15S-ECON-301-01 - Economy: Fed Chair Yellen', 'courses', '15S', 0),
			(221, '15S-ECON-301-02', '15S-ECON-301-02 - Economy: Fed Chair Bernanke', '15S-ECON-301-02 - Economy: Fed Chair Bernanke', 'courses', '15S', 0),
			(222, '15S-HIST-101-01', '15S-HIST-201-01 - History of the Civil War', '15S-HIST-201-01 - History of the Civil War', 'courses', '15S', 0),
			(223, '15S-MATH-101-01', '15S-MATH-201-01 - Math - Calculus Intermediate', '15S-MATH-201-01 - Math - Calculus Intermediate', 'courses', '15S', 0)
        ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Course data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Enrollments($dbConn) {
		// 400 series ids
		# enrollment: 'enrollment_id', 'course_idstr', 'user_id', 'course_role_name', 'section_idstr', 'flag_delete'
		$addTestSql  = "INSERT INTO " . Enrollment::$dbTable . " VALUES
			(401,'15F-ARTH-101-01', 101, 'teacher', '15F-ARTH-101-01', 0),
			(402,'15F-ARTH-101-01', 102, 'teacher', '15F-ARTH-101-01', 0),
			(403,'15F-ARTH-101-01', 103, 'student', '15F-ARTH-101-01', 0),
			(404,'15F-ARTH-101-01', 104, 'student', '15F-ARTH-101-01', 0),
			(405,'15F-ARTH-101-01', 105, 'student', '15F-ARTH-101-01', 0),
			(406,'15F-ARTH-101-01', 106, 'student', '15F-ARTH-101-01', 0),
			(407,'15F-ARTH-101-01', 107, 'student', '15F-ARTH-101-01', 1),
			(408,'15F-ARTH-101-01', 108, 'student', '15F-ARTH-101-01', 1),
			(409,'15F-ARTH-101-01', 109, 'student', '15F-ARTH-101-01', 0),
			(410,'15F-ARTH-101-01', 110, 'student', '15F-ARTH-101-01', 0),
			(411,'15F-BIOL-101-01', 101, 'teacher', '15F-BIOL-101-01', 0),
			(412,'15F-BIOL-101-01', 102, 'student', '15F-BIOL-101-01', 0),
			(413,'15F-BIOL-101-01', 103, 'student', '15F-BIOL-101-01', 0),
			(414,'15F-BIOL-101-01', 104, 'student', '15F-BIOL-101-01', 0),
			(415,'15F-CHEM-101-01', 104, 'student', '15F-CHEM-101-01', 0),
			(416,'15F-MATH-101-01', 104, 'student', '15F-MATH-101-01', 0)
    ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Enrollments data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_SUS_Sheetgroups($dbConn) {
		// 500 series ids
		# SUS_Sheetgroup: 'sheetgroup_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'flag_is_default', 'name', 'description', 'max_g_total_user_signups', 'max_g_pending_user_signups'
		$addTestSql  = "INSERT INTO " . SUS_Sheetgroup::$dbTable . " VALUES
			(501, NOW(), NOW(), 0, 101, 1, 'Sheetgroup 501', 'Something to organize my math sheets', 10, 2),
			(502, NOW(), NOW(), 0, 101, 0, 'Sheetgroup 502', 'Something to organize my english sheets', 4, 2),
			(503, NOW(), NOW(), 0, 101, 0, 'Sheetgroup 503', 'Something to organize my spanish sheets', 6, 3),
			(504, NOW(), NOW(), 0, 102, 0, 'Sheetgroup 504', 'Help me keep track of so many sheets', 1, 1),
			(505, NOW(), NOW(), 0, 102, 1, 'Sheetgroup 505', 'Something to help me organize', 1, 1),
			(506, NOW(), NOW(), 0, 103, 1, 'Sheetgroup 506', 'Something to help me organize', -1, -1),
			(507, NOW(), NOW(), 0, 104, 1, 'Sheetgroup 507', 'Something to help me organize', -1, -1),
			(508, NOW(), NOW(), 0, 105, 0, 'Sheetgroup 508', 'Something to help me organize', -1, -1),
			(509, NOW(), NOW(), 0, 106, 1, 'Sheetgroup 509', 'Something to help me organize', -1, -1),
			(510, NOW(), NOW(), 0, 109, 1, 'Sheetgroup 510', 'Something to help me organize', -1, -1)
    ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test SUS_Sheetgroups data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_SUS_Sheets($dbConn) {
		// 600 series ids
		# SUS_Sheet: 'sheet_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'sheetgroup_id', 'name', 'description',
		# 'type', 'date_opens', 'date_closes', 'max_total_user_signups', 'max_pending_user_signups', 'flag_alert_owner_change', 'flag_alert_owner_signup',
		# 'flag_alert_owner_imminent', 'flag_alert_admin_change', 'flag_alert_admin_signup', 'flag_alert_admin_imminent', 'flag_private_signups'
		$addTestSql  = "INSERT INTO " . SUS_Sheet::$dbTable . " VALUES
			(601, NOW(), NOW(), 0, 101, 501, 'Sheet 601', 'Sheet 601, Sheetgroup 501', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 0, 0, 0, 0, 0),
			(602, NOW(), NOW(), 0, 101, 501, 'Sheet 602', 'Sheet 602, Sheetgroup 501', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 2, 3, 0, 0, 0, 0, 0, 0, 0),
			(603, NOW(), NOW(), 0, 101, 501, 'Sheet 603', 'Sheet 603, Sheetgroup 501', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 4, 6, 1, 0, 0, 0, 0, 0, 0),
			(604, NOW(), NOW(), 0, 101, 502, 'Sheet 604', 'Sheet 604, Sheetgroup 502', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 1, 0, 0, 0, 0, 0),
			(605, NOW(), NOW(), 0, 101, 503, 'Sheet 605', 'Sheet 605, Sheetgroup 503', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 1, 0, 0, 0, 0),
			(606, NOW(), NOW(), 0, 102, 504, 'Sheet 606', 'Sheet 606, Sheetgroup 504', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 0, 1, 0, 0, 0),
			(607, NOW(), NOW(), 0, 102, 504, 'Sheet 607', 'Sheet 607, Sheetgroup 504', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 0, 0, 1, 0, 0),
			(608, NOW(), NOW(), 0, 103, 506, 'Sheet 608', 'Sheet 608, Sheetgroup 506', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 0, 0, 0, 1, 0),
			(609, NOW(), NOW(), 0, 104, 507, 'Sheet 609', 'Sheet 609, Sheetgroup 506', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 0, 0, 0, 0, 1),
			(610, NOW(), NOW(), 0, 109, 510, 'Sheet 610', 'Sheet 610, Sheetgroup 510', 'timeblocks', NOW(), TIMESTAMPADD(month,1,NOW()), 1, -1, 0, 0, 0, 0, 0, 0, 0)
    ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test SUS_Sheet data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_SUS_Openings($dbConn) {
		// 700 series ids
		# SUS_Opening: 'opening_id', 'created_at', 'updated_at', 'flag_delete', 'sheet_id', 'opening_group_id', 'name', 'description',
		# 'max_signups', 'admin_comment', 'begin_datetime', 'end_datetime', 'location'
		$addTestSql  = "INSERT INTO " . SUS_Opening::$dbTable . " VALUES
			(701, NOW(), NOW(), 0, 601, 0, 'Opening 701', 'Opening 701, Sheet 601, Sheetgroup 501', 8 , 'opening admin comment', TIMESTAMPADD(hour,4,NOW()),  TIMESTAMPADD(hour,5,NOW()), 'opening location at CET 256'),
			(702, NOW(), NOW(), 0, 601, 0, 'Opening 702', 'Opening 702, Sheet 601, Sheetgroup 501', 2 , 'no comment', TIMESTAMPADD(day,-4,NOW()),  TIMESTAMPADD(hour,25,NOW()), 'CET MakerSpace'),
			(703, NOW(), NOW(), 0, 602, 0, 'Opening 703', 'Opening 703, Sheet 602, Sheetgroup 501', 2 , 'no comment', TIMESTAMPADD(week,1,NOW()),  TIMESTAMPADD(hour,1,NOW()), ''),
			(704, NOW(), NOW(), 0, 602, 0, 'Opening 704', 'Opening 704, Sheet 602, Sheetgroup 501', 2 , '', TIMESTAMPADD(hour,1,NOW()),  TIMESTAMPADD(hour,2,NOW()), ''),
			(705, NOW(), NOW(), 0, 603, 0, 'Opening 705', 'Opening 705, Sheet 603, Sheetgroup 501', 4 , '', TIMESTAMPADD(hour,22,NOW()),  TIMESTAMPADD(hour,23,NOW()), ''),
			(706, NOW(), NOW(), 0, 604, 0, 'Opening 706', 'Opening 706, Sheet 604, Sheetgroup 502', 1 , '', NOW(),  TIMESTAMPADD(hour,1,NOW()), 'Faculty House'),
			(707, NOW(), NOW(), 1, 605, 0, 'Opening 707', 'Opening 707, Sheet 605, Sheetgroup 503', 1 , '', NOW(),  TIMESTAMPADD(hour,1,NOW()), 'Purple Pub'),
			(708, NOW(), NOW(), 1, 606, 0, 'Opening 708', 'Opening 708, Sheet 606, Sheetgroup 504', 1 , '', NOW(),  TIMESTAMPADD(hour,1,NOW()), ''),
			(709, NOW(), NOW(), 0, 607, 0, 'Opening 709', 'Opening 709, Sheet 607, Sheetgroup 504', 1 , '', NOW(),  TIMESTAMPADD(hour,1,NOW()), ''),
			(710, NOW(), NOW(), 0, 610, 0, 'Opening 710', 'Opening 710, Sheet 610, Sheetgroup 510', 1 , '', NOW(),  TIMESTAMPADD(hour,1,NOW()), '')
    ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test SUS_Opening data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_SUS_Signups($dbConn) {
		// 800 series ids
		# SUS_Signup: 'signup_id', 'created_at', 'updated_at', 'flag_delete', 'opening_id', 'signup_user_id', 'admin_comment'
		$addTestSql  = "INSERT INTO " . SUS_Signup::$dbTable . " VALUES
			(801, NOW(), NOW(), 0, 701, 101, 'signup admin comment'),
			(802, TIMESTAMPADD(day,1,NOW()), TIMESTAMPADD(day,1,NOW()), 0, 704, 101, 'no comment'),
			(803, TIMESTAMPADD(day,2,NOW()), TIMESTAMPADD(day,3,NOW()), 0, 705, 101, 'no comment'),
			(804, NOW(), NOW(), 0, 701, 102, ''),
			(805, NOW(), NOW(), 0, 702, 102, ''),
			(806, NOW(), NOW(), 0, 701, 103, ''),
			(807, NOW(), NOW(), 1, 708, 103, ''),
			(808, NOW(), NOW(), 1, 701, 104, ''),
			(809, NOW(), NOW(), 0, 702, 104, ''),
			(810, NOW(), NOW(), 0, 703, 104, ''),
			(811, NOW(), NOW(), 0, 704, 104, ''),
			(812, NOW(), NOW(), 1, 705, 104, ''),
			(813, NOW(), NOW(), 0, 701, 105, ''),
			(814, NOW(), NOW(), 0, 710, 105, ''),
			(815, NOW(), NOW(), 1, 707, 106, ''),
			(816, NOW(), NOW(), 0, 708, 106, ''),
			(817, NOW(), NOW(), 1, 705, 107, ''),
			(818, NOW(), NOW(), 1, 706, 107, ''),
			(819, NOW(), NOW(), 0, 709, 108, ''),
			(820, NOW(), TIMESTAMPADD(day,1,NOW()), 0, 710, 109, '')
    ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test SUS_Signup data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_SUS_Access($dbConn) {
		// 900 series ids
		# SUS_Access: 'access_id', 'created_at', 'updated_at', 'sheet_id', 'type', 'constraint_id', 'constraint_data', 'broadness'
		$addTestSql  = "INSERT INTO " . SUS_Access::$dbTable . " VALUES
			(901, NOW(), NOW(), 601, 'adminbyuser', 0, 'tusr3', 1),
			(902, NOW(), NOW(), 601, 'byuser', 0, 'tusr4', 10),
			(903, NOW(), NOW(), 601, 'bycourse', 0, '15F-ARTH-101-01', 20),
			(904, NOW(), NOW(), 601, 'byinstr', 101, '', 30),
			(905, NOW(), NOW(), 601, 'bygradyear', 18, '', 50),
			(906, NOW(), NOW(), 601, 'byrole', 0, 'teacher', 60),
			(907, NOW(), NOW(), 601, 'byhasaccount', 0, 'all', 60),
			(908, NOW(), NOW(), 607, 'byuser', 0, 'mockUserJBond', 1),
			(909, NOW(), NOW(), 608, 'adminbyuser', 0, 'tusr9', 1),
			(910, NOW(), NOW(), 602, 'byrole', 0, 'teacher', 60),
			(911, NOW(), NOW(), 601, 'byuser', 0, 'tusr5', 10),
			(912, NOW(), NOW(), 601, 'byrole', 0, 'student', 60)
    ";
		$addTestStmt = $dbConn->prepare($addTestSql);
		$addTestStmt->execute();
		if ($addTestStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test SUS_Access data to the DB\n";
			print_r($addTestStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function makeAuthedTestUserAdmin($dbConn) {
		$u1                       = User::getOneFromDb(['username' => TESTINGUSER], $dbConn);
		$u1->flag_is_system_admin = TRUE;
		$u1->updateDb();
	}

	//--------------------------------------------------------------------------------------------------------------

	function createAllTestData($dbConn) {
		createTestData_Terms($dbConn);
		createTestData_Users($dbConn);
		createTestData_Courses($dbConn);
		createTestData_Enrollments($dbConn);
		createTestData_SUS_Sheetgroups($dbConn);
		createTestData_SUS_Sheets($dbConn);
		createTestData_SUS_Openings($dbConn);
		createTestData_SUS_Signups($dbConn);
		createTestData_SUS_Access($dbConn);

		//        $all_actions = Action::getAllFromDb([],$dbConn);
		//        global $ACTIONS;
		//        foreach ($all_actions as $a) {
		//            $ACTIONS[$a->name] = $a;
		//        }
	}

	//--------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------

	function _removeTestDataFromTable($dbConn, $tableName) {
		# This preserves specific test data
		$sql = "DELETE FROM $tableName";
		//echo "<pre>" . $sql . "\n</pre>";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

	function removeTestData_Users($dbConn) {
		# This preserves specific test data
		$sql  = "DELETE FROM " . User::$dbTable . " WHERE " . User::$primaryKeyField . " > 1";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

	function removeTestData_Terms($dbConn) {
		_removeTestDataFromTable($dbConn, Term::$dbTable);
	}

	function removeTestData_Courses($dbConn) {
		_removeTestDataFromTable($dbConn, Course::$dbTable);
	}

	function removeTestData_Enrollments($dbConn) {
		_removeTestDataFromTable($dbConn, Enrollment::$dbTable);
	}

	function removeTestData_SUS_Sheetgroups($dbConn) {
		_removeTestDataFromTable($dbConn, SUS_Sheetgroup::$dbTable);
	}

	function removeTestData_SUS_Sheets($dbConn) {
		_removeTestDataFromTable($dbConn, SUS_Sheet::$dbTable);
	}

	function removeTestData_SUS_Openings($dbConn) {
		_removeTestDataFromTable($dbConn, SUS_Opening::$dbTable);
	}

	function removeTestData_SUS_Signups($dbConn) {
		_removeTestDataFromTable($dbConn, SUS_Signup::$dbTable);
	}

	function removeTestData_SUS_Access($dbConn) {
		_removeTestDataFromTable($dbConn, SUS_Access::$dbTable);
	}

	function removeTestData_EXAMPLE($dbConn) {
		_removeTestDataFromTable($dbConn, Metadata_Structure::$dbTable);
	}

	//--------------------------------------------------------------------------------------------------------------

	function removeAllTestData($dbConn) {
		removeTestData_Terms($dbConn);
		removeTestData_Users($dbConn);
		removeTestData_Courses($dbConn);
		removeTestData_Enrollments($dbConn);
		removeTestData_SUS_Sheetgroups($dbConn);
		removeTestData_SUS_Sheets($dbConn);
		removeTestData_SUS_Openings($dbConn);
		removeTestData_SUS_Signups($dbConn);
		removeTestData_SUS_Access($dbConn);
	}