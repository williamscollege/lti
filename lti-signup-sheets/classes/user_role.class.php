<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class User_Role extends Db_Linked {
		public static $fields = array('user_role_link_id', 'created_at', 'updated_at', 'last_user_id', 'user_id', 'role_id');
		public static $primaryKeyField = 'user_role_link_id';
		public static $dbTable = 'user_role_links';
        public static $entity_type_label = 'user_role';

		// instance attributes
        public $role = '';
        public $user = '';

        // NOTE: roles are basically fixed; role_id of 1 corresponds to teacher, 2 to student, 3 to observer, and 4 to alumni
		public function loadRole() {
			$this->role = Role::getOneFromDb(['role_id' => $this->role_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function loadUser() {
            $this->user = User::getOneFromDb(['user_id' => $this->user_id, 'flag_delete' => FALSE], $this->dbConnection);
        }
	}
