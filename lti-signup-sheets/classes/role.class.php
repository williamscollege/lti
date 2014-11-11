<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';


	class Role extends Db_Linked {
		public static $fields = array('role_id', 'priority', 'name', 'flag_delete');
		public static $primaryKeyField = 'role_id';
		public static $dbTable = 'roles';
        public static $entity_type_label = 'role';

        public static $VALID_ROLE_NAMES = ['manager','assistant','field user','public'];
        public static $SORT_BY_ROLE_NAMES = ['manager'=>10,'assistant'=>20,'field user'=>30,'public'=>100];

		public static function cmp($a, $b) {
			# The most powerful system admin role is priority = 1; lowest anonymous/guest priority is X
			if ($a->priority == $b->priority) {
                if ($a->name == $b->name) {
    				return 0;
                }
                return (Role::$SORT_BY_ROLE_NAMES[$a->name] < Role::$SORT_BY_ROLE_NAMES[$b->name]) ? -1 : 1;
			}
			return ($a->priority < $b->priority) ? -1 : 1;
		}

        public function getUsers() {
            $urs = User_Role::getAllFromDb(['role_id'=>$this->role_id],$this->dbConnection);

            $user_ids = Db_Linked::arrayOfAttrValues($urs,'user_id');

            $users = User::getAllFromDb(['user_id'=>$user_ids],$this->dbConnection);

            usort($users,'User::cmp');

            return $users;
        }

        public function getRoleActionTargets() {
            $rats = Role_Action_Target::getAllFromDb(['role_id'=>$this->role_id],$this->dbConnection);
            usort($rats,'Role_Action_Target::cmp');
            return $rats;
        }

    }
