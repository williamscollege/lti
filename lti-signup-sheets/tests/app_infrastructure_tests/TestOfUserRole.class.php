<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfUserRole extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testUserRoleAtributesExist() {
			$this->assertEqual(count(User_Role::$fields), 6);

            $this->assertTrue(in_array('user_role_link_id', User_Role::$fields));
            $this->assertTrue(in_array('created_at', User_Role::$fields));
            $this->assertTrue(in_array('updated_at', User_Role::$fields));
            $this->assertTrue(in_array('last_user_id', User_Role::$fields));
            $this->assertTrue(in_array('user_id', User_Role::$fields));
            $this->assertTrue(in_array('role_id', User_Role::$fields));
		}

		//// static methods

        //// instance methods - object itself

        //// instance methods - related data

        function testLoadUser() {
            $ur = User_Role::getOneFromDb(['user_role_link_id'=>301],$this->DB);
            $this->assertEqual(301,$ur->user_role_link_id);

            $ur->loadUser();

            $this->assertEqual(101,$ur->user->user_id);
        }

        function testLoadRole() {
            $ur = User_Role::getOneFromDb(['user_role_link_id'=>301],$this->DB);
            $this->assertEqual(301,$ur->user_role_link_id);

            $ur->loadRole();

            $this->assertEqual(3,$ur->role->role_id);
        }

    }