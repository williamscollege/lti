<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

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

		// static factory function to populate new object with desired base values
		public static function createNewAccess($type, $forSheetId, $constraintId, $constraintData, $dbConnection) {
			$broadness = -1;
			switch ($type) {
				case 'adminbyuser':
					$broadness = 1;
					break;
				case 'byuser':
					$broadness = 10;
					break;
				case 'bycourse':
					$broadness = 20;
					break;
				case 'byinstr':
					$broadness = 30;
					break;
				case 'bydept':
					$broadness = 40;
					break;
				case 'bygradyear':
					$broadness = 50;
					break;
				case 'byrole':
					$broadness = 60;
					break;
				case 'byhasaccount':
					$broadness = 60;
					break;
			}

			return new SUS_Access([
				'access_id'       => 'NEW',
				'created_at'      => util_currentDateTimeString_asMySQL(),
				'updated_at'      => util_currentDateTimeString_asMySQL(),
				'sheet_id'        => $forSheetId,
				'type'            => $type,
				'constraint_id'   => $constraintId,
				'constraint_data' => $constraintData,
				'broadness'       => $broadness,
				'DB'              => $dbConnection
			]);
		}

		/* public functions */

		public function doDelete($debug = 0) {
			$sql  = 'DELETE FROM ' . SUS_Access::$dbTable . ' WHERE access_id=' . $this->access_id;
			$stmt = $this->dbConnection->prepare($sql);
			$stmt->execute();
		}


	}
