<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Signup extends Db_Linked {
		public static $fields = array('signup_id', 'created_at', 'updated_at', 'flag_deleted', 'last_user_id', 'sus_opening_id', 'signup_user_id', 'admin_comment');
		public static $primaryKeyField = 'signup_id';
		public static $dbTable = 'sus_signups';
		public static $entity_type_label = 'sus_signup';



		public static function cmp($a, $b) {
			if ($a->created_at == $b->created_at) {
				if ($a->created_at == $b->created_at) {
					return 0;
				}
				return ($a->created_at < $b->created_at) ? -1 : 1;
			}
			return ($a->created_at < $b->created_at) ? -1 : 1;
		}
	}
