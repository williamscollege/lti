<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Access extends Db_Linked {
		public static $fields = array('access_id', 'created_at', 'updated_at', 'sheet_id', 'type', 'constraint_id', 'constraint_data', 'broadness');
		public static $primaryKeyField = 'access_id';
		public static $dbTable = 'sus_access';
		public static $entity_type_label = 'sus_access';


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

		public static function cmp($a, $b) {
			if ($a->broadness == $b->broadness) {
				if ($a->broadness == $b->broadness) {
					return 0;
				}
				return ($a->broadness < $b->broadness) ? -1 : 1;
			}
			return ($a->broadness < $b->broadness) ? -1 : 1;
		}


		/* public functions */




	}
