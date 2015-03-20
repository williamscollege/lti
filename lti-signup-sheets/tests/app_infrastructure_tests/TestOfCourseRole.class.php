<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');

	class TestOfCourseRole extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testCourseRoleAtributesExist() {
			$this->assertEqual(count(Course_Role::$fields), 4);

			$this->assertTrue(in_array('course_role_id', Course_Role::$fields));
			$this->assertTrue(in_array('priority', Course_Role::$fields));
			$this->assertTrue(in_array('course_role_name', Course_Role::$fields));
			$this->assertTrue(in_array('flag_delete', Course_Role::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$r1 = Course_Role::getOneFromDb(['course_role_id' => 1], $this->DB);
			$r2 = Course_Role::getOneFromDb(['course_role_id' => 2], $this->DB);

			$this->assertEqual(Course_Role::cmp($r1, $r2), -1);
			$this->assertEqual(Course_Role::cmp($r1, $r1), 0);
			$this->assertEqual(Course_Role::cmp($r2, $r1), 1);
		}

		//// instance methods - object itself

		//// instance methods - related data


	}
