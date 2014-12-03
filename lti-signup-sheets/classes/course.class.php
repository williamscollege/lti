<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Course extends Db_Linked {
		public static $fields = array('course_id', 'course_idstr', 'short_name', 'long_name', 'account_idstr', 'term_idstr', 'flag_delete');
		public static $primaryKeyField = 'course_id';
		public static $dbTable = 'courses';
		public static $entity_type_label = 'course';

		public $enrollments;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;
			$this->enrollments = array();
		}

		public function clearCaches() {
			$this->enrollments = array();
		}


		/* static functions */

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

			$enclosed = htmlentities($this->short_name);
			if ($flag_linked) {
				$enclosed = '<a href="' . APP_ROOT_PATH . '/app_code/course.php?course_id=' . $this->course_id . '">' . $enclosed . '</a>';
			}

			return '<div class="rendered-object course-render course-render-minimal course-render-' . $this->course_id . '" data-for-course="' . $this->course_id . '" data-course_idstr="' . htmlentities($this->course_idstr) . '">' . $enclosed . '</div>';
		}

		// cache provides data while eliminating unnecessary DB calls
		public function cacheEnrollments() {
			if (!$this->enrollments) {
				$this->loadEnrollments();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadEnrollments() {
			$this->enrollments = [];
			$this->enrollments = Enrollment::getAllFromDb(['course_idstr' => $this->course_idstr], $this->dbConnection);
			usort($this->enrollments, 'Enrollment::cmp');
		}

	}
