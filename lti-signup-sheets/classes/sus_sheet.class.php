<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class SUS_Sheet extends Db_Linked {
		public static $fields = array('sheet_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'sheetgroup_id', 'name', 'description', 'type', 'begin_date', 'end_date', 'max_total_user_signups', 'max_pending_user_signups', 'flag_alert_owner_change', 'flag_alert_owner_signup', 'flag_alert_owner_imminent', 'flag_alert_admin_change', 'flag_alert_admin_signup', 'flag_alert_admin_imminent', 'flag_private_signups');
		public static $primaryKeyField = 'sheet_id';
		public static $dbTable = 'sus_sheets';
		public static $entity_type_label = 'sus_sheet';

		public $openings;
		public $access;
		public $structured_data;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			// $this->flag_workflow_published = false;
			// $this->flag_workflow_validated = false;
			$this->openings        = array();
			$this->access          = array();
			$this->structured_data = array();
		}

		public static function createNewSheet($owner_user_id, $dbConnection) {
			return new SUS_Sheet([
					//					'sheet_id'                  => 'NEW',
					'created_at'                => util_currentDateTimeString_asMySQL(),
					'updated_at'                => util_currentDateTimeString_asMySQL(),
					'flag_delete'               => FALSE,
					'owner_user_id'             => $owner_user_id,
					'sheetgroup_id'             => 0,
					'name'                      => '',
					'description'               => '',
					'type'                      => '',
					'begin_date'                => util_currentDateTimeString_asMySQL(),
					'end_date'                  => util_currentDateTimeString_asMySQL(),
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
			$this->openings        = array();
			$this->access          = array();
			$this->structured_data = array();
		}

		/* static functions */

		public static function cmp($a, $b) {
			// compare strings as lowercase w/o effecting actual values
			if (strtolower($a->name) == strtolower($b->name)) {
				return 0;
			}
			return (strtolower($a->name) < strtolower($b->name)) ? -1 : 1;
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
		public function renderAsHtmlUsageAlert() {
			// explicitly call the global session variable for use here
			global $USER;

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


		// ***************************
		// Begin: Structured Data
		// ***************************

		# takes: a sheet id and an optional time value (i.e. a full datetime
		# value, as returned from time() or mktime()), an optional
		# opening_id, and an optional signup id
		# returns: a structured data object for that sheet. At the top level
		# is the sheet info as an object. In addition to the basic sheet data,
		# it has
		# group : a signup sheet group object
		# access_controls : an complex structure of access objects (from getAccessPermissions($sheet_id))
		# openings, a time-ordered array of opening objects.
		# Each opening object has all the opening info, and
		# signups : a time-of-signup ordered array of signups
		# users : a limited info users object
		# signups_by_id : an assoc array of sign-ups keyed by signup ID
		# signups_by_user : an assoc array of sign-ups keyed by ID of signed up user


		// cache provides data while eliminating unnecessary DB calls
		public function cacheStructuredData($datetime = 0, $opening_id = 0, $signup_id = 0) {
			if (!$this->structured_data) {
				$this->loadStructuredData($datetime = 0, $opening_id = 0, $signup_id = 0);
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadStructuredData($datetime = 0, $opening_id = 0, $signup_id = 0, $debug = 1) {
			$this->structured_data = [];

			// constants
			$SHEET_GROUP_FIELDS = "
				sg.sheetgroup_id AS sg_id
				,sg.created_at AS sg_created_at
				,sg.updated_at AS sg_updated_at
				,sg.flag_delete AS sg_flag_delete
				,sg.owner_user_id AS sg_owner_user_id
				,sg.flag_is_default AS sg_flag_is_default
				,sg.name AS sg_name
				,sg.description AS sg_description
				,sg.max_g_total_user_signups AS sg_max_g_total_user_signups
				,sg.max_g_pending_user_signups AS sg_max_g_pending_user_signups
			";

			$SHEET_FIELDS = "
				s.sheet_id AS s_id
				,s.created_at AS s_created_at
				,s.updated_at AS s_updated_at
				,s.flag_delete AS s_flag_delete
				,s.owner_user_id AS s_owner_user_id
				,s.sheetgroup_id AS s_sheetgroup_id
				,s.name AS s_name
				,s.description AS s_description
				,s.type AS s_type
				,s.begin_date AS s_begin_date
				,s.end_date AS s_end_date
				,s.max_total_user_signups AS s_max_total_user_signups
				,s.max_pending_user_signups AS s_max_pending_user_signups
				,s.flag_alert_owner_change AS s_flag_alert_owner_change
				,s.flag_alert_owner_signup AS s_flag_alert_owner_signup
				,s.flag_alert_owner_imminent AS s_flag_alert_owner_imminent
				,s.flag_alert_admin_change AS s_flag_alert_admin_change
				,s.flag_alert_admin_signup AS s_flag_alert_admin_signup
				,s.flag_alert_admin_imminent AS s_flag_alert_admin_imminent
				,s.flag_private_signups AS s_flag_private_signups
			";

			// TODO - update/eliminate conversions and usage of UNIXTIME's throughout fxn??.
			$OPENING_FIELDS = "
				o.opening_id AS o_id
				,o.created_at AS o_created_at
				,o.updated_at AS o_updated_at
				,o.flag_delete AS o_flag_delete
				,o.sheet_id AS o_sheet_id
				,o.opening_group_id AS o_opening_group_id
				,o.name AS o_name
				,o.description AS o_description
				,o.max_signups AS o_max_signups
				,o.admin_comment AS o_admin_comment
				,o.begin_datetime AS o_begin_datetime
				,o.end_datetime AS o_end_datetime
				,o.end_datetime - o.begin_datetime AS o_dur_seconds
				,FROM_UNIXTIME(o.begin_datetime,'%Y%m%d') AS o_dateymd
				,FROM_UNIXTIME(o.begin_datetime,'%Y-%m-%d') AS o_date_y_m_d
				,FROM_UNIXTIME(o.begin_datetime,'%l:%i %p') AS o_begin_time_h_m_p
				,FROM_UNIXTIME(o.end_datetime,'%l:%i %p') AS o_end_time_h_m_p
				,FROM_UNIXTIME(o.begin_datetime,'%k:%i') AS o_begin_time_h24_m
				,FROM_UNIXTIME(o.end_datetime,'%k:%i') AS o_end_time_h24_m
				,o.location AS o_location
				,o.admin_comment AS o_admin_comment
			";

			$SIGNUP_FIELDS = "
				su.signup_id AS su_id
				,su.created_at AS su_created_at
				,su.updated_at AS su_updated_at
				,su.flag_delete AS su_flag_delete
				,su.opening_id AS su_opening_id
				,su.signup_user_id AS su_signup_user_id
				,su.admin_comment AS su_admin_comment
			";

			$USER_FIELDS = "
				u.user_id AS u_id
				,u.username AS u_username
				,u.email AS u_email
				,u.first_name AS u_first_name
				,u.last_name AS u_last_name
			";

			$sql = "
				SELECT
					$SHEET_GROUP_FIELDS
					,$SHEET_FIELDS
					,$OPENING_FIELDS
					,$SIGNUP_FIELDS
					,$USER_FIELDS
				FROM
					sus_sheets AS s
					JOIN sus_sheetgroups AS sg ON s.sheetgroup_id = sg.sheetgroup_id
					LEFT OUTER JOIN sus_openings AS o ON o.sheet_id=s.sheet_id AND o.flag_delete != 1
					LEFT OUTER JOIN sus_signups AS su ON su.opening_id = o.opening_id AND su.flag_delete != 1
					LEFT OUTER JOIN users AS u ON u.user_id = su.signup_user_id
				WHERE s.flag_delete != 1
					AND s.sheet_id = $this->sheet_id
			" .
				($datetime ? " AND FROM_UNIXTIME(o.begin_datetime,'%Y%m%d')= FROM_UNIXTIME($datetime,'%Y%m%d')\n" : '') .
				($opening_id ? " AND o.opening_id=$opening_id\n" : '') .
				($signup_id ? " AND su.signup_id=$signup_id\n" : '') .
				"	ORDER BY
					o.begin_datetime,u.last_name,u.username,su.created_at
			";

			if ($debug) {
				echo "sql is:\n";
				util_prePrintR($sql);
			}

			/* Custom SQL using PDO
			 * It uses the sus_recordset_to_array utility function, which result in a true array of results rather than assoc array.
			 * Get a number of records as an array of objects.
			 * Return an array of record objects
			 * @param string $sql       the SQL select query to execute. The first column of this SELECT statement
			 *                          must be a unique value (usually the 'id' field), as it will be used as the key of the
			 *                          returned array.
			 * [dkc: obsolete param] @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
			 * [dkc: obsolete param] @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
			 * @return mixed an array of objects, or false if no records were found or an error occured.
			 */
			// $sql = "SELECT * FROM ".SUS_Sheetgroup::$dbTable;
			// $sql  = "SELECT * FROM sus_sheetgroups INNER JOIN sus_sheets ON sus_sheetgroups.sheetgroup_id = sus_sheets.sheetgroup_id INNER JOIN sus_openings ON sus_openings.sheet_id = sus_sheets.sheet_id INNER JOIN sus_signups ON sus_signups.opening_id = sus_openings.opening_id WHERE sus_sheetgroups.sheetgroup_id = " . htmlentities($s->sheetgroup_id, ENT_QUOTES, 'UTF-8') . " AND  sus_signups.signup_user_id = " . htmlentities($USER->user_id, ENT_QUOTES, 'UTF-8');
			$stmt = $this->prepare($sql);
			$stmt->execute();
			$all_rec_objs = sus_recordset_to_array($stmt->fetchAll(PDO::FETCH_ASSOC)); // TODO - still necessary to convert record set to an array?

			if ($debug) {
				echo "all_rec_objs:\n";
				util_prePrintR($all_rec_objs);
				exit;
			}

			if (!$all_rec_objs) {
				return FALSE;
			}

			$sheet_data = (object)(array(
				's_id'                        => $all_rec_objs[0]->s_id,
				's_created_at'                => $all_rec_objs[0]->s_created_at,
				's_updated_at'                => $all_rec_objs[0]->s_updated_at,
				's_flag_delete'               => $all_rec_objs[0]->s_flag_delete,
				's_owner_user_id'             => $all_rec_objs[0]->s_owner_user_id,
				's_sheetgroup_id'             => $all_rec_objs[0]->s_sheetgroup_id,
				's_name'                      => $all_rec_objs[0]->s_name,
				's_description'               => $all_rec_objs[0]->s_description,
				's_type'                      => $all_rec_objs[0]->s_type,
				's_begin_date'                => $all_rec_objs[0]->s_begin_date,
				's_end_date'                  => $all_rec_objs[0]->s_end_date,
				's_max_total_user_signups'    => $all_rec_objs[0]->s_max_total_user_signups,
				's_max_pending_user_signups'  => $all_rec_objs[0]->s_max_pending_user_signups,
				's_flag_alert_owner_change'   => $all_rec_objs[0]->s_flag_alert_owner_change,
				's_flag_alert_owner_signup'   => $all_rec_objs[0]->s_flag_alert_owner_signup,
				's_flag_alert_owner_imminent' => $all_rec_objs[0]->s_flag_alert_owner_imminent,
				's_flag_alert_admin_change'   => $all_rec_objs[0]->s_flag_alert_admin_change,
				's_flag_alert_admin_signup'   => $all_rec_objs[0]->s_flag_alert_admin_signup,
				's_flag_alert_admin_imminent' => $all_rec_objs[0]->s_flag_alert_admin_imminent,
				's_flag_private_signups'      => $all_rec_objs[0]->s_flag_private_signups
			));

			$sheet_data->group = (object)(array(
				'sg_id'                         => $all_rec_objs[0]->sg_id,
				'sg_created_at'                 => $all_rec_objs[0]->sg_created_at,
				'sg_updated_at'                 => $all_rec_objs[0]->sg_updated_at,
				'sg_flag_delete'                => $all_rec_objs[0]->sg_flag_delete,
				'sg_owner_user_id'              => $all_rec_objs[0]->sg_owner_user_id,
				'sg_flag_is_default'            => $all_rec_objs[0]->sg_flag_is_default,
				'sg_name'                       => $all_rec_objs[0]->sg_name,
				'sg_description'                => $all_rec_objs[0]->sg_description,
				'sg_max_g_total_user_signups'   => $all_rec_objs[0]->sg_max_g_total_user_signups,
				'sg_max_g_pending_user_signups' => $all_rec_objs[0]->sg_max_g_pending_user_signups
			));

			$sheet_data->access_controls = getAccessPermissions($this->sheet_id);

			$sheet_data->openings = array();
			$opening              = array();

			$prior_opening_id = '';
			foreach ($all_rec_objs as $obj) {
				if ($prior_opening_id != $obj->o_id) {
					// close out accumulation
					if ($prior_opening_id) {
						$opening->o_num_signups = count($opening->signups);
						$sheet_data->openings[] = $opening;
					}

					// set up for next bunch
					$opening = (object)(array(
						'o_id'               => $obj->o_id,
						'o_created_at'       => $obj->o_created_at,
						'o_updated_at'       => $obj->o_updated_at,
						'o_flag_delete'      => $obj->o_flag_delete,
						'o_sheet_id'         => $obj->o_sheet_id,
						'o_opening_group_id' => $obj->o_opening_group_id,
						'o_name'             => $obj->o_name,
						'o_description'      => $obj->o_description,
						'o_max_signups'      => $obj->o_max_signups,
						'o_admin_comment'    => $obj->o_admin_comment,
						'o_begin_datetime'   => $obj->o_begin_datetime,
						'o_end_datetime'     => $obj->o_end_datetime,
						'o_dur_seconds'      => $obj->o_dur_seconds,
						'o_dateymd'          => $obj->o_dateymd,
						'o_date_y_m_d'       => $obj->o_date_y_m_d,
						'o_begin_time_h_m_p' => $obj->o_begin_time_h_m_p,
						'o_end_time_h_m_p'   => $obj->o_end_time_h_m_p,
						'o_begin_time_h24_m' => $obj->o_begin_time_h24_m,
						'o_end_time_h24_m'   => $obj->o_end_time_h24_m,
						'o_location'         => $obj->o_location,
						'o_admin_comment'    => $obj->o_admin_comment
					));
					#            signups : a time-of-signup ordered array of signups
					#            signups_by_id : an assoc array of signups keyed by signup ID
					#            signups_by_user : an assoc array of signups keyed by ID of signed up user
					$opening->signups         = array();
					$opening->signups_by_id   = array();
					$opening->signups_by_user = array();
					$prior_opening_id         = $obj->o_id;
				}
				if ($obj->su_id) {
					$user                                                  = (object)(array(
						'usr_id'         => $obj->usr_id,
						'usr_username'   => $obj->usr_username,
						'usr_email'      => $obj->usr_email,
						'usr_first_name' => $obj->usr_first_name,
						'usr_last_name'  => $obj->usr_last_name
					));
					$su                                                    = (object)(array(
						'su_id'             => $obj->su_id,
						'su_created_at'     => $obj->su_created_at,
						'su_updated_at'     => $obj->su_updated_at,
						'su_flag_delete'    => $obj->su_flag_delete,
						'su_opening_id'     => $obj->su_opening_id,
						'su_signup_user_id' => $obj->su_signup_user_id,
						'su_admin_comment'  => $obj->su_admin_comment,
						'user'              => $user
					));
					$opening->signups[]                                    = $su;
					$opening->signups_by_id["{$obj->su_id}"]               = $su;
					$opening->signups_by_user["{$obj->su_signup_user_id}"] = $su;
				}
			}
			// don't forget to add that last accumulated info!
			$sheet_data->openings[] = $opening;

			if ($debug) {
				echo "structured_data is:\n";
				util_prePrintR($this->structured_data);
				exit;
			}

			$this->structured_data = $sheet_data;
		}

		////////////////////////////////////////////////////////////////////////////////////////

		// takes: a sheet id
		// returns: a complex data structure, holding both organized arrays of
		//  access objects and organized arrays object ids. The top level keys
		//  are the access types (e.g. 'by course','byinstr', etc.), or the
		//  string 'data_or_ids_of_' followed by the types
		//  (e.g. 'data_or_ids_of_bycourse'), or the string 'keyed_' followed by
		//  the types (e.g. 'keyed_byuser'). The second levels are lists approp
		//  access objects for the type keys, lists of the constraint data or
		//  constraint id objects for the data_or_ids_of_ keys, and assoc arrays
		//  of access object ids keyed by constraint data or constraint id for the
		//  keyed_ keys.

		public function getAccessPermissions($sheet_id, $access_type = '', $debug = 1) {
			$ACCESS_FIELDS = "
				ac.access_id AS a_id
				,ac.created_at AS a_created_at
				,ac.updated_at AS a_updated_at
				,ac.sheet_id AS a_sheet_id
				,ac.type AS a_type
				,ac.constraint_id AS a_constraint_id
				,ac.constraint_data AS a_constraint_data
				,ac.broadness AS a_broadness
			";

			$sql = "
			SELECT
				$ACCESS_FIELDS
			FROM
				sus_access AS ac
			WHERE
				ac.sheet_id = $sheet_id
				" . ($access_type ? "  AND ac.type='$access_type'" : '') . "
			ORDER BY
				type
		";

			if ($debug) {
				echo "sql is:\n";
				util_prePrintR($sql);
			}

			/* Custom SQL using PDO
			 * It uses the sus_recordset_to_array utility function, which result in a true array of results rather than assoc array.
			 * Get a number of records as an array of objects.
			 * Return an array of record objects
			 * @param string $sql       the SQL select query to execute. The first column of this SELECT statement
			 *                          must be a unique value (usually the 'id' field), as it will be used as the key of the
			 *                          returned array.
			 * [dkc: obsolete param] @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
			 * [dkc: obsolete param] @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
			 * @return mixed an array of objects, or false if no records were found or an error occured.
			 */
			// $sql = "SELECT * FROM ".SUS_Sheetgroup::$dbTable;
			// $sql  = "SELECT * FROM sus_sheetgroups INNER JOIN sus_sheets ON sus_sheetgroups.sheetgroup_id = sus_sheets.sheetgroup_id INNER JOIN sus_openings ON sus_openings.sheet_id = sus_sheets.sheet_id INNER JOIN sus_signups ON sus_signups.opening_id = sus_openings.opening_id WHERE sus_sheetgroups.sheetgroup_id = " . htmlentities($s->sheetgroup_id, ENT_QUOTES, 'UTF-8') . " AND  sus_signups.signup_user_id = " . htmlentities($USER->user_id, ENT_QUOTES, 'UTF-8');
			$stmt = $this->prepare($sql);
			$stmt->execute();
			$access_objs = sus_recordset_to_array($stmt->fetchAll(PDO::FETCH_ASSOC)); // TODO - still necessary to convert record set to an array?

			if ($debug) {
				echo "access_objs is:\n";
				util_prePrintR($access_objs);
				exit;
			}

			// convert to approp hierarchical array structure
			$ret = array();
			foreach ($access_objs as $access) {
				if (!isset($ret[$access->a_type])) {
					$ret[$access->a_type]                     = array();
					$ret['data_or_ids_of_' . $access->a_type] = array();
					$ret['keyed_' . $access->a_type]          = array();
				}
				$ret[$access->a_type][] = $access;
				if ($access->a_constraint_id) {
					$ret['data_or_ids_of_' . $access->a_type][]                = $access->a_constraint_id;
					$ret['keyed_' . $access->a_type][$access->a_constraint_id] = $access->a_id;
				}
				else {
					if ($access->a_constraint_data) {
						$ret['data_or_ids_of_' . $access->a_type][]                  = $access->a_constraint_data;
						$ret['keyed_' . $access->a_type][$access->a_constraint_data] = $access->a_id;
					}
				}
			}
			if ($debug) {
				echo "ret is:\n";
				util_prePrintR($ret);
				exit;
			}

			return $ret;
		}


		////////////////////////////////////////////////////////////////////////////////////////
		/// REPLACED FUNCTION
		/// this replacement function would normally be in /lib/dmlib.ph, except
		/// those functions DON'T WORK! Or at least, not the way they should.
		////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This is a utility function that converts a record set to an array. The original version converted it
		 * to an associative array, keyed by the first column of the query. However, what I really need is an
		 * actual array, with ordinal keys, and one element per record returned. Stupid moodle.
		 *
		 * NOTE: this relies on a mysql back end, I've taken out all the oracle related hacks
		 *
		 * @param object an ADODB RecordSet object.
		 * @return mixed mixed an array of objects, or false if an error occured or the RecordSet was empty.
		 */
		public function sus_recordset_to_array($rs) {

			if ($rs && !rs_EOF($rs)) {
				$objects = array();
				if ($records = $rs->GetRows()) {
					foreach ($records as $record) {
						$objects[] = (object)$record;
					}
					return $objects;
				}
				else {
					return FALSE;
				}
			}
			else {
				return FALSE;
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////

		// ***************************
		// End: Structured Data
		// ***************************


	}
