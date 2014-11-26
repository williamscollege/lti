<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Opening extends Db_Linked {
		public static $fields = array('opening_id', 'created_at', 'updated_at', 'flag_deleted', 'last_user_id', 'sus_sheet_id', 'opening_set_id', 'name', 'description', 'max_signups', 'admin_comment', 'begin_datetime', 'end_datetime', 'location');
		public static $primaryKeyField = 'opening_id';
		public static $dbTable = 'sus_openings';
		public static $entity_type_label = 'sus_opening';



		public static function cmp($a, $b) {
			if ($a->begin_datetime == $b->begin_datetime) {
				if ($a->begin_datetime == $b->begin_datetime) {
					return 0;
				}
				return ($a->begin_datetime < $b->begin_datetime) ? -1 : 1;
			}
			return ($a->begin_datetime < $b->begin_datetime) ? -1 : 1;
		}
	}
