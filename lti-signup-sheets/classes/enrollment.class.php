<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Enrollment extends Db_Linked {
		public static $fields = array('enrollment_id', 'course_idstr', 'user_id', 'course_role_name', 'section_id', 'flag_delete');
		public static $primaryKeyField = 'enrollment_id';
		public static $dbTable = 'enrollments';
        public static $entity_type_label = 'enrollment';


    }
