<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

	Mock::generate('Auth_Base');

	class TestOfUser extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);

			$this->auth              = new MockAuth_Base();
			$this->auth->username    = Auth_Base::$TEST_USERNAME;
			$this->auth->email       = Auth_Base::$TEST_EMAIL;
			$this->auth->fname       = Auth_Base::$TEST_FNAME;
			$this->auth->lname       = Auth_Base::$TEST_LNAME;
			$this->auth->sortname    = Auth_Base::$TEST_SORTNAME;
			$this->auth->inst_groups = array_slice(Auth_Base::$TEST_INST_GROUPS, 0);
			$this->auth->msg         = '';
			$this->auth->debug       = '';
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testUserAtributesExist() {
			$this->assertEqual(count(User::$fields), 8);

			$this->assertTrue(in_array('user_id', User::$fields));
            $this->assertTrue(in_array('created_at', User::$fields));
            $this->assertTrue(in_array('updated_at', User::$fields));
			$this->assertTrue(in_array('username', User::$fields));
            $this->assertTrue(in_array('screen_name', User::$fields));
            $this->assertTrue(in_array('flag_is_system_admin', User::$fields));
			$this->assertTrue(in_array('flag_is_banned', User::$fields));
			$this->assertTrue(in_array('flag_delete', User::$fields));
		}

		//// static methods

		function testCmp() {
            $u1 = new User(['user_id' => 50, 'screen_name' => 'jones, fred', 'DB' => $this->DB]);
            $u2 = new User(['user_id' => 50, 'screen_name' => 'albertson, fred', 'DB' => $this->DB]);
            $u3 = new User(['user_id' => 50, 'screen_name' => 'ji, al', 'DB' => $this->DB]);
            $u4 = new User(['user_id' => 50, 'screen_name' => 'ji, bab', 'DB' => $this->DB]);

			$this->assertEqual(User::cmp($u1, $u2), 1);
			$this->assertEqual(User::cmp($u1, $u1), 0);
			$this->assertEqual(User::cmp($u2, $u1), -1);

			$this->assertEqual(User::cmp($u3, $u4), -1);
		}


		//// DB interaction tests

		function testUserDBInsert() {
			$u = new User(['user_id' => 50, 'username' => 'fjones', 'screen_name' => 'jones, fred', 'DB' => $this->DB]);

			$u->updateDb();

			$u2 = User::getOneFromDb(['user_id' => 50], $this->DB);

			$this->assertTrue($u2->matchesDb);
			$this->assertEqual($u2->username, 'fjones');
		}

		function testUserRetrievedFromDb() {
			$u = new User(['user_id' => 101, 'DB' => $this->DB]);
			$this->assertNull($u->username);

			$u->refreshFromDb();
			$this->assertEqual($u->username, Auth_Base::$TEST_USERNAME);
		}

        //// instance methods - object itself

        function testUserRenderMinimal() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $canonical = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101" data-user_screen_name="'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</div>';
            $rendered = $u->renderMinimal();
//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            $canonical = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101" data-user_screen_name="'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'"><a href="'.APP_ROOT_PATH.'/app_code/user.php?user_id=101">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</a></div>';
            $rendered = $u->renderMinimal(true);
//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        function testUserRender() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
//            $canonical = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</div>';
//            $rendered = $u->render();
//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
//            $this->assertEqual($canonical,$rendered);
            $this->todo('implement user render');
        }

        function testUserRenderRich() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
//            $canonical = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</div>';
//            $rendered = $u->renderRich();
//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
//            $this->assertEqual($canonical,$rendered);
            $this->todo('implement user renderRich');
        }

        //// instance methods - related data

        function testGetRoles() {
            $u1 = User::getOneFromDb(['user_id' => 101], $this->DB);
            $u2 = User::getOneFromDb(['user_id' => 110], $this->DB);
            $u3 = new User(['user_id' => 50, 'username' => 'fjones', 'screen_name' => 'jones, fred', 'DB' => $this->DB]);
            $u4 = User::getOneFromDb(['user_id' => 109], $this->DB);

            $r1 = $u1->getRoles();
            $this->assertEqual(1,count($r1));
            $this->assertEqual('field user',$r1[0]->name);

            $r2 = $u2->getRoles();
            $this->assertEqual(1,count($r2));
            $this->assertEqual('manager',$r2[0]->name);

            $r3 = $u3->getRoles();
            $this->assertEqual(1,count($r3));
            $this->assertEqual('public',$r3[0]->name);

            $r4 = $u4->getRoles();
            $this->assertEqual(1,count($r4));
            $this->assertEqual('public',$r4[0]->name);
        }

        function testGetAccessibleNotebooksBasic() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
            $notebooks = $u->getAccessibleNotebooks(Action::getOneFromDb(['name'=>'edit'],$this->DB));

            $this->assertEqual(2,count($notebooks),'number of notebooks mismatch');
            $this->assertEqual(1001,$notebooks[0]->notebook_id,'notebook id mismatch');
            $this->assertEqual(1002,$notebooks[1]->notebook_id,'notebook id mismatch');
        }

        function testGetAccessibleNotebooksSystemAdmin() {
            makeAuthedTestUserAdmin($this->DB);
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
            $notebooks = $u->getAccessibleNotebooks(Action::getOneFromDb(['name'=>'edit'],$this->DB));

            $this->assertEqual(4,count($notebooks),'number of notebooks mismatch: 4 vs '.count($notebooks));
            $this->assertEqual(1001,$notebooks[0]->notebook_id,'notebook id mismatch');
            $this->assertEqual(1002,$notebooks[1]->notebook_id,'notebook id mismatch');
            $this->assertEqual(1003,$notebooks[2]->notebook_id,'notebook id mismatch');
            $this->assertEqual(102,$notebooks[2]->user_id,'notebook user id mismatch');
        }

        function testGetAccessibleNotebooksManager() {
            $u = User::getOneFromDb(['user_id' => 110], $this->DB);
            $r = $u->getRoles();
            $this->assertTrue(in_array('manager',array_map(function($e){return $e->name;},$r)));

            $actions = Action::getAllFromDb([],$this->DB);
            $this->assertEqual(8,count($actions));

            $all_n = Notebook::getAllFromDb([],$this->DB);
            $num_all_n = count($all_n);

            foreach ($actions as $a) {

                //!!!!!!!!!!!!!!!!!!!!!!!!!!!
                // THIS IS THE MAIN TESTED ACTION
                $accessible_n = $u->getAccessibleNotebooks($a);
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!

                $num_accessible_n = count($accessible_n);
                $this->assertEqual($num_all_n,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting $num_all_n, but got $num_accessible_n instead");
            }
        }

        function testGetAccessibleNotebooksFieldUser() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
            $r = $u->getRoles();
            $this->assertFalse(in_array('manager',array_map(function($e){return $e->name;},$r)));
            $this->assertTrue(in_array('field user',array_map(function($e){return $e->name;},$r)));

            $actions = Action::getAllFromDb([],$this->DB);
            $this->assertEqual(8,count($actions));

            foreach ($actions as $a) {

                //!!!!!!!!!!!!!!!!!!!!!!!!!!!
                // THIS IS THE MAIN TESTED ACTION
                $accessible_n = $u->getAccessibleNotebooks($a);
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!

                $num_accessible_n = count($accessible_n);
                if ($a->name == 'view') {
                    $this->assertEqual(3,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting3n, but got $num_accessible_n instead");
                } elseif ($a->name == 'create') {
                    $this->assertEqual(4,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting 2, but got $num_accessible_n instead");
                } elseif ($a->name == 'list') {
                    $this->assertEqual(4,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting 2, but got $num_accessible_n instead");
                }  else {
                    $this->assertEqual(2,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting 2, but got $num_accessible_n instead");
                }
            }
        }

        function testGetAccessibleNotebooksPublicUser() {
            $u = User::getOneFromDb(['user_id' => 109], $this->DB);

            $r = $u->getRoles();
            $this->assertFalse(in_array('manager',array_map(function($e){return $e->name;},$r)));
            $this->assertFalse(in_array('field user',array_map(function($e){return $e->name;},$r)));
            $this->assertTrue(in_array('public',array_map(function($e){return $e->name;},$r)));

            $actions = Action::getAllFromDb([],$this->DB);
            $this->assertEqual(8,count($actions));

            foreach ($actions as $a) {

                //!!!!!!!!!!!!!!!!!!!!!!!!!!!
                // THIS IS THE MAIN TESTED ACTION
                $accessible_n = $u->getAccessibleNotebooks($a);
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!

                $num_accessible_n = count($accessible_n);
                if ($a->name == 'view') {
                    $this->assertEqual(1,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting 1, but got $num_accessible_n instead");
                } elseif ($a->name == 'list') {
                    $this->assertEqual(4,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting 4, but got $num_accessible_n instead");
                } else {
                    $this->assertEqual(0,$num_accessible_n,'mismatch on notebooks accesible for action '.$a->name.": expecting 0, but got $num_accessible_n instead");
                }
            }
        }

        function testCacheRoleActionTargets() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $u->cacheRoleActionTargets();

            $this->assertEqual(1,count($u->cached_roles));
            $this->assertEqual('field user',$u->cached_roles[0]->name);

            $rats_ids = array_keys($u->cached_role_action_targets_hash_by_id);
            $this->assertEqual(9,count($rats_ids));
            $this->assertTrue(in_array(207,$rats_ids));
            $this->assertTrue(in_array(210,$rats_ids));
            $this->assertTrue(in_array(212,$rats_ids));
            $this->assertTrue(in_array(220,$rats_ids));
            $this->assertTrue(in_array(221,$rats_ids));

            $rats_targets = array_keys($u->cached_role_action_targets_hash_by_target_type_by_id);
            $this->assertEqual(5,count($rats_targets));
            $this->assertTrue(in_array('global_metadata',$rats_targets));
            $this->assertTrue(in_array('global_notebook',$rats_targets));
            $this->assertTrue(in_array('global_specimen',$rats_targets));
            $this->assertTrue(in_array('notebook',$rats_targets));

            $rats_actions = array_keys($u->cached_role_action_targets_hash_by_action_name_by_id);
            $this->assertEqual(3,count($rats_actions));
            $this->assertTrue(in_array('view',$rats_actions));
            $this->assertTrue(in_array('create',$rats_actions));
            $this->assertTrue(in_array('list',$rats_actions));

            $rats_view_ids = array_keys($u->cached_role_action_targets_hash_by_action_name_by_id['view']);
            $this->assertEqual(3,count($rats_view_ids));
            $this->assertTrue(in_array(207,$rats_view_ids));
            $this->assertTrue(in_array(210,$rats_view_ids));
            $this->assertTrue(in_array(212,$rats_view_ids));

            $rats_create_ids = array_keys($u->cached_role_action_targets_hash_by_action_name_by_id['create']);
            $this->assertEqual(2,count($rats_create_ids));
            $this->assertTrue(in_array(220,$rats_create_ids));
            $this->assertTrue(in_array(221,$rats_create_ids));
        }

        function testCanActOnTarget() {
            $n1 = Notebook::getOneFromDb(['notebook_id'=>1001],$this->DB); // owned by 101
            $n2 = Notebook::getOneFromDb(['notebook_id'=>1003],$this->DB); // owned by 102
            $n3 = Notebook::getOneFromDb(['notebook_id'=>1004],$this->DB); // owned by 110
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB); // part of notebook 101
            $s1 = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB); // owned by 110
            $s2 = Specimen::getOneFromDb(['specimen_id'=>8002],$this->DB); // owned by 101
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6004],$this->DB);
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id'=>6101],$this->DB);
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6211],$this->DB);

            $actions_list = Action::getAllFromDb([],$this->DB);
            $actions = [];
            foreach ($actions_list as $act_elt) {
                $actions[$act_elt->name] = $act_elt;
            }

            // basic, field user
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $this->assertTrue($u->canActOnTarget($actions['view'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['edit'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['delete'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n1));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n1));

            $this->assertFalse($u->canActOnTarget($actions['view'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['edit'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['delete'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $n3));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $n3));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $n3));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $n3));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n3));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $n3));

            $this->assertTrue($u->canActOnTarget($actions['view'],$np1));
            $this->assertTrue($u->canActOnTarget($actions['edit'],$np1));
            $this->assertTrue($u->canActOnTarget($actions['create'],$np1));
            $this->assertTrue($u->canActOnTarget($actions['delete'],$np1));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$np1));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$np1));


            $this->assertFalse($u->canActOnTarget($actions['view'],   $s1));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $s1));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $s1));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $s1));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$s1));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $s1));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $s2));
            $this->assertTrue ($u->canActOnTarget($actions['edit'],   $s2));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $s2));
            $this->assertTrue ($u->canActOnTarget($actions['delete'], $s2));
            $this->assertTrue ($u->canActOnTarget($actions['publish'],$s2));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $s2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mds));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $mds));
            $this->assertFalse($u->canActOnTarget($actions['create'], $mds));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $mds));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$mds));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $mds));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdts));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $mdts));
            $this->assertFalse($u->canActOnTarget($actions['create'], $mdts));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $mdts));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$mdts));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $mdts));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['create'], $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$mdtv));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $mdtv));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $ap));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $ap));
            $this->assertFalse($u->canActOnTarget($actions['create'], $ap));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $ap));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$ap));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $ap));

            // system admin
            $u->flag_is_system_admin = true;

            $this->assertTrue($u->canActOnTarget($actions['view'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['edit'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['delete'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['verify'],$n1));

            $this->assertTrue($u->canActOnTarget($actions['view'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['edit'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['delete'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['verify'],$n2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $n3));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $n3));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $n3));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $n3));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n3));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $n3));

            $this->assertTrue($u->canActOnTarget($actions['view'],   $s1));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $s1));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $s1));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $s1));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$s1));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $s1));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $s2));
            $this->assertTrue ($u->canActOnTarget($actions['edit'],   $s2));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $s2));
            $this->assertTrue ($u->canActOnTarget($actions['delete'], $s2));
            $this->assertTrue ($u->canActOnTarget($actions['publish'],$s2));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $s2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mds));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $mds));
            $this->assertTrue($u->canActOnTarget($actions['create'], $mds));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $mds));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$mds));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $mds));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdts));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $mdts));
            $this->assertTrue($u->canActOnTarget($actions['create'], $mdts));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $mdts));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$mdts));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $mdts));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['create'], $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$mdtv));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $mdtv));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $ap));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $ap));
            $this->assertTrue($u->canActOnTarget($actions['create'], $ap));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $ap));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$ap));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $ap));

            // public user
            $u = User::getOneFromDb(['user_id' => 109], $this->DB);

            $this->assertFalse($u->canActOnTarget($actions['view'],$n1));
            $this->assertFalse($u->canActOnTarget($actions['edit'],$n1));
            $this->assertFalse($u->canActOnTarget($actions['create'],$n1));
            $this->assertFalse($u->canActOnTarget($actions['delete'],$n1));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n1));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n1));

            $this->assertFalse($u->canActOnTarget($actions['view'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['edit'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['create'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['delete'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $n3));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $n3));
            $this->assertFalse ($u->canActOnTarget($actions['create'], $n3));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $n3));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n3));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $n3));

            $this->assertFalse($u->canActOnTarget($actions['view'],   $s1));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $s1));
            $this->assertFalse ($u->canActOnTarget($actions['create'], $s1));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $s1));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$s1));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $s1));

            $this->assertFalse ($u->canActOnTarget($actions['view'],   $s2));
            $this->assertFalse ($u->canActOnTarget($actions['edit'],   $s2));
            $this->assertFalse ($u->canActOnTarget($actions['create'], $s2));
            $this->assertFalse ($u->canActOnTarget($actions['delete'], $s2));
            $this->assertFalse ($u->canActOnTarget($actions['publish'],$s2));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $s2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mds));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $mds));
            $this->assertFalse($u->canActOnTarget($actions['create'], $mds));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $mds));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$mds));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $mds));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdts));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $mdts));
            $this->assertFalse($u->canActOnTarget($actions['create'], $mdts));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $mdts));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$mdts));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $mdts));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['create'], $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $mdtv));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$mdtv));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $mdtv));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $ap));
            $this->assertFalse($u->canActOnTarget($actions['edit'],   $ap));
            $this->assertFalse($u->canActOnTarget($actions['create'], $ap));
            $this->assertFalse($u->canActOnTarget($actions['delete'], $ap));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$ap));
            $this->assertFalse($u->canActOnTarget($actions['verify'], $ap));

            // manager
            $u = User::getOneFromDb(['user_id' => 110], $this->DB); // manager user

            $this->assertTrue($u->canActOnTarget($actions['view'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['edit'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['delete'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n1));
            $this->assertTrue($u->canActOnTarget($actions['verify'],$n1));

            $this->assertTrue($u->canActOnTarget($actions['view'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['edit'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['delete'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['verify'],$n2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $n3));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $n3));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $n3));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $n3));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$n3));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $n3));

            $this->assertTrue($u->canActOnTarget($actions['view'],   $s1));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $s1));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $s1));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $s1));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$s1));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $s1));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $s2));
            $this->assertTrue ($u->canActOnTarget($actions['edit'],   $s2));
            $this->assertTrue ($u->canActOnTarget($actions['create'], $s2));
            $this->assertTrue ($u->canActOnTarget($actions['delete'], $s2));
            $this->assertTrue ($u->canActOnTarget($actions['publish'],$s2));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $s2));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mds));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $mds));
            $this->assertTrue($u->canActOnTarget($actions['create'], $mds));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $mds));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$mds));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $mds));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdts));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $mdts));
            $this->assertTrue($u->canActOnTarget($actions['create'], $mdts));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $mdts));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$mdts));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $mdts));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['create'], $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $mdtv));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$mdtv));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $mdtv));

            $this->assertTrue ($u->canActOnTarget($actions['view'],   $ap));
            $this->assertTrue($u->canActOnTarget($actions['edit'],   $ap));
            $this->assertTrue($u->canActOnTarget($actions['create'], $ap));
            $this->assertTrue($u->canActOnTarget($actions['delete'], $ap));
            $this->assertTrue($u->canActOnTarget($actions['publish'],$ap));
            $this->assertTrue($u->canActOnTarget($actions['verify'], $ap));
        }


        function testCanActOnTarget_Pub_Verify() {
            $n2 = Notebook::getOneFromDb(['notebook_id'=>1003],$this->DB); // owned by 102

            $actions_list = Action::getAllFromDb([],$this->DB);
            $actions = [];
            foreach ($actions_list as $act_elt) {
                $actions[$act_elt->name] = $act_elt;
            }

            $rat = new Role_Action_Target(['last_user_id'=>110, 'role_id'=>3, 'action_id'=>1, 'target_type'=>'notebook', 'target_id'=>1003,'DB'=>$this->DB]);
            $rat->updateDb();

            $this->assertTrue($rat->matchesDb);

            // basic, field user
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $this->assertFalse($n2->flag_workflow_published);
            $this->assertFalse($n2->flag_workflow_validated);

            $this->assertFalse($u->canActOnTarget($actions['view'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['edit'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['delete'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n2));

            $n2->flag_workflow_published = true;
            $n2->updateDb();
            $this->assertTrue($n2->matchesDb);
            $u->clearCaches();

            $this->assertFalse($u->canActOnTarget($actions['view'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['edit'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['delete'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n2));

            $n2->flag_workflow_validated = true;
            $n2->updateDb();
            $this->assertTrue($n2->matchesDb);
            $u->clearCaches();

            $this->assertTrue($u->canActOnTarget($actions['view'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['edit'],$n2));
            $this->assertTrue($u->canActOnTarget($actions['create'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['delete'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['publish'],$n2));
            $this->assertFalse($u->canActOnTarget($actions['verify'],$n2));

        }

            //// auth-related tests

		function testUserUpdatesBaseDbWhenValidAuthDataIsDifferent() {
			$u = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->assertEqual($u->username, Auth_Base::$TEST_USERNAME);
			$this->assertTrue($u->matchesDb);

            $this->auth->lname       = 'Newlastname';
            $this->auth->screen_name       = $this->auth->lname.", ".$this->auth->fname;

			$u->updateDbFromAuth($this->auth);

			$this->assertEqual($u->screen_name, $this->auth->screen_name);
			$this->assertTrue($u->matchesDb);

			$u2 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->assertEqual($u2->username, Auth_Base::$TEST_USERNAME);
			$this->assertEqual($u2->screen_name, $this->auth->screen_name);
		}

		function testUserUpdatesBaseDbWhenAuthDataIsInvalid() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// should let caller/program know there's a problem
			$this->assertFalse($status);
		}

		function testNewUserBaseRecordCreatedWhenAuthDataIsForNewUser() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// should let caller/program know there's a problem
			$this->assertFalse($status);
		}

    }