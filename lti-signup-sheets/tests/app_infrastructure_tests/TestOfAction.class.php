<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfAction extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testActionAtributesExist() {
			$this->assertEqual(count(Action::$fields), 4);

        	$this->assertTrue(in_array('action_id', Action::$fields));
            $this->assertTrue(in_array('name', Action::$fields));
            $this->assertTrue(in_array('ordering', Action::$fields));
			$this->assertTrue(in_array('flag_delete', Action::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = new Action(['action_id' => 50, 'name' => 'nA', 'ordering'=>2, 'DB' => $this->DB]);
            $n2 = new Action(['action_id' => 60, 'name' => 'nB', 'ordering'=>1, 'DB' => $this->DB]);

            $nar = [$n1,$n2];
            usort($nar,'Action::cmp');

            $this->assertEqual('nB',$nar[0]->name);
            $this->assertEqual('nA',$nar[1]->name);

            $n1->ordering = 1;

            $nar = [$n1,$n2];
            usort($nar,'Action::cmp');

            $this->assertEqual('nA',$nar[0]->name);
            $this->assertEqual('nB',$nar[1]->name);
        }

        function testSanitizeAction() {
            $this->assertEqual('view',Action::sanitizeAction('view'));
            $this->assertEqual('view',Action::sanitizeAction(''));
            $this->assertEqual('view',Action::sanitizeAction('blarg'));
            $this->assertEqual('edit',Action::sanitizeAction('EDIT'));
        }

        function testActionsMatchDatabase() {
            $all_db_actions = Action::getAllFromDb(['flag_delete' => FALSE],$this->DB);
            $this->assertEqual(count(Action::$VALID_ACTIONS),count($all_db_actions));
            $action_db_names = Db_Linked::arrayOfAttrValues($all_db_actions,'name');
            foreach (Action::$VALID_ACTIONS as $va) {
                $this->assertTrue(in_array($va,$action_db_names),"'$va' missing from database");
            }
            foreach ($action_db_names as $db_a) {
                $this->assertTrue(in_array($db_a,Action::$VALID_ACTIONS),"'$db_a' missing from VALID_ACTIONS");
            }
        }

        //// instance methods - object itself

        //// instance methods - related data

    }