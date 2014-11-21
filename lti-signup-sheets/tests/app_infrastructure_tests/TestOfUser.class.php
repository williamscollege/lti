<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

	Mock::generate('Auth_Base');

	class TestOfUser extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);

			$this->auth           = new MockAuth_Base();
			$this->auth->username = Auth_Base::$TEST_USERNAME;
			$this->auth->email    = Auth_Base::$TEST_EMAIL;
			$this->auth->fname    = Auth_Base::$TEST_FNAME;
			$this->auth->lname    = Auth_Base::$TEST_LNAME;
			$this->auth->sortname = Auth_Base::$TEST_SORTNAME;
			$this->auth->msg      = '';
			$this->auth->debug    = '';
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testUserAtributesExist() {
			$this->assertEqual(count(User::$fields), 10);

			$this->assertTrue(in_array('user_id', User::$fields));
			$this->assertTrue(in_array('username', User::$fields));
			$this->assertTrue(in_array('email', User::$fields));
			$this->assertTrue(in_array('first_name', User::$fields));
			$this->assertTrue(in_array('last_name', User::$fields));
			$this->assertTrue(in_array('created_at', User::$fields));
			$this->assertTrue(in_array('updated_at', User::$fields));
			$this->assertTrue(in_array('flag_is_system_admin', User::$fields));
			$this->assertTrue(in_array('flag_is_banned', User::$fields));
			$this->assertTrue(in_array('flag_delete', User::$fields));
		}

		//// static methods

		function testCmp() {
			$u1 = new User(['user_id' => 50, 'username' => 'falb1', 'first_name' => 'Fred', 'last_name' => 'Albertson', 'DB' => $this->DB]);
			$u2 = new User(['user_id' => 50, 'username' => 'djon1', 'first_name' => 'David', 'last_name' => 'Jones', 'DB' => $this->DB]);
			$u3 = new User(['user_id' => 50, 'username' => 'jall1', 'first_name' => 'Jack L', 'last_name' => 'Allen', 'DB' => $this->DB]);
			$u4 = new User(['user_id' => 50, 'username' => 'jzow3', 'first_name' => 'Jack B', 'last_name' => 'Zowiski', 'DB' => $this->DB]);

			$this->assertEqual(User::cmp($u1, $u2), -1);
			$this->assertEqual(User::cmp($u1, $u1), 0);
			$this->assertEqual(User::cmp($u2, $u1), 1);
			$this->assertEqual(User::cmp($u3, $u4), -1);
		}

		//// DB interaction tests

		function testUserDBInsert() {
			$u = new User(['user_id' => 50, 'username' => 'falb1', 'first_name' => 'Fred', 'last_name' => 'Albertson', 'DB' => $this->DB]);

			$u->updateDb();

			$u2 = User::getOneFromDb(['user_id' => 50], $this->DB);

			$this->assertTrue($u2->matchesDb);
			$this->assertEqual($u2->username, 'falb1');
		}

		function testUserRetrievedFromDb() {
			$u = new User(['user_id' => 101, 'DB' => $this->DB]);
			$this->assertNull($u->username);

			$u->refreshFromDb();
			$this->assertEqual($u->username, Auth_Base::$TEST_USERNAME);
		}

		//// instance methods - object itself

		function testUserRenderMinimal() {
			$u = User::getOneFromDb(['user_id' => 101], $this->DB);

			$canonical = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101" data-user_full_name="' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '">' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '</div>';
			$rendered  = $u->renderMinimal();
			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
			$this->assertEqual($canonical, $rendered);

			$canonical = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101" data-user_full_name="' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '"><a href="' . APP_ROOT_PATH . '/app_code/user.php?user_id=101">' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '</a></div>';
			$rendered  = $u->renderMinimal(TRUE);
			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
			$this->assertEqual($canonical, $rendered);
		}

		//// instance methods - related data

		function testLoadCourseRoles() {
			$u1 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$u2 = User::getOneFromDb(['user_id' => 102], $this->DB);
			$u3 = new User(['user_id' => 50, 'username' => 'falb1', 'first_name' => 'Fred', 'last_name' => 'Albertson', 'DB' => $this->DB]);
			$u4 = User::getOneFromDb(['user_id' => 110], $this->DB);

			$u1->loadCourseRoles();
			$this->assertEqual(1, count($u1->course_roles));
			$this->assertEqual('teacher', $u1->course_roles[0]->course_role_name);

			$u2->loadCourseRoles();
			$this->assertEqual(2, count($u2->course_roles));
			$this->assertEqual('teacher', $u2->course_roles[0]->course_role_name);
			$this->assertEqual('student', $u2->course_roles[1]->course_role_name);

			$r3 = $u3->loadCourseRoles();
			$this->assertEqual(0, count($r3));

			$r4 = $u4->loadCourseRoles();
			$this->assertEqual(0, count($r4));
		}

		//// auth-related tests

		function testUserUpdatesBaseDbWhenValidAuthDataIsDifferent() {
			$u = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->assertEqual($u->username, Auth_Base::$TEST_USERNAME);
			$this->assertTrue($u->matchesDb);

			$this->auth->lname = 'Newlastname';

			$u->updateDbFromAuth($this->auth);

			$this->assertTrue($u->matchesDb);

			$u2 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->assertEqual($u2->username, Auth_Base::$TEST_USERNAME);
		}

		function testUserUpdatesBaseDbWhenAuthDataIsInvalid() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// should let caller/program know there's a problem
			$this->assertFalse($status);
		}

		function testNewUserBaseRecordCreatedWhenAuthDataIsForNewUser() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// should let caller/program know there's a problem
			$this->assertFalse($status);
		}

	}