<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Role_Action_Target extends Db_Linked {
		public static $fields = array('role_action_target_link_id', 'created_at', 'updated_at', 'last_user_id', 'role_id', 'action_id', 'target_type', 'target_id', 'flag_delete');
		public static $primaryKeyField = 'role_action_target_link_id';
		public static $dbTable = 'role_action_target_links';
        public static $entity_type_label = 'role_action_target';


        public static $VALID_TARGET_TYPES =  ['global_notebook', 'global_metadata', 'global_plant', 'global_specimen', 'notebook', 'metadata_structure', 'plant', 'specimen'];

        public static $SORT_PRIORITIES_FOR_TYPES = [
            'global_notebook'=>10,
            'global_metadata'=>20,
            'global_plant'   =>30,
            'global_specimen'=>40,
            'notebook'       =>50,
            'metadata_structure' =>60,
            'plant'          =>70,
            'specimen'       =>80
        ];


        public static function cmp($a, $b) {
            // role, then action, then type, then target
            if ($a->role_id == $b->role_id) {
                if ($a->action_id == $b->action_id) {
                    if (Role_Action_Target::$SORT_PRIORITIES_FOR_TYPES[$a->target_type] == Role_Action_Target::$SORT_PRIORITIES_FOR_TYPES[$b->target_type]) {
                        if ($a->target_id == $b->target_id) {
                            return 0;
                        }
                        if ($a->target_id == 0) {
                            return -1;
                        }
                        if ($b->target_id == 0) {
                            return 1;
                        }
                        switch ($a->target_type) {
                            case 'notebook':
                                return Notebook::cmp($a->getTargets()[0],$b->getTargets()[0]);
                                break;
                            case 'metadata_structure':
                                return Metadata_Structure::cmp($a->getTargets()[0],$b->getTargets()[0]);
                                break;
                            case 'plant':
                                return Authoritative_Plant::cmp($a->getTargets()[0],$b->getTargets()[0]);
                                break;
                            case 'specimen':
                                return Specimen::cmp($a->getTargets()[0],$b->getTargets()[0]);
                                break;
                            default:
                                return 0;
                        }

                    }
                    return (Role_Action_Target::$SORT_PRIORITIES_FOR_TYPES[$a->target_type] < Role_Action_Target::$SORT_PRIORITIES_FOR_TYPES[$b->target_type]) ? -1 : 1;
                }
                return Action::cmp($a->getAction(),$b->getAction());            }
            return Role::cmp($a->getRole(),$b->getRole());
        }

        public static function getGlobalTargetTypeForObject($obj) {
            $obj_class = get_class($obj);
            switch ($obj_class) {
                case 'Authoritative_Plant':
                    return 'global_plant';
                    break;
                case 'Authoritative_Plant_Extra':
                    return 'global_plant';
                    break;
                case 'Metadata_Structure':
                    return 'global_metadata';
                    break;
                case 'Metadata_Term_Set':
                    return 'global_metadata';
                    break;
                case 'Metadata_Term_Value':
                    return 'global_metadata';
                    break;
                case 'Metadata_Reference':
                    return 'global_metadata';
                    break;
                case 'Notebook':
                    return 'global_notebook';
                    break;
                case 'Notebook_Page':
                    return 'global_notebook';
                    break;
                case 'Notebook_Page_Field':
                    return 'global_notebook';
                    break;
                case 'Specimen':
                    return 'global_specimen';
                    break;
                case 'Specimen_Image':
                    return 'global_specimen';
                    break;
                default:
                    return false;
            }
        }

        public static function getSpecificTargetTypeForObject($obj) {
            $obj_class = get_class($obj);
            switch ($obj_class) {
                case 'Authoritative_Plant':
                    return 'plant';
                    break;
                case 'Authoritative_Plant_Extra':
                    return 'plant';
                    break;
                case 'Metadata_Structure':
                    return 'metadata_structure';
                    break;
                case 'Metadata_Term_Set':
                    return 'metadata_structure';
                    break;
                case 'Metadata_Term_Value':
                    return 'metadata_structure';
                    break;
                case 'Metadata_Reference':
                    return 'metadata_structure';
                    break;
                case 'Notebook':
                    return 'notebook';
                    break;
                case 'Notebook_Page':
                    return 'notebook';
                    break;
                case 'Notebook_Page_Field':
                    return 'notebook';
                    break;
                case 'Specimen':
                    return 'specimen';
                    break;
                case 'Specimen_Image':
                    return 'specimen';
                    break;
                default:
                    return false;
            }
        }

        // NOTE: roles are basically fixed; role_id of 1 corresponds to manager, 2 to assistant, 3 to field user, and 4 to public
		public function getRole() {
			return Role::getOneFromDb(['role_id' => $this->role_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function getAction() {
            return Action::getOneFromDb(['action_id' => $this->action_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function getTargets() {
            switch ($this->target_type) {
                case 'global_notebook':
                    return Notebook::getAllFromDb([],$this->dbConnection);
                    break;
                case 'global_metadata':
                    return Metadata_Structure::getAllFromDb([],$this->dbConnection);
                    break;
                case 'global_plant':
                    return Authoritative_Plant::getAllFromDb([],$this->dbConnection);
                    break;
                case 'global_specimen':
                    return Specimen::getAllFromDb([],$this->dbConnection);
                    break;
                case 'notebook':
                    return array(Notebook::getOneFromDb(['notebook_id'=>$this->target_id],$this->dbConnection));
                    break;
                case 'metadata_structure':
                    return array(Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$this->target_id],$this->dbConnection));
                    break;
                case 'plant':
                    return array(Authoritative_Plant::getOneFromDb(['authoritative_id'=>$this->target_id],$this->dbConnection));
                    break;
                case 'specimen':
                    return array(Specimen::getOneFromDb(['specimen_id'=>$this->target_id],$this->dbConnection));
                    break;
                default:
                    return array();
            }
        }
	}
