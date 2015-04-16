<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');

	class TestOfSUS_Sheet extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_SheetAtributesExist() {
			$this->assertEqual(count(SUS_Sheet::$fields), 20);

			$this->assertTrue(in_array('sheet_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('created_at', SUS_Sheet::$fields));
			$this->assertTrue(in_array('updated_at', SUS_Sheet::$fields));
			$this->assertTrue(in_array('flag_delete', SUS_Sheet::$fields));
			$this->assertTrue(in_array('owner_user_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('sheetgroup_id', SUS_Sheet::$fields));
			$this->assertTrue(in_array('name', SUS_Sheet::$fields));
			$this->assertTrue(in_array('description', SUS_Sheet::$fields));
			$this->assertTrue(in_array('type', SUS_Sheet::$fields));
			$this->assertTrue(in_array('begin_date', SUS_Sheet::$fields));
			$this->assertTrue(in_array('end_date', SUS_Sheet::$fields));
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
			$s1 = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $this->DB);
			$s2 = SUS_Sheet::getOneFromDb(['sheet_id' => 602], $this->DB);

			$this->assertEqual(SUS_Sheet::cmp($s1, $s2), -1);
			$this->assertEqual(SUS_Sheet::cmp($s1, $s1), 0);
			$this->assertEqual(SUS_Sheet::cmp($s2, $s1), 1);
		}

		//// instance methods - object itself

		//// instance methods - related data

		public function testCacheOpenings() {
			$s = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $this->DB);
			$this->assertTrue($s->matchesDb);

			$s->cacheOpenings();
			$this->assertTrue($s->matchesDb);

			$this->assertEqual(2, count($s->openings));
		}

		public function testLoadOpenings() {
			$s = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $this->DB);
			$this->assertTrue($s->matchesDb);

			$s->loadOpenings();
			$this->assertTrue($s->matchesDb);

			$this->assertEqual(2, count($s->openings));
		}

		public function testCacheAccess() {
			$a = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $this->DB);
			$this->assertTrue($a->matchesDb);

			$a->cacheAccess();
			// util_prePrintR($a->access);

			$this->assertTrue($a->matchesDb);
			$this->assertEqual(9, count($a->access));
		}

		public function testLoadAccess() {
			$a = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $this->DB);
			$this->assertTrue($a->matchesDb);

			$a->loadAccess();
			$this->assertTrue($a->matchesDb);
			$this->assertEqual(9, count($a->access));
		}

		public function testCascadeDelete() {
			$s = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $this->DB);
			$this->assertTrue($s->matchesDb);
			$this->assertEqual(0, $s->flag_delete);

			$s->cascadeDelete();
			// util_prePrintR($s->openings);

			// test expected results
			$this->assertEqual(2, count($s->openings));
			$this->assertEqual(4, count($s->openings[0]->signups));
			$this->assertEqual(2, count($s->openings[1]->signups));
			$this->assertEqual(701, $s->openings[0]->opening_id);
			$this->assertEqual(813, $s->openings[0]->signups[0]->signup_id);

			// were items correctly marked as deleted?
			$this->assertEqual(1, $s->flag_delete); // sheet
			$this->assertEqual(1, $s->openings[0]->flag_delete); // opening
			$this->assertEqual(1, $s->openings[1]->flag_delete); // opening
			$this->assertEqual(1, $s->openings[0]->signups[0]->flag_delete); // signup
			$this->assertEqual(1, $s->openings[0]->signups[1]->flag_delete); // signup
			$this->assertEqual(1, $s->openings[1]->signups[0]->flag_delete); // signup
			$this->assertEqual(1, $s->openings[1]->signups[1]->flag_delete); // signup
		}

	}
