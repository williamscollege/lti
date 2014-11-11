<?php
    require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfRole extends WMSUnitTestCaseDB
	{
        function setUp() {
            createAllTestData($this->DB);
        }

        function tearDown() {
            removeAllTestData($this->DB);
        }

        function testRoleAtributesExist() {
            $this->assertEqual(count(Role::$fields), 4);

            $this->assertTrue(in_array('role_id', Role::$fields));
            $this->assertTrue(in_array('priority', Role::$fields));
            $this->assertTrue(in_array('name', Role::$fields));
            $this->assertTrue(in_array('flag_delete', Role::$fields));
        }

        //// static methods

		public function testOfCmp() {
            $r1 = Role::getOneFromDb(['role_id'=>1],$this->DB);
            $r2 = Role::getOneFromDb(['role_id'=>2],$this->DB);

            $this->assertEqual(Role::cmp($r1,$r2),-1);
            $this->assertEqual(Role::cmp($r1,$r1),0);
            $this->assertEqual(Role::cmp($r2,$r1),1);
		}

        //// instance methods - object itself

        //// instance methods - related data

        public function testGetUsers() {
            $r = Role::getOneFromDb(['role_id'=>3],$this->DB);

            $us = $r->getUsers();

            $this->assertEqual(6,count($us));

            $this->assertEqual(101,$us[0]->user_id);
            $this->assertEqual(102,$us[1]->user_id);
            $this->assertEqual(103,$us[2]->user_id);
            $this->assertEqual(104,$us[3]->user_id);
            $this->assertEqual(106,$us[4]->user_id);
            $this->assertEqual(107,$us[5]->user_id);
        }

        public function testGetRoleActionTargets() {
            $r = Role::getOneFromDb(['role_id'=>3],$this->DB);

            $ats = $r->getRoleActionTargets();

            $this->assertEqual(9,count($ats));

            $this->assertEqual(207,$ats[0]->role_action_target_link_id);
            $this->assertEqual(210,$ats[1]->role_action_target_link_id);
            $this->assertEqual(212,$ats[2]->role_action_target_link_id);
            $this->assertEqual(220,$ats[3]->role_action_target_link_id);
            $this->assertEqual(221,$ats[4]->role_action_target_link_id);
            $this->assertEqual(37,$ats[5]->role_action_target_link_id);
            $this->assertEqual(38,$ats[6]->role_action_target_link_id);
            $this->assertEqual(39,$ats[7]->role_action_target_link_id);
            $this->assertEqual(40,$ats[8]->role_action_target_link_id);
        }
	}

?>