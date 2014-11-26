<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSUS_Sheetgroup extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_SheetgroupAtributesExist() {
			$this->assertEqual(count(SUS_Sheetgroup::$fields), 10);

			$this->assertTrue(in_array('sheetgroup_id', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('created_at', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('updated_at', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('flag_deleted', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('owner_user_id', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('flag_is_default', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('name', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('description', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('max_g_total_user_signups', SUS_Sheetgroup::$fields));
			$this->assertTrue(in_array('max_g_pending_user_signups', SUS_Sheetgroup::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$s1 = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => 501], $this->DB);
			$s2 = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => 502], $this->DB);

			$this->assertEqual(SUS_Sheetgroup::cmp($s1, $s2), -1);
			$this->assertEqual(SUS_Sheetgroup::cmp($s1, $s1), 0);
			$this->assertEqual(SUS_Sheetgroup::cmp($s2, $s1), 1);
		}

	}
