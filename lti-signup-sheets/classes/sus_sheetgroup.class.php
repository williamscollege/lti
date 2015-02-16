<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Sheetgroup extends Db_Linked {
		public static $fields = array('sheetgroup_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'flag_is_default', 'name', 'description', 'max_g_total_user_signups', 'max_g_pending_user_signups');
		public static $primaryKeyField = 'sheetgroup_id';
		public static $dbTable = 'sus_sheetgroups';
		public static $entity_type_label = 'sus_sheetgroup';

		public $sheets;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;
			$this->sheets = array();
		}

		public function clearCaches() {
			$this->sheets = array();
		}

		/* static functions */

		// static factory function to populate new object with desired base values
		public static function createNewSheetgroupForUser($user_id, $name, $description, $dbconnection) {
			// 'sheetgroup_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'flag_is_default', 'name', 'description', 'max_g_total_user_signups', 'max_g_pending_user_signups'
			$n = new SUS_Sheetgroup([
				'created_at'                 => util_currentDateTimeString_asMySQL(),
				'updated_at'                 => util_currentDateTimeString_asMySQL(),
				'flag_delete'                => 0,
				'owner_user_id'              => $user_id,
				'flag_is_default'            => 1,
				'name'                       => $name,
				'description'                => $description,
				'max_g_total_user_signups'   => -1,
				'max_g_pending_user_signups' => -1,
				'DB'                         => $dbconnection]);
			return $n;
		}

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
				return 0;
			}
			return ($a->name < $b->name) ? -1 : 1;
		}

		/* public functions */

		// cache provides data while eliminating unnecessary DB calls
		public function cacheSheets() {
			if (!$this->sheets) {
				$this->loadSheets();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadSheets() {
			$this->sheets = [];
			$this->sheets = SUS_Sheet::getAllFromDb(['sheetgroup_id' => $this->sheetgroup_id], $this->dbConnection);
			usort($this->sheets, 'SUS_Sheet::cmp');
		}

		// mark this object as deleted as well as any lower dependent items
		public function cascadeDelete() {
			// mark sheetgroup as deleted
			$this->doDelete();

			// for this sheetgroup: fetch sheets
			$this->cacheSheets();

			// mark sheets as deleted
			foreach ($this->sheets as $sheet) {
				$sheet->cascadeDelete();
			}
		}

	}
