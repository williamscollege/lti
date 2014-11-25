<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSUS_Sheet extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_SheetAtributesExist() {
			$this->assertEqual(count(SUS_Sheet::$fields), 21);

			$this->assertTrue(in_array('sheet_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('created_at', SUS_Sheet::$fields));
			$this->assertTrue(in_array('updated_at', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_deleted', SUS_Sheet::$fields));
			$this->assertTrue(in_array('owner_user_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('last_user_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('sus_sheetgroup_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('name', SUS_Sheet::$fields));
			$this->assertTrue(in_array('description', SUS_Sheet::$fields));
			$this->assertTrue(in_array('type', SUS_Sheet::$fields));
			$this->assertTrue(in_array('date_opens', SUS_Sheet::$fields));
			$this->assertTrue(in_array('date_closes', SUS_Sheet::$fields));
			$this->assertTrue(in_array('max_total_user_signups', SUS_Sheet::$fields));
			$this->assertTrue(in_array('max_pending_user_signups', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_alert_owner_change', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_alert_owner_signup', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_alert_owner_imminent', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_alert_admin_change', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_alert_admin_signup', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_alert_admin_imminent', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_private_signups', SUS_Sheet::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$s1 = SUS_Sheet::getOneFromDb(['sheet_id' => 1], $this->DB);
			$s2 = SUS_Sheet::getOneFromDb(['sheet_id' => 2], $this->DB);

			$this->assertEqual(SUS_Sheet::cmp($s1, $s2), -1);
			$this->assertEqual(SUS_Sheet::cmp($s1, $s1), 0);
			$this->assertEqual(SUS_Sheet::cmp($s2, $s1), 1);
			exit;
		}

	}
