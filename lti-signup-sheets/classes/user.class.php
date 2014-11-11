<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class User extends Db_Linked {
		public static $fields = array('user_id', 'created_at', 'updated_at', 'username', 'screen_name', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete');
		public static $primaryKeyField = 'user_id';
		public static $dbTable = 'users';
        public static $entity_type_label = 'user';

        public $cached_roles;
        public $cached_role_action_targets_hash_by_id;
        public $cached_role_action_targets_hash_by_target_type_by_id;
        public $cached_role_action_targets_hash_by_action_name_by_id;

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
            $this->cached_roles = array();
            $this->cached_role_action_targets_hash_by_id = array();
            $this->cached_role_action_targets_hash_by_target_type_by_id = array();
            $this->cached_role_action_targets_hash_by_action_name_by_id = array();		}

        public function clearCaches() {
            $this->cached_roles = array();
            $this->cached_role_action_targets_hash_by_id = array();
            $this->cached_role_action_targets_hash_by_target_type_by_id = array();
            $this->cached_role_action_targets_hash_by_action_name_by_id = array();
        }

		public static function cmp($a, $b) {
			if ($a->username == $b->username) {
				if ($a->screen_name == $b->screen_name) {
							return 0;
                }
                return ($a->screen_name < $b->screen_name) ? -1 : 1;
			}
			return ($a->username < $b->username) ? -1 : 1;
		}

        // returns: a very basic HTML representation of the user
        public function renderMinimal($flag_linked=false) {

            $enclosed = htmlentities($this->screen_name);
            if ($flag_linked) {
                $enclosed = '<a href="'.APP_ROOT_PATH.'/app_code/user.php?user_id='.$this->user_id.'">'.$enclosed.'</a>';
            }

            return '<div class="rendered-object user-render user-render-minimal user-render-'.$this->user_id.'" data-for-user="'.$this->user_id.'" data-user_screen_name="'.htmlentities($this->screen_name).'">'.$enclosed.'</div>';
        }

        // returns: an HTML representation of the user with a little extra info available as a mouse-over
        public function render($flag_linked=false) {
            return '<div class="rendered-object user-render user-render-minimal user-render-'.$this->user_id.'" data-for-user="'.$this->user_id.'" data-user_screen_name="'.htmlentities($this->screen_name).'">'.$this->screen_name.'</div>';
        }

        // returns: an HTML representation of the user with detailed extra info available in a subsidiary div (so it can be controlled via css
        public function renderRich($flag_linked=false) {
            $info = '<div class="rendered-object user-render user-render-minimal user-render-'.$this->user_id.'" data-for-user="'.$this->user_id.'" data-user_screen_name="'.htmlentities($this->screen_name).'">'.$this->screen_name.'</div>';

            return $info;
        }

		public function updateDbFromAuth($auth) {
			//echo "doing db update<br/>\n";
			//$this->refreshFromDb();

			// if we're passed in an array of auth data, convert it to an object
			if (is_array($auth)) {
                if ((! $auth['lastname']) || (! $auth['firstname'])) {
                    return FALSE;
                }
				$a              = new Auth_Base();
				$a->username    = $auth['username'];
                $a->screen_name = $auth['lastname'].', '.$auth['firstname'];
				$auth           = $a;
			} else {
                if ((! $auth->lname) || (! $auth->fname)) {
                    return FALSE;
                }
                $auth->screen_name = $auth->lname.', '.$auth->fname;
            }

			// update info if changed
			if ($this->screen_name != $auth->screen_name) {
				$this->screen_name = $auth->screen_name;
			}

			//User::getOneFromDb(['username'=>$this->username],$this->dbConnection)
			$this->updateDb();
			//echo "TESTUSERIDUPDATED=" . $this->user_id . "<br>";

			return TRUE;
		}


        public function getRoles() {
            $user_roles = array();
            $user_roles = User_Role::getAllFromDb(['user_id' => $this->user_id],$this->dbConnection);
            if (count($user_roles) <= 0) { return array(Role::getOneFromDb(['name'=>'public'],$this->dbConnection)); }

//            $roles = Role::getAllFromDb(['role_id'=>array_map(function($e){return $e->role_id;},$user_roles)],
              $roles = Role::getAllFromDb(['role_id'=> Db_Linked::arrayOfAttrValues($user_roles,'role_id')],
                $this->dbConnection);
            return $roles;
        }

        public function cacheRoles() {
            if (! $this->cached_roles) {
                $this->cached_roles = $this->getRoles();
            }
        }

        public function cacheRoleActionTargets() {
            if (! $this->cached_role_action_targets_hash_by_id) {

                $this->cacheRoles();

                $this->cached_role_action_targets_hash_by_id = array();

                $this->cached_role_action_targets_hash_by_target_type_by_id = array();
                $this->cached_role_action_targets_hash_by_action_name_by_id = array();

                foreach ($this->cached_roles as $r) {

//                    util_prePrintR($r);

                    $rats = $r->getRoleActionTargets();

//                    util_prePrintR($rats);

                    foreach ($rats as $rat) {
                        $this->cached_role_action_targets_hash_by_id[$rat->role_action_target_link_id] = $rat;

                        if (! array_key_exists($rat->target_type,$this->cached_role_action_targets_hash_by_target_type_by_id)) {
                            $this->cached_role_action_targets_hash_by_target_type_by_id[$rat->target_type] = array();
                        }
                        $this->cached_role_action_targets_hash_by_target_type_by_id[$rat->target_type][$rat->role_action_target_link_id] = $rat;

                        $action_name = $rat->getAction()->name;
                        if (! array_key_exists($action_name,$this->cached_role_action_targets_hash_by_action_name_by_id)) {
                            $this->cached_role_action_targets_hash_by_action_name_by_id[$action_name] = array();
                        }
                        $this->cached_role_action_targets_hash_by_action_name_by_id[$action_name][$rat->role_action_target_link_id] = $rat;
                    }
                }
            }
        }


        public function canActOnTarget($action,$target) {
            // system admin -> always yes
            // owner of target -> always yes, except for verification
            // all other situatons -> check role action targets
            //   - matching globals -> yes
            //   - specifics
            //      + gets messy
            //   - otherwise -> no

            if (is_string($action)) {
                global $ACTIONS;
                $action = $ACTIONS[$action];
            }

            // system admin -> always yes
            if ($this->flag_is_system_admin) {
                return true;
            }

            // owner of target -> always yes, except for verification
            if ($target->user_id == $this->user_id) {
                if ($action->name != 'verify') { return true; }
            }

            // all other situatons -> check role action targets
            $this->cacheRoleActionTargets();

            //   - matching globals -> yes
            $target_global_type = Role_Action_Target::getGlobalTargetTypeForObject($target);
            if (in_array($target_global_type,array_keys($this->cached_role_action_targets_hash_by_target_type_by_id))) {
                foreach ($this->cached_role_action_targets_hash_by_target_type_by_id[$target_global_type] as $glob_rat) {
                    if ($glob_rat->action_id == $action->action_id) {
                        return true;
                    }
                }
            }

            //   - specifics
            //      + gets messy

            // if the allowed target types do not contain the specific type of the target in question, then no need to go further
            $target_specific_type = Role_Action_Target::getSpecificTargetTypeForObject($target);
            if (! in_array($target_specific_type,array_keys($this->cached_role_action_targets_hash_by_target_type_by_id))) {
                return false;
            }

            // get a list of all the specific ids to check. This gets a bit messy as we have to climb or include a hierarchy depending on what exactly the target is

//            util_prePrintR($target);

            $ids_to_check = array();

            $target_class = get_class($target);
            switch ($target_class) {
                case 'Authoritative_Plant':
                    $ids_to_check = array($target->authoritative_plant_id);
                    break;
                case 'Authoritative_Plant_Extra':
                    // can act on this if can act on the plant
                    return $this->canActOnTarget($action,$target->getAuthoritativePlant());
                    break;
                case 'Metadata_Structure':
                    // can edit this if can edit itself or any parent
                    $ids_to_check = Db_Linked::arrayOfAttrValues($target->getLineage(),'metadata_structure_id');
                    break;
                case 'Metadata_Term_Set':
                    // can edit if can edit any structure that uses this term set
                    $structures = Metadata_Structure::getAllFromDb(['metadata_term_set_id'=>$target->metadata_term_set_id],$this->dbConnection);
                    $ids_to_check = array();
                    foreach ($structures as $s) {
                        $ids_to_check = array_merge($ids_to_check,Db_Linked::arrayOfAttrValues($s->getLineage(),'metadata_structure_id'));
                    }
                    break;
                case 'Metadata_Term_Value':
                    // can edit if can edit any structure that uses the term set for which this is a value
                    return $this->canActOnTarget($action,Metadata_Term_Set::getOneFromDb(['metadata_term_set_id'=>$target->metadata_term_set_id],$this->dbConnection));
                    break;
                case 'Metadata_Reference':
                    // can edit if can edit anything to which this refers
                    return $this->canActOnTarget($action,$target->getReferrent());
                    break;
                case 'Notebook':
                    $ids_to_check = array($target->notebook_id);
                    break;
                case 'Notebook_Page':
                    // can act on if can act on the notebook that contains this page
                    return $this->canActOnTarget($action,$target->getNotebook());
                    break;
                case 'Notebook_Page_Field':
                    // can act on if can act on the notebook that contains the notebook page that this page field
                    return $this->canActOnTarget($action,$target->getNotebookPage()->getNotebook());
                    break;
                case 'Specimen':
                    $ids_to_check = array($target->specimen_id);
                    break;
                case 'Specimen_Image':
                    // can act on if can act on the specimen
                    return $this->canActOnTarget($action,$target->getSpecimen());
                    break;
                default:
                    break;
            }

//            util_prePrintR($ids_to_check);
//            util_prePrintR($this->cached_role_action_targets_hash_by_target_type_by_id);

            foreach ($this->cached_role_action_targets_hash_by_target_type_by_id[$target_specific_type] as $spec_rat) {
                if (($spec_rat->action_id == $action->action_id) && (in_array($spec_rat->target_id,$ids_to_check))) {
                    if ($action->name == 'view') {
                        $actual_target = $spec_rat->getTargets()[0];
                        if (array_key_exists('flag_workflow_published',$actual_target->fieldValues)) {
                            return $actual_target->flag_workflow_published && $actual_target->flag_workflow_validated;
                        }
                    }
                    return true;
                }
            }

            return false;
        }

        public function getAccessibleNotebooks($for_action,$debug_flag = 0) {
            if ($this->flag_is_system_admin) {
                if ($debug_flag) {
                    echo "user is system admin<br/>\n";
                }
                return Notebook::getAllFromDb(['flag_delete' => FALSE],$this->dbConnection);
            }

            if (is_string($for_action)) {
                global $ACTIONS;
                $for_action = $ACTIONS[$for_action];
            }

            $accessible_notebooks_ids = array();
            $roles = $this->getRoles();
            if ($debug_flag) {
                echo "user roles are<br/>\n";
                util_prePrintR($roles);
            }

            foreach (Db_Linked::arrayOfAttrValues($roles,'role_id') as $role_id) {
                $global_check = Role_Action_Target::getAllFromDb(['role_id'=>$role_id,'action_id'=>$for_action->action_id,'target_type'=>'global_notebook'],$this->dbConnection);
                if (count($global_check) > 0) {
                    return Notebook::getAllFromDb(['flag_delete' => FALSE],$this->dbConnection);
                }
                $role_action_targets = Role_Action_Target::getAllFromDb(['role_id'=>$role_id,'action_id'=>$for_action->action_id,'target_type'=>'notebook'],$this->dbConnection);
                foreach ($role_action_targets as $rat) {
                    if (! in_array($rat->target_id,$accessible_notebooks_ids)) {
                        $accessible_notebooks_ids[] = $rat->target_id;
                    }
                }
            }

            $owned_notebooks = Notebook::getAllFromDb(['user_id' => $this->user_id],$this->dbConnection);
            $owned_notebook_ids = Db_Linked::arrayOfAttrValues($owned_notebooks,'notebook_id');

            $additional_notebook_ids = array();
            foreach ($accessible_notebooks_ids as $an_id) {
                if (! in_array($an_id,$owned_notebook_ids)) {
                    $additional_notebook_ids[] = $an_id;
                }
            }

            $additional_notebooks = array();
            if (count($additional_notebook_ids) > 0) {
                $additional_notebooks = Notebook::getAllFromDb(['notebook_id' => $additional_notebook_ids],$this->dbConnection);
            }

            return array_merge($owned_notebooks,$additional_notebooks);
        }

	}
