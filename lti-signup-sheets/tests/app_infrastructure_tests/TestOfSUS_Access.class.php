<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSUS_Access extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_AccessAtributesExist() {
			$this->assertEqual(count(SUS_Access::$fields), 9);

			$this->assertTrue(in_array('access_id', SUS_Access::$fields));
			$this->assertTrue(in_array('created_at', SUS_Access::$fields));
			$this->assertTrue(in_array('updated_at', SUS_Access::$fields));
			$this->assertTrue(in_array('last_user_id', SUS_Access::$fields));
			$this->assertTrue(in_array('sheet_id', SUS_Access::$fields));
			$this->assertTrue(in_array('type', SUS_Access::$fields));
			$this->assertTrue(in_array('constraint_id', SUS_Access::$fields));
			$this->assertTrue(in_array('constraint_data', SUS_Access::$fields));
			$this->assertTrue(in_array('broadness', SUS_Access::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$s1 = SUS_Access::getOneFromDb(['access_id' => 1], $this->DB);
			$s2 = SUS_Access::getOneFromDb(['access_id' => 2], $this->DB);

			$this->assertEqual(SUS_Access::cmp($s1, $s2), -1);
			$this->assertEqual(SUS_Access::cmp($s1, $s1), 0);
			$this->assertEqual(SUS_Access::cmp($s2, $s1), 1);
		}

	}
