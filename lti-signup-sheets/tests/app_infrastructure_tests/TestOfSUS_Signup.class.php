<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

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
			$s1 = SUS_Signup::getOneFromDb(['signup_id' => 801], $this->DB);
			$s2 = SUS_Signup::getOneFromDb(['signup_id' => 802], $this->DB);

			$this->assertEqual(SUS_Signup::cmp($s1, $s2), -1);
			$this->assertEqual(SUS_Signup::cmp($s1, $s1), 0);
			$this->assertEqual(SUS_Signup::cmp($s2, $s1), 1);
		}


		//// instance methods - object itself

		//// instance methods - related data

		public function testCascadeDelete() {
			$s = SUS_Signup::getOneFromDb(['signup_id' => 801], $this->DB);
			$this->assertTrue($s->matchesDb);
			$this->assertEqual(0, $s->flag_delete);

			$s->cascadeDelete();

			// were items correctly marked as deleted?
			$this->assertEqual(1, $s->flag_delete);
		}


	}
