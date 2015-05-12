<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class Term extends Db_Linked {
		public static $fields = array('term_id', 'canvas_term_id', 'term_idstr', 'name', 'start_date', 'end_date', 'flag_delete');
		public static $primaryKeyField = 'term_id';
		public static $dbTable = 'terms';
		public static $entity_type_label = 'term';


		public $courses;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			// $this->flag_workflow_published = false;
			// $this->flag_workflow_validated = false;
			$this->courses = array();
		}

		public function clearCaches() {
			$this->courses = array();
		}

		/* static functions */

		public static function cmp($a, $b) {
			if ($a->start_date == $b->start_date) {
				if ($a->start_date == $b->start_date) {
					return 0;
				}
				return ($a->start_date < $b->start_date) ? -1 : 1;
			}
			return ($a->start_date < $b->start_date) ? -1 : 1;
		}


		/* public functions */

		// cache provides data while eliminating unnecessary DB calls
		public function cacheCourses() {
			if (!$this->courses) {
				$this->loadCourses();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadCourses() {
			$this->courses = [];
			$this->courses = Course::getAllFromDb(['term_idstr' => $this->term_idstr], $this->dbConnection);
			usort($this->courses, 'Course::cmp');
		}

	}
