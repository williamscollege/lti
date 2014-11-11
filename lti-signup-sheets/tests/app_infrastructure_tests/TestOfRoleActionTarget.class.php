<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfRoleActionTarget extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testRoleActionTargetAtributesExist() {
			$this->assertEqual(count(Role_Action_Target::$fields), 9);

            $this->assertTrue(in_array('role_action_target_link_id', Role_Action_Target::$fields));
            $this->assertTrue(in_array('created_at', Role_Action_Target::$fields));
            $this->assertTrue(in_array('updated_at', Role_Action_Target::$fields));
            $this->assertTrue(in_array('last_user_id', Role_Action_Target::$fields));
            $this->assertTrue(in_array('role_id', Role_Action_Target::$fields));
            $this->assertTrue(in_array('action_id', Role_Action_Target::$fields));
            $this->assertTrue(in_array('target_type', Role_Action_Target::$fields));
            $this->assertTrue(in_array('target_id', Role_Action_Target::$fields));
            $this->assertTrue(in_array('flag_delete', Role_Action_Target::$fields));
		}

		//// static methods

        function testCmp() {
            $rat1 = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>209],$this->DB);
            $rat2 = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>210],$this->DB);

            $this->assertEqual(Role_Action_Target::cmp($rat1, $rat2), -1);
            $this->assertEqual(Role_Action_Target::cmp($rat1, $rat1), 0);
            $this->assertEqual(Role_Action_Target::cmp($rat2, $rat1), 1);

            $all = Role_Action_Target::getAllFromDb(['role_action_target_link_id >'=>'100'],$this->DB);

            usort($all,'Role_Action_Target::cmp');

//            // role, then action, then type, then target
//            SORT_PRIORITIES_FOR_TYPES = [
//                'global_notebook'=>10,
//                'global_metadata'=>20,
//                'global_plant'   =>30,
//                'global_specimen'=>40,
//                'notebook'       =>50,
//                'metadata_structure' =>60,
//                'plant'          =>70,
//                'specimen'       =>80
//            ];


            //----------------------------------------

//            (201,NOW(),NOW(), 110, 2, 1, 'global_notebook', 0, 0),
//            (206,NOW(),NOW(), 110, 2, 1, 'global_metadata', 0, 0),
//            (209,NOW(),NOW(), 110, 2, 1, 'global_plant', 0, 0),

//            (202,NOW(),NOW(), 110, 2, 2, 'global_notebook', 0, 0),
//            (203,NOW(),NOW(), 110, 2, 2, 'global_metadata', 0, 0),
//            (204,NOW(),NOW(), 110, 2, 2, 'global_plant', 0, 0),
//            (205,NOW(),NOW(), 110, 2, 2, 'global_specimen', 0, 0),

//            (215,NOW(),NOW(), 110, 2, 3, 'global_notebook', 0, 0),
//            (214,NOW(),NOW(), 110, 2, 3, 'global_metadata', 0, 0),
//            (216,NOW(),NOW(), 110, 2, 3, 'global_specimen', 0, 0),

//            (218,NOW(),NOW(), 110, 2, 4, 'global_notebook', 0, 0),
//            (217,NOW(),NOW(), 110, 2, 4, 'global_metadata', 0, 0),
//            (219,NOW(),NOW(), 110, 2, 4, 'global_specimen', 0, 0),

//            (207,NOW(),NOW(), 110, 3, 1, 'global_metadata', 0, 0),
//            (210,NOW(),NOW(), 110, 3, 1, 'global_plant', 0, 0),
//            (212,NOW(),NOW(), 110, 3, 1, 'notebook', 1004, 0),

//            (220,NOW(),NOW(), 110, 3, 4, 'global_notebook', 0, 0),
//            (221,NOW(),NOW(), 110, 3, 4, 'global_specimen', 0, 0)

//            (208,NOW(),NOW(), 110, 4, 1, 'global_metadata', 0, 0),
//            (211,NOW(),NOW(), 110, 4, 1, 'global_plant', 0, 0),
//            (213,NOW(),NOW(), 110, 4, 1, 'notebook', 1004, 0),

            $this->assertEqual(201, $all[0]->role_action_target_link_id);
            $this->assertEqual(206, $all[1]->role_action_target_link_id);
            $this->assertEqual(209, $all[2]->role_action_target_link_id);

            $this->assertEqual(202, $all[3]->role_action_target_link_id);
            $this->assertEqual(203, $all[4]->role_action_target_link_id);
            $this->assertEqual(204, $all[5]->role_action_target_link_id);
            $this->assertEqual(205, $all[6]->role_action_target_link_id);

            $this->assertEqual(215, $all[7]->role_action_target_link_id);
            $this->assertEqual(214, $all[8]->role_action_target_link_id);
            $this->assertEqual(216, $all[9]->role_action_target_link_id);

            $this->assertEqual(218, $all[10]->role_action_target_link_id);
            $this->assertEqual(217, $all[11]->role_action_target_link_id);
            $this->assertEqual(219, $all[12]->role_action_target_link_id);

            $this->assertEqual(207, $all[13]->role_action_target_link_id);
            $this->assertEqual(210, $all[14]->role_action_target_link_id);
            $this->assertEqual(212, $all[15]->role_action_target_link_id);

            $this->assertEqual(220, $all[16]->role_action_target_link_id);
            $this->assertEqual(221, $all[17]->role_action_target_link_id);

            $this->assertEqual(208, $all[18]->role_action_target_link_id);
            $this->assertEqual(211, $all[19]->role_action_target_link_id);
            $this->assertEqual(213, $all[20]->role_action_target_link_id);
        }

        function testGetGlobalTargetTypeForObject() {
            $this->assertEqual('global_plant', Role_Action_Target::getGlobalTargetTypeForObject(new Authoritative_Plant(['DB' => $this->DB])));
            $this->assertEqual('global_plant', Role_Action_Target::getGlobalTargetTypeForObject(new Authoritative_Plant_Extra(['DB' => $this->DB])));

            $this->assertEqual('global_metadata', Role_Action_Target::getGlobalTargetTypeForObject(new Metadata_Structure(['DB' => $this->DB])));
            $this->assertEqual('global_metadata', Role_Action_Target::getGlobalTargetTypeForObject(new Metadata_Term_Set(['DB' => $this->DB])));
            $this->assertEqual('global_metadata', Role_Action_Target::getGlobalTargetTypeForObject(new Metadata_Term_Value(['DB' => $this->DB])));
            $this->assertEqual('global_metadata', Role_Action_Target::getGlobalTargetTypeForObject(new Metadata_Reference(['DB' => $this->DB])));

            $this->assertEqual('global_notebook', Role_Action_Target::getGlobalTargetTypeForObject(new Notebook(['DB' => $this->DB])));
            $this->assertEqual('global_notebook', Role_Action_Target::getGlobalTargetTypeForObject(new Notebook_Page(['DB' => $this->DB])));
            $this->assertEqual('global_notebook', Role_Action_Target::getGlobalTargetTypeForObject(new Notebook_Page_Field(['DB' => $this->DB])));

            $this->assertEqual('global_specimen', Role_Action_Target::getGlobalTargetTypeForObject(new Specimen(['DB' => $this->DB])));
            $this->assertEqual('global_specimen', Role_Action_Target::getGlobalTargetTypeForObject(new Specimen_Image(['DB' => $this->DB])));

            $this->assertEqual(false, Role_Action_Target::getGlobalTargetTypeForObject(new Action(['DB' => $this->DB])));
        }

        function testGetSpecificTargetTypeForObject() {
            $this->assertEqual('plant', Role_Action_Target::getSpecificTargetTypeForObject(new Authoritative_Plant(['DB' => $this->DB])));
            $this->assertEqual('plant', Role_Action_Target::getSpecificTargetTypeForObject(new Authoritative_Plant_Extra(['DB' => $this->DB])));

            $this->assertEqual('metadata_structure', Role_Action_Target::getSpecificTargetTypeForObject(new Metadata_Structure(['DB' => $this->DB])));
            $this->assertEqual('metadata_structure', Role_Action_Target::getSpecificTargetTypeForObject(new Metadata_Term_Set(['DB' => $this->DB])));
            $this->assertEqual('metadata_structure', Role_Action_Target::getSpecificTargetTypeForObject(new Metadata_Term_Value(['DB' => $this->DB])));
            $this->assertEqual('metadata_structure', Role_Action_Target::getSpecificTargetTypeForObject(new Metadata_Reference(['DB' => $this->DB])));

            $this->assertEqual('notebook', Role_Action_Target::getSpecificTargetTypeForObject(new Notebook(['DB' => $this->DB])));
            $this->assertEqual('notebook', Role_Action_Target::getSpecificTargetTypeForObject(new Notebook_Page(['DB' => $this->DB])));
            $this->assertEqual('notebook', Role_Action_Target::getSpecificTargetTypeForObject(new Notebook_Page_Field(['DB' => $this->DB])));

            $this->assertEqual('specimen', Role_Action_Target::getSpecificTargetTypeForObject(new Specimen(['DB' => $this->DB])));
            $this->assertEqual('specimen', Role_Action_Target::getSpecificTargetTypeForObject(new Specimen_Image(['DB' => $this->DB])));

            $this->assertEqual(false, Role_Action_Target::getSpecificTargetTypeForObject(new Action(['DB' => $this->DB])));
        }

        //// instance methods - object itself

        //// instance methods - related data

        function testGetRole() {
            $rat = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>201],$this->DB);
            $this->assertEqual(201,$rat->role_action_target_link_id);

            $r = $rat->getRole();

            $this->assertEqual(2,$r->role_id);
        }

        function testGetAction() {
            $rat = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>201],$this->DB);
            $this->assertEqual(201,$rat->role_action_target_link_id);

            $a = $rat->getAction();

            $this->assertEqual(1,$a->action_id);
        }

        function testGetTargetsGlobal() {
            $rat = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>201],$this->DB);

            $targets = $rat->getTargets();

            $this->assertEqual(4,count($targets));

            $this->assertEqual(1001,$targets[0]->notebook_id);
            $this->assertEqual(1002,$targets[1]->notebook_id);
            $this->assertEqual(1003,$targets[2]->notebook_id);
            $this->assertEqual(1004,$targets[3]->notebook_id);
        }

        function testGetTargetsSpecific() {
            $rat = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>212],$this->DB);

            $targets = $rat->getTargets();

            $this->assertEqual(1,count($targets));
            $this->assertEqual(1004,$targets[0]->notebook_id);
        }
    }