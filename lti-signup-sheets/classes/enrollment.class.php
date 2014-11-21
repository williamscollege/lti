<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Enrollment extends Db_Linked {
		public static $fields = array('enrollment_id', 'course_idstr', 'user_id', 'course_role_name', 'section_id', 'flag_delete');
		public static $primaryKeyField = 'enrollment_id';
		public static $dbTable = 'enrollments';
        public static $entity_type_label = 'enrollment';



		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;

		}

		public function clearCaches() {
			$this->$cached_xxxxxxx = array();
		}

		public static function cmp($a, $b) {
			if ($a->course_role_name == $b->course_idstr) {
				if ($a->course_idstr == $b->course_idstr) {
					return 0;
				}
				return ($a->course_idstr < $b->course_idstr) ? -1 : 1;
			}
			return ($a->course_idstr < $b->course_idstr) ? -1 : 1;
		}

    }
