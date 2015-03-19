<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Sheet extends Db_Linked {
		public static $fields = array('sheet_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'sheetgroup_id', 'name', 'description', 'type', 'begin_date', 'end_date', 'max_total_user_signups', 'max_pending_user_signups', 'flag_alert_owner_change', 'flag_alert_owner_signup', 'flag_alert_owner_imminent', 'flag_alert_admin_change', 'flag_alert_admin_signup', 'flag_alert_admin_imminent', 'flag_private_signups');
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
					'begin_date'                => util_currentDateTimeString_asMySQL(),
					'end_date'               => util_currentDateTimeString_asMySQL(),
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

		// render as html the usage details concerning max and pending signup limits, and current counts of each
		public function renderAsHtmlUsageDetails() {
			// explicitly call the global session variable for use here
			global $USER;

			// fetch usage details
			$usage_ary = $USER->fetchUserSignupUsageData($this->sheet_id);

			$rendered = "<div id=\"contents_usage_quotas\"><p>";
			$rendered .= "You may use <span class=\"badge\">" . $this->sus_grammatical_max_signups($usage_ary['sg_max_g_total_user_signups']) . "</span> across all sheets in this group, ";
			$rendered .= "of which <span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($usage_ary['sg_max_g_pending_user_signups']) . "</span> may be for future times. ";
			$rendered .= "Currently you have used ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups($usage_ary['sg_count_g_total_user_signups']) . "</span> in this group, ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($usage_ary['sg_count_g_pending_user_signups']) . "</span> of which are in the future. ";
			$rendered .= "You may have";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups($usage_ary['s_max_total_user_signups']) . "</span> on this sheet, of which ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($usage_ary['s_max_pending_user_signups']) . "</span> may be for future times. ";
			$rendered .= "Currently you have ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups($usage_ary['s_count_total_user_signups']) . "</span> on this sheet, ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($usage_ary['s_count_pending_user_signups']) . "</span> of which are in the future.";
			$rendered .= "</p></div>";

			return $rendered;
		}

		// determine if user has any signups remaining
		//public function checkUserHasSignupsRemaining($UserId = 0) {
		public function renderAsHtmlUsageAlert() {
			// explicitly call the global session variable for use here
			global $USER;

			// TODO - Serverside: enforce ability to signup or not based on param passed back (pretty error code and boolean value)

			// default condition
			$status = '<div id="alert_usage_quotas"></div>';

			// fetch usage details
			$usage_ary = $USER->fetchUserSignupUsageData($this->sheet_id);

			// notation: '_g_' signifies '_group_'
			if (($usage_ary['sg_max_g_total_user_signups'] != -1) && ($usage_ary['sg_count_g_total_user_signups'] >= $usage_ary['sg_max_g_total_user_signups'])) {
				$status = '<div id="alert_usage_quotas"><p><span class="wms-reached-signup-limit label label-danger">You have 0 signups remaining in this sheet group</span></p></div>';
				return $status;
			}
			if (($usage_ary['sg_max_g_pending_user_signups'] != -1) && ($usage_ary['sg_count_g_pending_user_signups'] >= $usage_ary['sg_max_g_pending_user_signups'])) {
				$status = '<div id="alert_usage_quotas"><p><span class="wms-reached-signup-limit label label-danger">You have 0 future signups remaining in this sheet group</span></p></div>';
				return $status;
			}
			if (($usage_ary['s_max_total_user_signups'] != -1) && ($usage_ary['s_count_total_user_signups'] >= $usage_ary['s_max_total_user_signups'])) {
				$status = '<div id="alert_usage_quotas"><p><span class="wms-reached-signup-limit label label-danger">You have 0 signups remaining in this sheet</span></p></div>';
				return $status;
			}
			if (($usage_ary['s_max_pending_user_signups'] != -1) && ($usage_ary['s_count_pending_user_signups'] >= $usage_ary['s_max_pending_user_signups'])) {
				$status = '<div id="alert_usage_quotas"><p><span class="wms-reached-signup-limit label label-danger">You have 0 future signups remaining in this sheet</span></p></div>';
				return $status;
			}

			return $status;
		}

		// ***************************
		// private helper functions
		// ***************************

		// grammar for usage details (verbose)
		private function sus_grammatical_max_signups($num) {
			if (intval($num) < 0) {
				return 'an unlimited number of signups';
			}
			else {
				if (intval($num) == 1) {
					return "1 signup";
				}
				else {
					return "$num signups";
				}
			}
		}

		// grammar for usage details (less verbose)
		private function sus_grammatical_max_signups_less_verbose($num) {
			if (intval($num) < 0) {
				return 'an unlimited number';
			}
			else {
				if (intval($num) == 1) {
					return "1";
				}
				else {
					return "$num";
				}
			}
		}

	}
