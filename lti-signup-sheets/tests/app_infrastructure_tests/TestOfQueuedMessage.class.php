<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');
	require_once(dirname(__FILE__) . '/../../classes/auth_base.class.php');

	Mock::generate('Auth_Base');

	class TestOfQueuedMessage extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testQueuedMessageAtributesExist() {
			$this->assertEqual(count(QueuedMessage::$fields), 13);

			$this->assertTrue(in_array('queued_message_id', QueuedMessage::$fields));
			$this->assertTrue(in_array('user_id', QueuedMessage::$fields));
			$this->assertTrue(in_array('sheet_id', QueuedMessage::$fields));
			$this->assertTrue(in_array('opening_id', QueuedMessage::$fields));
			$this->assertTrue(in_array('delivery_type', QueuedMessage::$fields));
			$this->assertTrue(in_array('flag_is_delivered', QueuedMessage::$fields));
			$this->assertTrue(in_array('target', QueuedMessage::$fields));
			$this->assertTrue(in_array('summary', QueuedMessage::$fields));
			$this->assertTrue(in_array('body', QueuedMessage::$fields));
			$this->assertTrue(in_array('action_datetime', QueuedMessage::$fields));
			$this->assertTrue(in_array('action_status', QueuedMessage::$fields));
			$this->assertTrue(in_array('action_notes', QueuedMessage::$fields));
			$this->assertTrue(in_array('flag_delete', QueuedMessage::$fields));
		}

		//// static methods

		//// DB interaction tests

		function testQueuedMessageDBInsert() {
			$qm = new QueuedMessage(['queued_message_id' => 50, 'user_id' => 20, 'sheet_id' => '150', 'opening_id' => '250', 'delivery_type' => 'email', 'DB' => $this->DB]);

			$qm->updateDb();

			$qm2 = QueuedMessage::getOneFromDb(['queued_message_id' => 50], $this->DB);

			$this->assertTrue($qm2->matchesDb);
			$this->assertEqual($qm2->sheet_id, '150');
		}

		function testQueuedMessageRetrievedFromDb() {
			$qm = new QueuedMessage(['queued_message_id' => 50, 'DB' => $this->DB]);
			$this->assertNull($qm->sheet_id);

			$qm->sheet_id = '160';
			$qm->updateDb();

			$qm->refreshFromDb();
			$this->assertEqual($qm->sheet_id, '160');
		}

		function testQueuedMessageDBInsertViaFactory() {
			// QueuedMessage::factory($db, $user_id, $target, $summary, $body, $opening_id = 0, $sheet_id = 0, $type = 'email' )
			$qm = QueuedMessage::factory($this->DB, 50, 'jbond@institution.edu', 'Glow Signup Sheets - James Bond cancelled on Sheet 602', 'Signup cancelled: James Bond Opening: 08/25/2015 10:01 PM On Sheet: Sheet 602.', 703, 602);
			$qm->updateDb();

			$qm->refreshFromDb();
			$this->assertEqual($qm->sheet_id, 602);
		}


		//// instance methods - related data


	}