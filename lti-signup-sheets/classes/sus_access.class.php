<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Access extends Db_Linked {
		public static $fields = array('access_id', 'created_at', 'updated_at', 'last_user_id', 'sheet_id', 'type', 'constraint_id', 'constraint_data', 'broadness');
		public static $primaryKeyField = 'access_id';
		public static $dbTable = 'sus_access';
		public static $entity_type_label = 'sus_access';



	}
