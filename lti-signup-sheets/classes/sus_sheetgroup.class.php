<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Sheetgroup extends Db_Linked {
		public static $fields = array('sheetgroup_id', 'created_at', 'updated_at', 'flag_deleted', 'owner_user_id', 'flag_is_default', 'name', 'description', 'max_g_total_user_signups', 'max_g_pending_user_signups');
		public static $primaryKeyField = 'sheetgroup_id';
		public static $dbTable = 'sus_sheetgroups';
		public static $entity_type_label = 'sus_sheetgroup';



		//// static methods

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
				return 0;
			}
			return ($a->name < $b->name) ? -1 : 1;
		}




	}
