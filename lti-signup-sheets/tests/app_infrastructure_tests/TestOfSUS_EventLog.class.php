<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');

	class TestOfSUS_EventLog extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_EventLogAtributesExist() {
			$this->assertEqual(count(SUS_EventLog::$fields), 10);

			$this->assertTrue(in_array('eventlog_id', SUS_EventLog::$fields));
			$this->assertTrue(in_array('user_id', SUS_EventLog::$fields));
			$this->assertTrue(in_array('flag_success', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_action', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_action_id', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_action_target_type', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_note', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_dataset', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_filepath', SUS_EventLog::$fields));
			$this->assertTrue(in_array('user_agent_string', SUS_EventLog::$fields));
			$this->assertTrue(in_array('event_datetime', SUS_EventLog::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$e1 = SUS_EventLog::getOneFromDb(['eventlog_id' => 1001], $this->DB);
			$e2 = SUS_EventLog::getOneFromDb(['eventlog_id' => 1002], $this->DB);

			//			$this->assertEqual(SUS_EventLog::cmp($e1, $e2), -1);
			//			$this->assertEqual(SUS_EventLog::cmp($e1, $e1), 1);
			//			$this->assertEqual(SUS_EventLog::cmp($e2, $e1), -1);
		}


		//// instance methods - object itself

		//// instance methods - related data


	}
