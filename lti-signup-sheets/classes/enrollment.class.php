<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Enrollment extends Db_Linked {
		public static $fields = array('enrollment_id', 'course_idstr', 'user_id', 'course_role_name', 'section_idstr', 'flag_delete');
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

		}


		/* static functions */

		# Sort course enrollments by alphabetical course name
		public static function cmp($a, $b) {
			if ($a->course_idstr == $b->course_idstr) {
				if ($a->course_idstr == $b->course_idstr) {
					return 0;
				}
				return ($a->course_idstr < $b->course_idstr) ? -1 : 1;
			}
			return ($a->course_idstr < $b->course_idstr) ? -1 : 1;
		}


		/* public functions */

		// returns: a very basic HTML representation of the object
		public function renderMinimal($flag_linked = FALSE) {

			$enclosed = htmlentities($this->course_idstr);
			if ($flag_linked) {
				$enclosed = '<a href="' . APP_ROOT_PATH . '/app_code/enrollment.php?enrollment_id=' . $this->enrollment_id . '">' . $enclosed . '</a>';
			}

			return '<div class="rendered-object enrollment-render enrollment-render-minimal enrollment-render-' . $this->enrollment_id . '" data-for-enrollment="' . $this->enrollment_id . '" data-course_idstr="' . htmlentities($this->course_idstr) . '">' . $enclosed . '</div>';
		}


	}
