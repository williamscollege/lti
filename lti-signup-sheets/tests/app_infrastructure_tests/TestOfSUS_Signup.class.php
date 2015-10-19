<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');

	class TestOfSUS_Signup extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_SignupAtributesExist() {
			$this->assertEqual(count(SUS_Signup::$fields), 7);

			$this->assertTrue(in_array('signup_id', SUS_Signup::$fields));
			$this->assertTrue(in_array('created_at', SUS_Signup::$fields));
			$this->assertTrue(in_array('updated_at', SUS_Signup::$fields));
			$this->assertTrue(in_array('flag_delete', SUS_Signup::$fields));
			$this->assertTrue(in_array('opening_id', SUS_Signup::$fields));
			$this->assertTrue(in_array('signup_user_id', SUS_Signup::$fields));
			$this->assertTrue(in_array('admin_comment', SUS_Signup::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$su1 = SUS_Signup::getOneFromDb(['signup_id' => 801], $this->DB);
			$su2 = SUS_Signup::getOneFromDb(['signup_id' => 802], $this->DB);

			$this->assertEqual(SUS_Signup::cmp($su1, $su2), -1);
			$this->assertEqual(SUS_Signup::cmp($su1, $su1), 0);
			$this->assertEqual(SUS_Signup::cmp($su2, $su1), 1);
		}


		//// instance methods - object itself

		//// instance methods - related data

		public function testCascadeDelete() {
			$su = SUS_Signup::getOneFromDb(['signup_id' => 801], $this->DB);
			$this->assertTrue($su->matchesDb);
			$this->assertEqual(0, $su->flag_delete);

			$o = SUS_Opening::getOneFromDb(['opening_id' => $su->opening_id], $this->DB);
			$s = SUS_Sheet::getOneFromDb(['sheet_id' => $o->sheet_id], $this->DB);
			$usr = User::getOneFromDb(['user_id' => $s->owner_user_id], $this->DB);
			$this->assertTrue($usr->matchesDb);
			$this->assertEqual(0, $usr->flag_delete);

			$su->cascadeDelete($usr);

			// were items correctly marked as deleted?
			$this->assertEqual(1, $su->flag_delete);
		}


	}
