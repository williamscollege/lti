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
			$this->assertTrue(in_array('flag_delete', SUS_Sheetgroup::$fields));
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

		//// DB interaction tests

		function testSUS_SheetgroupDBInsert() {
			$s = new SUS_Sheetgroup(['owner_user_id' => '5', 'DB' => $this->DB]);

			$s->updateDb();
			$this->assertTrue($s->matchesDb);

			$s2 = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $s->sheetgroup_id], $this->DB);

			$this->assertTrue($s2->matchesDb);
			$this->assertEqual($s2->owner_user_id, 5);
		}

		function testSUS_SheetgroupRetrievedFromDb() {
			$s = new SUS_Sheetgroup(['sheetgroup_id' => 501, 'DB' => $this->DB]);
			$this->assertNull($s->owner_user_id);

			$s->refreshFromDb();
			$this->assertEqual($s->owner_user_id, 101);
		}


		//// instance methods - object itself

		//// instance methods - related data

		function testCacheSheets() {
			$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id'=>501],$this->DB);
			$this->assertTrue($sg->matchesDb);

			$sg->cacheSheets();
			$this->assertEqual(3, count($sg->sheets));
		}

		function testLoadSheets() {
			$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id'=>501],$this->DB);
			$this->assertTrue($sg->matchesDb);

			$sg->loadSheets();
			$this->assertEqual(3, count($sg->sheets));
		}


		public function testCascadeDelete() {
			$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => 501], $this->DB);
			$this->assertTrue($sg->matchesDb);
			$this->assertEqual(0, $sg->flag_delete);

			$sg->cascadeDelete();

			// test expected results
			$this->assertEqual(3, count($sg->sheets));
			$this->assertEqual(2, count($sg->sheets[0]->openings));
			$this->assertEqual(2, count($sg->sheets[1]->openings));
			$this->assertEqual(1, count($sg->sheets[2]->openings));
			$this->assertEqual(2, count($sg->sheets[0]->openings[0]->signups));
			$this->assertEqual(4, count($sg->sheets[0]->openings[1]->signups));
			$this->assertEqual(1, count($sg->sheets[1]->openings[0]->signups));
			$this->assertEqual(2, count($sg->sheets[1]->openings[1]->signups));
			$this->assertEqual(1, count($sg->sheets[2]->openings[0]->signups));
			$this->assertEqual(601, $sg->sheets[0]->openings[0]->sheet_id);
			$this->assertEqual(702, $sg->sheets[0]->openings[0]->opening_id);
			$this->assertEqual(809, $sg->sheets[0]->openings[0]->signups[0]->signup_id);

			// were items correctly marked as deleted?
			$this->assertEqual(1, $sg->flag_delete); // sheetgroup
			$this->assertEqual(1, $sg->sheets[0]->flag_delete); // sheet
			$this->assertEqual(1, $sg->sheets[1]->flag_delete); // sheet
			$this->assertEqual(1, $sg->sheets[2]->flag_delete); // sheet
			$this->assertEqual(1, $sg->sheets[0]->openings[0]->flag_delete); // opening
			$this->assertEqual(1, $sg->sheets[0]->openings[1]->flag_delete); // opening
			$this->assertEqual(1, $sg->sheets[0]->openings[0]->signups[0]->flag_delete); // signup
			$this->assertEqual(1, $sg->sheets[0]->openings[0]->signups[1]->flag_delete); // signup
			$this->assertEqual(1, $sg->sheets[0]->openings[1]->signups[0]->flag_delete); // signup
			$this->assertEqual(1, $sg->sheets[0]->openings[1]->signups[1]->flag_delete); // signup
			$this->assertEqual(1, $sg->sheets[0]->openings[1]->signups[2]->flag_delete); // signup
			$this->assertEqual(1, $sg->sheets[0]->openings[1]->signups[3]->flag_delete); // signup
		}

	}
