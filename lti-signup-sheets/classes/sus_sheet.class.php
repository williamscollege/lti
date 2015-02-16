<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Sheet extends Db_Linked {
		public static $fields = array('sheet_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'sheetgroup_id', 'name', 'description', 'type', 'date_opens', 'date_closes', 'max_total_user_signups', 'max_pending_user_signups', 'flag_alert_owner_change', 'flag_alert_owner_signup', 'flag_alert_owner_imminent', 'flag_alert_admin_change', 'flag_alert_admin_signup', 'flag_alert_admin_imminent', 'flag_private_signups');
		public static $primaryKeyField = 'sheet_id';
		public static $dbTable = 'sus_sheets';
		public static $entity_type_label = 'sus_sheet';

		public $openings;
		public $access;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;
			$this->openings = array();
			$this->access   = array();
		}

		public static function createNewSheet($owner_user_id, $dbConnection) {
			return new SUS_Sheet([
					'sheet_id'                  => 'NEW',
					'created_at'                => util_currentDateTimeString_asMySQL(),
					'updated_at'                => util_currentDateTimeString_asMySQL(),
					'flag_delete'               => FALSE,
					'owner_user_id'             => $owner_user_id,
					'sheetgroup_id'             => 0,
					'name'                      => '',
					'description'               => '',
					'type'                      => '',
					'date_opens'                => util_currentDateTimeString_asMySQL(),
					'date_closes'               => util_currentDateTimeString_asMySQL(),
					'max_total_user_signups'    => -1,
					'max_pending_user_signups'  => -1,
					'flag_alert_owner_change'   => 0,
					'flag_alert_owner_signup'   => 0,
					'flag_alert_owner_imminent' => 0,
					'flag_alert_admin_change'   => 0,
					'flag_alert_admin_signup'   => 0,
					'flag_alert_admin_imminent' => 0,
					'flag_private_signups'      => 0,
					'DB'                        => $dbConnection]
			);
		}

		public function clearCaches() {
			$this->openings = array();
			$this->access   = array();
		}

		/* static functions */

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
				return 0;
			}
			return ($a->name < $b->name) ? -1 : 1;
		}


		/* public functions */

		// cache provides data while eliminating unnecessary DB calls
		public function cacheOpenings() {
			if (!$this->openings) {
				$this->loadOpenings();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadOpenings() {
			$this->openings = [];
			$this->openings = SUS_Opening::getAllFromDb(['sheet_id' => $this->sheet_id], $this->dbConnection);
			usort($this->openings, 'SUS_Opening::cmp');
		}

		// cache provides data while eliminating unnecessary DB calls
		public function cacheAccess() {
			if (!$this->access) {
				$this->loadAccess();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadAccess() {
			$this->access = [];
			$this->access = SUS_Access::getAllFromDb(['sheet_id' => $this->sheet_id], $this->dbConnection);
			usort($this->access, 'SUS_Access::cmp');
		}

		// mark this object as deleted as well as any lower dependent items
		public function cascadeDelete() {
			// mark sheet as deleted
			$this->doDelete();

			// for this sheet: fetch openings
			$this->cacheOpenings();

			// mark openings as deleted
			foreach ($this->openings as $opening) {
				$opening->cascadeDelete();
			}
		}

	}
