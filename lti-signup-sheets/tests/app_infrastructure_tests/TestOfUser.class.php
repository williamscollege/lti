<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');
	require_once(dirname(__FILE__) . '/../../classes/auth_base.class.php');

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
			$this->assertEqual(count(User::$fields), 12);

			$this->assertTrue(in_array('user_id', User::$fields));
			$this->assertTrue(in_array('canvas_user_id', User::$fields));
			$this->assertTrue(in_array('sis_user_id', User::$fields));
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
			//$u = new User(['user_id' => 50, 'username' => 'falb1', 'first_name' => 'Fred', 'last_name' => 'Albertson', 'DB' => $this->DB]);
			$u = new User(['user_id' => 50, 'canvas_user_id' => 0, 'sis_user_id' => 0, 'username' => 'falb1', 'DB' => $this->DB]);

			$u->updateDb();

			$u2 = User::getOneFromDb(['user_id' => 50], $this->DB);

			$this->assertTrue($u2->matchesDb);
			$this->assertEqual($u2->username, 'falb1');
		}

		function testUserRetrievedFromDb() {
			$u1 = new User(['user_id' => 101, 'DB' => $this->DB]);
			$u2 = new User(['user_id' => 102, 'DB' => $this->DB]);

			$this->assertEqual('mockUserJBond', $u1->username);
			$this->assertEqual('tusr2', $u2->username);

			$u1->refreshFromDb();
			$this->assertEqual($u1->username, Auth_Base::$TEST_USERNAME);
		}

		//// instance methods - object itself

		function testUserRenderMinimal() {
			$u = User::getOneFromDb(['user_id' => 101], $this->DB);

			$canonical = '<div class="rendered-object" data-for-user_id="101" data-user_full_name="' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '">' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '</div>';
			$rendered  = $u->renderMinimal();
			$this->assertEqual($canonical, $rendered);

			$canonical = '<div class="rendered-object" data-for-user_id="101" data-user_full_name="' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '"><a href="' . APP_ROOT_PATH . '/app_code/user.php?user_id=101">' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '</a></div>';
			$rendered  = $u->renderMinimal(TRUE);
			$this->assertEqual($canonical, $rendered);
		}

		//// instance methods - related data

		function testCacheEnrollments() {
			$u1 = User::getOneFromDb(['user_id' => 104], $this->DB);
			$u1->cacheEnrollments();
			$this->assertTrue($u1->matchesDb);

			$this->assertEqual(4, count($u1->enrollments));
		}

		function testLoadEnrollments() {
			$u1 = User::getOneFromDb(['user_id' => 104], $this->DB);
			$u1->loadEnrollments();
			$this->assertTrue($u1->matchesDb);

			$this->assertEqual(4, count($u1->enrollments));
		}

		function testCacheSheetgroups() {
			$u1 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$u2 = User::getOneFromDb(['user_id' => 102], $this->DB);
			$u3 = new User(['user_id' => 50, 'username' => 'falb1', 'first_name' => 'Fred', 'last_name' => 'Albertson', 'DB' => $this->DB]);
			$u4 = User::getOneFromDb(['user_id' => 109], $this->DB);

			$u1->cacheSheetgroups();
			$this->assertEqual(3, count($u1->sheetgroups));
			$this->assertEqual('Sheetgroup 501', $u1->sheetgroups[0]->name);

			$u2->cacheSheetgroups();
			$this->assertEqual(2, count($u2->sheetgroups));
			$this->assertEqual('Sheetgroup 504', $u2->sheetgroups[0]->name);
			$this->assertEqual('Sheetgroup 505', $u2->sheetgroups[1]->name);

			$u3->cacheSheetgroups();
			$this->assertEqual(0, count($u3->sheetgroups));

			$u4->cacheSheetgroups();
			$this->assertEqual(1, count($u4->sheetgroups));
		}

		function testCacheManagedSheets() {
			$u1 = User::getOneFromDb(['user_id' => 101], $this->DB);

			$u1->cacheManagedSheets();
			$this->assertEqual(1, count($u1->managed_sheets));
			$this->assertEqual(608, $u1->managed_sheets[0]->sheet_id);
		}

		function testCacheMySignups() {
			$u1 = User::getOneFromDb(['user_id' => 101], $this->DB);

			$u1->cacheMySignups();

			$this->assertEqual(3, count($u1->signups_all));
			// note hash notation (instead of object property)
			$this->assertEqual(704, $u1->signups_all[0]['opening_id']);
			$this->assertEqual(701, $u1->signups_all[1]['opening_id']);
			$this->assertEqual(705, $u1->signups_all[2]['opening_id']);
		}

		function testCacheSignupsOnMySheets() {
			$u1 = User::getOneFromDb(['user_id' => 101], $this->DB);

			$u1->cacheSignupsOnMySheets();

			// count # of openings
			$this->assertEqual(5, count($u1->signups_on_my_sheets));
			$this->assertEqual(702, $u1->signups_on_my_sheets[0]['opening_id']);
			$this->assertEqual(704, $u1->signups_on_my_sheets[1]['opening_id']);
			$this->assertEqual(701, $u1->signups_on_my_sheets[2]['opening_id']);

			// count # of signups in one opening
			$this->assertEqual(4, count($u1->signups_on_my_sheets[2]['array_signups']));
			$this->assertEqual(801, $u1->signups_on_my_sheets[2]['array_signups'][0]['signup_id']);
			$this->assertEqual(804, $u1->signups_on_my_sheets[2]['array_signups'][1]['signup_id']);
			$this->assertEqual(806, $u1->signups_on_my_sheets[2]['array_signups'][2]['signup_id']);
			$this->assertEqual(813, $u1->signups_on_my_sheets[2]['array_signups'][3]['signup_id']);

			// util_prePrintR($u1->signups_on_my_sheets); exit;
		}

		function testCacheMyAvailableSheetOpenings() {
			$u1 = User::getOneFromDb(['user_id' => 101], $this->DB);

			$u1->cacheMyAvailableSheetOpenings();

			$this->assertEqual(2, count($u1->sheet_openings_all));
			$this->assertEqual(601, $u1->sheet_openings_all[0]['s_id']);
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

			// TODO - should let caller/program know there's a problem
			$this->assertFalse($status);
		}

		function testNewUserBaseRecordCreatedWhenAuthDataIsForNewUser() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// TODO - should let caller/program know there's a problem
			$this->assertFalse($status);
		}

		//	util_prePrintR($u1);
		//	exit;

	}