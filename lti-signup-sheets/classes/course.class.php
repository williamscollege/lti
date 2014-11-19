<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Course extends Db_Linked {
		public static $fields = array('course_id', 'course_idstr', 'short_name', 'long_name', 'account_id', 'term_id', 'flag_delete');
		public static $primaryKeyField = 'course_id';
		public static $dbTable = 'courses';
        public static $entity_type_label = 'course';


    }
