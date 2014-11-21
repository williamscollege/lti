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
            (102,'tusr2','tusr2@williams.edu','tu2F','tu2L',NOW(),NOW(),0,0,0),
            (103,'tusr3','tusr3@williams.edu','tu3F','tu3L',NOW(),NOW(),0,0,0),
            (104,'tusr4','tusr4@williams.edu','tu4F','tu4L',NOW(),NOW(),0,0,0),
            (105,'tusr5','tusr5@williams.edu','tu5F','tu5L',NOW(),NOW(),0,0,0),
            (106,'tusr6','tusr6@williams.edu','tu6F','tu6L',NOW(),NOW(),1,0,0),
            (107,'tusr7','tusr7@williams.edu','tu7F','tu7L',NOW(),NOW(),0,1,0),
            (108,'tusr8','tusr8@williams.edu','tu8F','tu8L',NOW(),NOW(),0,0,1),
            (109,'tusr9','tusr9@williams.edu','tu9F','tu9L',NOW(),NOW(),0,0,0),
            (110,'tusr10','tusr10@williams.edu','tu10F','tuL',NOW(),NOW(),0,0,0)
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

	function createTestData_Terms($dbConn) {
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

	function createTestData_Courses($dbConn) {
		# course: 'course_id', 'course_idstr', 'short_name', 'long_name', 'account_idstr', 'term_idstr', 'flag_delete'
		$addTestSql  = "INSERT INTO " . Course::$dbTable . " VALUES
			(1, '15F-ARTH-101-01', '15F-ARTH-101-01 - Art History (Degas)', '15F-ARTH-101-01 - Art History (Degas)', 'courses', '15F', 0),
			(2, '15F-BIOL-101-01', '15F-BIOL-101-01 - Biology Intro (Fall Organisms)', '15F-BIOL-101-01 - Biology Intro (Fall Organisms)', 'courses', '15F', 0),
			(3, '15F-CHEM-101-01', '15F-CHEM-101-01 - Chemistry Compounds', '15F-CHEM-101-01 - Chemistry Compounds', 'courses', '15F', 0),
			(4, '15F-ECON-101-01', '15F-ECON-101-01 - Economy Introduction', '15F-ECON-101-01 - Economy Introduction', 'courses', '15F', 0),
			(5, '15F-ECON-201-01', '15F-ECON-201-01 - Economy: Depression Era to WW II', '15F-ECON-201-01 - Economy: Depression Era to WW II', 'courses', '15F', 0),
			(6, '15F-ECON-301-01', '15F-ECON-301-01 - Economy: Fed Chair Greenspan', '15F-ECON-301-01 - Economy: Fed Chair Greenspan', 'courses', '15F', 0),
			(7, '15F-ECON-301-02', '15F-ECON-301-02 - Economy: Fed Chair Volcker', '15F-ECON-301-02 - Economy: Fed Chair Volcker', 'courses', '15F', 0),
			(8, '15F-HIST-101-01', '15F-HIST-101-01 - History of the Revolutionary War', '15F-HIST-101-01 - History of the Revolutionary War', 'courses', '15F', 0),
			(9, '15F-MATH-101-01', '15F-MATH-101-01 - Math - Calculus Intro', '15F-MATH-101-01 - Math - Calculus Intro', 'courses', '15F', 0),
			(10, '15W-CHIN-101-01', '15W-CHIN-101-01 - Chinese in 30 Days', '15W-CHIN-101-01 - Chinese in 30 Days', 'courses', '15W', 0),
			(11, '15W-CSCI-101-01', '15W-CSCI-101-01 - Learn Python in 30 Days', '15W-CSCI-101-01 - Learn Python in 30 Days', 'courses', '15W', 0),
			(12, '15W-JAPN-101-01', '15W-JAPN-101-01 - Japanese in 30 Days', '15W-JAPN-101-01 - Japanese in 30 Days', 'courses', '15W', 0),
			(13, '15W-RELI-101-01', '15W-RELI-101-01 - Non-violent Religious Groups', '15W-RELI-101-01 - Non-violent Religious Groups', 'courses', '15W', 0),
			(14, '15W-UGDN-101-01', '15W-UGDN-101-01 - Trip to Uganda', '15W-UGDN-101-01 - Trip to Uganda', 'courses', '15W', 0),
			(15, '15S-ARTH-101-01', '15S-ARTH-101-01 - Art History (Hopper)', '15S-ARTH-101-01 - Art History (Hopper)', 'courses', '15S', 0),
			(16, '15S-BIOL-101-01', '15S-BIOL-101-01 - Biology Intro (Spring Organisms)', '15S-BIOL-101-01 - Biology Intro (Spring Organisms)', 'courses', '15S', 0),
			(17, '15S-CHEM-101-01', '15S-CHEM-101-01 - Chemistry Compounds', '15S-CHEM-101-01 - Chemistry Compounds', 'courses', '15S', 0),
			(18, '15S-ECON-101-01', '15S-ECON-101-01 - Economy Introduction', '15S-ECON-101-01 - Economy Introduction', 'courses', '15S', 0),
			(19, '15S-ECON-201-01', '15S-ECON-201-01 - Economy: Post WW II', '15S-ECON-201-01 - Economy: Post WW II', 'courses', '15S', 0),
			(20, '15S-ECON-301-01', '15S-ECON-301-01 - Economy: Fed Chair Yellen', '15S-ECON-301-01 - Economy: Fed Chair Yellen', 'courses', '15S', 0),
			(21, '15S-ECON-301-02', '15S-ECON-301-02 - Economy: Fed Chair Bernanke', '15S-ECON-301-02 - Economy: Fed Chair Bernanke', 'courses', '15S', 0),
			(22, '15S-HIST-101-01', '15S-HIST-201-01 - History of the Civil War', '15S-HIST-201-01 - History of the Civil War', 'courses', '15S', 0),
			(23, '15S-MATH-101-01', '15S-MATH-201-01 - Math - Calculus Intermediate', '15S-MATH-201-01 - Math - Calculus Intermediate', 'courses', '15S', 0)
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
		// 1100 series ids
		# enrollment: 'enrollment_id', 'course_idstr', 'user_id', 'course_role_name', 'section_id', 'flag_delete'
		$addTestSql  = "INSERT INTO " . Enrollment::$dbTable . " VALUES
			(1,'15F-ARTH-101-01', 101, 'teacher', '15F-ARTH-101-01', 0),
			(2,'15F-ARTH-101-01', 102, 'teacher', '15F-ARTH-101-01', 0),
			(3,'15F-ARTH-101-01', 103, 'student', '15F-ARTH-101-01', 0),
			(4,'15F-ARTH-101-01', 104, 'student', '15F-ARTH-101-01', 0),
			(5,'15F-ARTH-101-01', 105, 'student', '15F-ARTH-101-01', 0),
			(6,'15F-ARTH-101-01', 106, 'student', '15F-ARTH-101-01', 0),
			(7,'15F-ARTH-101-01', 107, 'student', '15F-ARTH-101-01', 1),
			(8,'15F-ARTH-101-01', 108, 'student', '15F-ARTH-101-01', 1),
			(9,'15F-ARTH-101-01', 109, 'student', '15F-ARTH-101-01', 0),
			(10,'15F-ARTH-101-01', 110, 'student', '15F-ARTH-101-01', 0),
			(11,'15F-BIOL-101-01', 101, 'teacher', '15F-BIOL-101-01', 0),
			(12,'15F-BIOL-101-01', 102, 'student', '15F-BIOL-101-01', 0),
			(13,'15F-BIOL-101-01', 103, 'student', '15F-BIOL-101-01', 0),
			(14,'15F-BIOL-101-01', 104, 'student', '15F-BIOL-101-01', 0),
			(15,'15F-CHEM-101-01', 104, 'student', '15F-CHEM-101-01', 0),
			(16,'15F-CHEM-101-01', 104, 'student', '15F-CHEM-101-01', 0)
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

	function makeAuthedTestUserAdmin($dbConn) {
		$u1                       = User::getOneFromDb(['username' => TESTINGUSER], $dbConn);
		$u1->flag_is_system_admin = TRUE;
		$u1->updateDb();
	}


	//--------------------------------------------------------------------------------------------------------------

	function createAllTestData($dbConn) {
		createTestData_Users($dbConn);
		createTestData_Terms($dbConn);
		createTestData_Courses($dbConn);
		createTestData_Enrollments($dbConn);

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

	function removeTestData_EXAMPLE($dbConn) {
		_removeTestDataFromTable($dbConn, Metadata_Structure::$dbTable);
	}

	//--------------------------------------------------------------------------------------------------------------

	function removeAllTestData($dbConn) {
		removeTestData_Users($dbConn);
		removeTestData_Terms($dbConn);
		removeTestData_Courses($dbConn);
		removeTestData_Enrollments($dbConn);

	}