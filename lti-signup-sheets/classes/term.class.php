<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Term extends Db_Linked {
		public static $fields = array('term_id', 'term_idstr', 'name', 'start_date', 'end_date', 'flag_delete');
		public static $primaryKeyField = 'term_id';
		public static $dbTable = 'terms';
		public static $entity_type_label = 'term';


	}
