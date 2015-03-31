<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class User extends Db_Linked {
		public static $fields = array('user_id', 'username', 'email', 'first_name', 'last_name', 'created_at', 'updated_at', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete');
		public static $primaryKeyField = 'user_id';
		public static $dbTable = 'users';
		public static $entity_type_label = 'user';

		public $course_roles;
		public $enrollments;
		public $sheetgroups;
		public $sheets;
		public $managed_sheets;
		public $signups_all;
		public $signups_on_my_sheets;
		public $sheet_openings_all;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;

			// ensure that user object is populated from DB
			$this->refreshFromDb();
			# TODO - need to build check (on app_code app_head or setup maybe)
			//			if (!$this->matchesDb) {
			//				// This user does not exist in the database. Abort.
			//				//util_wipeSession();
			//				//util_redirectToAppHome();
			//				die("This user does not exist in the database. Abort.");
			//			}

			$this->course_roles         = array();
			$this->enrollments          = array();
			$this->sheetgroups          = array();
			$this->sheets               = array();
			$this->managed_sheets       = array();
			$this->signups_all          = array();
			$this->signups_on_my_sheets = array();
			$this->sheet_openings_all   = array();
		}

		public function clearCaches() {
			$this->course_roles         = array();
			$this->enrollments          = array();
			$this->sheetgroups          = array();
			$this->sheets               = array();
			$this->managed_sheets       = array();
			$this->signups_all          = array();
			$this->signups_on_my_sheets = array();
			$this->sheet_openings_all   = array();
		}

		/* static functions */

		public static function cmp($a, $b) {
			if ($a->last_name == $b->last_name) {
				if ($a->first_name == $b->first_name) {
					return 0;
				}
				return ($a->first_name < $b->first_name) ? -1 : 1;
			}
			return ($a->last_name < $b->last_name) ? -1 : 1;
		}

		public static function getUsersByCourseRole($role, $dbconn) {
			$users = User::getAllFromDb(['flag_is_banned' => FALSE, 'flag_delete' => FALSE], $dbconn);

			$usersByRole = [];

			foreach ($users as $u) {
				$u->loadCourseRoles();

				foreach ($u->course_roles as $cr) {

					if ($cr->course_role_name == $role) {
						array_push($usersByRole, $u->user_id);
					}

				}
			}
			return $usersByRole;
		}


		/* public functions */

		// returns: a very basic HTML representation of the object
		public function renderMinimal($flag_linked = FALSE) {
			$enclosed = htmlentities($this->last_name) . ', ' . htmlentities($this->first_name);
			if ($flag_linked) {
				$enclosed = '<a href="' . APP_ROOT_PATH . '/app_code/user.php?user_id=' . $this->user_id . '">' . $enclosed . '</a>';
			}

			return '<div class="rendered-object user-render user-render-minimal user-render-' . $this->user_id . '" data-for-user="' . $this->user_id . '" data-user_full_name="' . htmlentities($this->last_name) . ', ' . htmlentities($this->first_name) . '">' . $enclosed . '</div>';
		}

		public function updateDbFromAuth($auth) {
			//echo "doing db update<br />\n";
			//$this->refreshFromDb();

			// if we're passed in an array of auth data, convert it to an object
			if (is_array($auth)) {
				if ((!$auth['lastname']) || (!$auth['firstname'])) {
					return FALSE;
				}
				$a             = new Auth_Base();
				$a->username   = $auth['username'];
				$a->email      = $auth['email'];
				$a->first_name = $auth['firstname'];
				$a->last_name  = $auth['lastname'];
				$auth          = $a;
			}
			else {
				if ((!$auth->lname) || (!$auth->fname)) {
					return FALSE;
				}

			}

			// update info if changed
			if ($this->first_name != $auth->fname) {
				$this->first_name = $auth->fname;
			}
			if ($this->last_name != $auth->lname) {
				$this->last_name = $auth->lname;
			}
			if ($this->email != $auth->email) {
				$this->email = $auth->email;
			}

			//User::getOneFromDb(['username'=>$this->username],$this->dbConnection)
			$this->updateDb();
			//echo "TESTUSERIDUPDATED=" . $this->user_id . "<br>";

			return TRUE;
		}

		// cache provides data while eliminating unnecessary DB calls
		public function cacheCourseRoles() {
			if (!$this->course_roles) {
				$this->loadCourseRoles();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadCourseRoles() {
			$course_role_names = array();
			$this->cacheEnrollments();

			foreach ($this->enrollments as $enr) {
				if (!in_array($enr->course_role_name, $course_role_names)) {
					$course_role_names[] = $enr->course_role_name;
				}
			}

			$this->course_roles = [];
			foreach ($course_role_names as $crname) {
				$this->course_roles[] = Course_Role::getOneFromDb(['course_role_name' => $crname], $this->dbConnection);
			}
			usort($this->course_roles, 'Course_Role::cmp');
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
			$this->enrollments = Enrollment::getAllFromDb(['user_id' => $this->user_id], $this->dbConnection);
			usort($this->enrollments, 'Enrollment::cmp');
		}

		public function cacheSheetgroups() {
			if (!$this->sheetgroups) {
				$this->loadSheetgroups();
			}
		}

		public function loadSheetgroups() {
			$this->sheetgroups = [];
			$this->sheetgroups = SUS_Sheetgroup::getAllFromDb(['owner_user_id' => $this->user_id], $this->dbConnection);
			usort($this->sheetgroups, 'SUS_Sheetgroup::cmp');
		}

		public function cacheSheets() {
			if (!$this->sheetgroups) {
				$this->loadSheetgroups();
			}
			if (!$this->sheets) {
				$this->loadSheets();
			}
		}

		public function loadSheets() {
			$this->cacheSheetgroups();
			$sheetgroup_ids = Db_Linked::arrayOfAttrValues($this->sheetgroups, 'sheetgroup_id');

			$this->sheets = [];
			$this->sheets = SUS_Sheet::getAllFromDb(['sheetgroup_id' => $sheetgroup_ids, 'owner_user_id' => $this->user_id], $this->dbConnection);
			usort($this->sheets, 'SUS_Sheet::cmp');
		}

		public function cacheManagedSheets() {
			if (!$this->managed_sheets) {
				$this->loadManagedSheets();
			}
		}

		public function loadManagedSheets() {
			$this->managed_sheets = [];

			// get all sheets that have been shared with current user by type='adminbyuser'
			$tmp_managed_access = SUS_Access::getAllFromDb(['type' => 'adminbyuser', 'constraint_data' => $this->username], $this->dbConnection);

			foreach ($tmp_managed_access as $sheet) {
				// TODO why is a sheet returned if flag_delete=1? [to replicate, set flag_delete=1 for sheet_id=607 and sheet_id=608]
				// TODO: this type of object to hash problem may exist elsewhere too
				array_push($this->managed_sheets, SUS_Sheet::getOneFromDb(['sheet_id' => $sheet->sheet_id], $this->dbConnection));

				// attempted hack to resolve above issue... aborted.
				//				$one_sheet = SUS_Sheet::getOneFromDb(['sheet_id' => $sheet->sheet_id], $this->dbConnection);
				//				if (isset($one_sheet->flag_delete)) {
				//					array_push($this->managed_sheets, $one_sheet);
				//				}
			}
			usort($this->managed_sheets, 'SUS_Sheet::cmp');
		}

		// TODO - IMPORTANT: standardize/improve class names to be more humanly consistent and readable
		// TODO - IMPORTANT: add comments that describe all of these very similarly named class functions
		public function cacheMySignups() {
			if (!$this->signups_all) {
				$this->loadMySignups();
			}
		}

		public function loadMySignups() {
			$this->signups_all = [];

			// get my signups
			$my_signups_ary = SUS_Signup::getAllFromDb(['signup_user_id' => $this->user_id], $this->dbConnection);

			// create hash of opening_id's
			$openingIDs = Db_Linked::arrayOfAttrValues($my_signups_ary, 'opening_id');

			// get openings (using hash of IDs)
			$openings_ary = SUS_Opening::getAllFromDb(['opening_id' => $openingIDs], $this->dbConnection);

			// get sheets (using hash of IDs)
			$sheetIDs = Db_Linked::arrayOfAttrValues($openings_ary, 'sheet_id');

			// get sheets (using hash of IDs)
			$sheets_ary = SUS_Sheet::getAllFromDb(['sheet_id' => $sheetIDs], $this->dbConnection);

			// get everyone's signups for each opening (using hash of IDs)
			$signups_ary = SUS_Signup::getAllFromDb(['opening_id' => $openingIDs], $this->dbConnection);
			//util_prePrintR($signups_ary);

			// get signup_user_id's (using hash of IDs)
			$signupUserIDs = Db_Linked::arrayOfAttrValues($signups_ary, 'signup_user_id');

			// get user names (using hash of IDs)
			$users_ary = User::getAllFromDb(['user_id' => $signupUserIDs], $this->dbConnection);

			// create hash of user information
			$user_info_ary = [];
			foreach ($users_ary as $user) {
				$user_info_ary[$user->user_id] = array(
					'user_id'   => $user->user_id,
					'full_name' => $user->first_name . ' ' . $user->last_name,
					'email'     => $user->email,
					'username'  => $user->username
				);
			}

			// count total signup_id's per each opening_id
			$countSignupsPerOpening = array_count_values(array_map(function ($item) {
				return $item->opening_id;
			}, $signups_ary));

			// create hash of signups, include user information (hash), and trim out cruft
			$signups_with_user_info_ary = [];
			foreach ($signups_ary as $signup) {
				array_push($signups_with_user_info_ary,
					array(
						// signup information
						'opening_id' => $signup->opening_id,
						'signup_id'  => $signup->signup_id,
						// 'signup_created_at' => $signup->created_at,
						// user information
						'user_id'    => $user_info_ary[$signup->signup_user_id]['user_id'],
						'full_name'  => $user_info_ary[$signup->signup_user_id]['full_name'],
						'email'      => $user_info_ary[$signup->signup_user_id]['email'],
						'username'   => $user_info_ary[$signup->signup_user_id]['username']
					)
				);
			}

			// create hash for output and trim out cruft
			$trimmed_array = [];
			foreach ($openings_ary as $opening) {

				// create a hash of signups for each opening
				$signups_for_this_opening = [];
				foreach ($signups_with_user_info_ary as $item) {
					if ($item['opening_id'] == $opening->opening_id) {
						$signups_for_this_opening[] = $item;    // 'user_id' => $item['user_id']
					}
				}

				// fetch sheet name
				$sheet_name = '';
				foreach ($sheets_ary as $sheet) {
					if ($sheet->sheet_id == $opening->sheet_id) {
						$sheet_name                 = $sheet->name;
						$sheet_flag_private_signups = $sheet->flag_private_signups;
					}
				}

				array_push($trimmed_array,
					array(
						'opening_id'                 => $opening->opening_id,
						'sheet_id'                   => $opening->sheet_id,
						'begin_datetime'             => $opening->begin_datetime,
						'end_datetime'               => $opening->end_datetime,
						'current_signups'            => $countSignupsPerOpening[$opening->opening_id],
						'opening_max_signups'        => $opening->max_signups,
						'opening_description'        => $opening->description,
						'opening_location'           => $opening->location,
						'opening_name'               => $opening->name,
						// 'signup_id'           => $signup_id,
						// 'signup_created_at'   => $signup_created_at,
						'sheet_name'                 => $sheet_name,
						'sheet_flag_private_signups' => $sheet_flag_private_signups,
						'array_signups'              => $signups_for_this_opening
					)
				);
			}

			// this returns a hash, not an object; retrieve values from this hash by referencing keys, instead of by using object properties
			$this->signups_all = $trimmed_array;

			// sort using the hash comparator fxn
			usort($this->signups_all, 'SUS_Opening::cmp_hash');
		}

		public function cacheSignupsOnMySheets() {
			if (!$this->signups_on_my_sheets) {
				$this->loadSignupsOnMySheets();
			}
		}

		public function loadSignupsOnMySheets() {
			$this->signups_on_my_sheets = [];

			// get my sheets
			$sheets_ary = SUS_Sheet::getAllFromDb(['owner_user_id' => $this->user_id], $this->dbConnection);

			// get sheets (using hash of IDs)
			$sheetIDs = Db_Linked::arrayOfAttrValues($sheets_ary, 'sheet_id');

			// get openings on my sheets (using hash of IDs)
			$openings_ary = SUS_Opening::getAllFromDb(['sheet_id' => $sheetIDs], $this->dbConnection);

			// get openings (using hash of IDs)
			$openingIDs = Db_Linked::arrayOfAttrValues($openings_ary, 'opening_id');

			// get signups on my openings (using hash of IDs)
			$signups_ary = SUS_Signup::getAllFromDb(['opening_id' => $openingIDs], $this->dbConnection);

			// get signup_user_id's (using hash of IDs)
			$signupUserIDs = Db_Linked::arrayOfAttrValues($signups_ary, 'signup_user_id');

			// get user names (using hash of IDs)
			$users_ary = User::getAllFromDb(['user_id' => $signupUserIDs], $this->dbConnection);

			// create hash of user information
			$user_info_ary = [];
			foreach ($users_ary as $user) {
				$user_info_ary[$user->user_id] = array(
					'user_id'   => $user->user_id,
					'full_name' => $user->first_name . ' ' . $user->last_name,
					'email'     => $user->email,
					'username'  => $user->username
				);
			}

			// count total signup_id's per each opening_id
			$countSignupsPerOpening = array_count_values(array_map(function ($item) {
				return $item->opening_id;
			}, $signups_ary));

			// create hash of signups, include user information (hash), and trim out cruft
			$signups_with_user_info_ary = [];
			foreach ($signups_ary as $signup) {
				array_push($signups_with_user_info_ary,
					array(
						// signup information
						'opening_id'        => $signup->opening_id,
						'signup_id'         => $signup->signup_id,
						'signup_created_at' => $signup->created_at,
						// user information
						'user_id'           => $user_info_ary[$signup->signup_user_id]['user_id'],
						'full_name'         => $user_info_ary[$signup->signup_user_id]['full_name'],
						'email'             => $user_info_ary[$signup->signup_user_id]['email'],
						'username'          => $user_info_ary[$signup->signup_user_id]['username']
					)
				);
			}

			// create hash for output and trim out cruft
			$trimmed_array = [];
			foreach ($openings_ary as $opening) {

				// create a hash of signups for each opening
				$signups_for_this_opening = [];
				foreach ($signups_with_user_info_ary as $item) {
					if ($item['opening_id'] == $opening->opening_id) {
						$signups_for_this_opening[] = $item;    // 'user_id' => $item['user_id']
					}
				}

				// fetch sheet name
				$sheet_name = '';
				foreach ($sheets_ary as $sheet) {
					if ($sheet->sheet_id == $opening->sheet_id) {
						$sheet_name = $sheet->name;
					}
				}

				// omit openings that contain zero signups
				if (isset($countSignupsPerOpening[$opening->opening_id])) {
					$trimmed_array[] = array(
						'opening_id'          => $opening->opening_id,
						'sheet_id'            => $opening->sheet_id,
						'begin_datetime'      => $opening->begin_datetime,
						'end_datetime'        => $opening->end_datetime,
						'current_signups'     => $countSignupsPerOpening[$opening->opening_id],
						'opening_max_signups' => $opening->max_signups,
						'opening_description' => $opening->description,
						'opening_location'    => $opening->location,
						'opening_name'        => $opening->name,
						'sheet_name'          => $sheet_name,
						'array_signups'       => $signups_for_this_opening
					);
				}
			}

			// this returns a hash, not an object; retrieve values from this hash by referencing keys, instead of by using object properties
			$this->signups_on_my_sheets = $trimmed_array;

			// sort using the hash comparator fxn
			usort($this->signups_on_my_sheets, 'SUS_Opening::cmp_hash');
			//util_prePrintR($this->signups_on_my_sheets);
		}

		public function cacheMyAvailableSheetOpenings() {
			if (!$this->sheet_openings_all) {
				$this->loadMyAvailableSheetOpenings();
			}
		}

		// takes: an optional flag for whether access data should be included in the results
		// returns: an array of sheet objects on which the current user has access to sign up
		// $for_sheet_id = use this to show openings for 1 sheet
		// TODO - verify use of params with moodle use cases
		public function loadMyAvailableSheetOpenings($includeAccessRecords = TRUE, $for_user_id = 0, $for_sheet_id = 0, $for_access_id = 0) {
			$this->sheet_openings_all = [];

			$strServerName = $_SERVER['SERVER_NAME'];
			if (($strServerName == "localhost") OR ($strServerName == "127.0.0.1")) {
				// MySQL connection string
				$connString = mysqli_connect(TESTING_DB_SERVER, TESTING_DB_USER, TESTING_DB_PASS, TESTING_DB_NAME) or
				die("Sorry! You lack proper authentication to the local database.");
			}
			else {
				// MySQL connection string
				$connString = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or
				die("Sorry! You lack proper authentication to the live database.");
			}

			if ($for_user_id < 1) {
				$for_user_id = $this->user_id;
			}

			// if filtering on the access_id, need to get it in the query
			if ($for_access_id > 0) {
				$includeAccessRecords = TRUE;
			}

			$sql = "
				SELECT
				s.sheet_id	AS s_id
				,s.created_at	AS s_created_at
				,s.updated_at	AS s_updated_at
				,s.flag_delete	AS s_flag_delete
				,s.owner_user_id	AS s_owner_user_id
				,s.sheetgroup_id	AS s_sheetgroup_id
				,s.name	AS s_name
				,s.description	AS s_description
				,s.type	AS s_type
				,s.begin_date	AS s_begin_date
				,s.end_date	AS s_end_date
				,s.max_total_user_signups	AS s_max_total_user_signups
				,s.max_pending_user_signups	AS s_max_pending_user_signups
				,s.flag_alert_owner_change	AS s_flag_alert_owner_change
				,s.flag_alert_owner_signup	AS s_flag_alert_owner_signup
				,s.flag_alert_owner_imminent	AS s_flag_alert_owner_imminent
				,s.flag_alert_admin_change	AS s_flag_alert_admin_change
				,s.flag_alert_admin_signup	AS s_flag_alert_admin_signup
				,s.flag_alert_admin_imminent	AS s_flag_alert_admin_imminent
				,s.flag_private_signups AS s_flag_private_signups
				" .
				($includeAccessRecords ? "
				,ac.access_id AS a_id
				,ac.created_at AS a_created_at
				,ac.updated_at AS a_updated_at
				,ac.sheet_id AS a_sheet_id
				,ac.type AS a_type
				,ac.constraint_id AS a_constraint_id
				,ac.constraint_data AS a_constraint_data
				,ac.broadness AS a_broadness" : '') . "
				FROM
				sus_sheets AS s
				JOIN (
					SELECT DISTINCT
						a.sheet_id" .
				($includeAccessRecords ? '
						,a.access_id AS access_id
						,a.created_at AS created_at
						,a.updated_at AS updated_at
						,a.type AS type
						,a.constraint_id AS constraint_id
						,a.constraint_data AS constraint_data
						,a.broadness AS broadness' : '') . "
					FROM
						sus_access AS a
					WHERE
						(a.type='byhasaccount')
						OR
						(a.type='byuser'
						 AND (a.constraint_data = '{$this->username}'))
						OR
						(a.type='byrole'
						 AND a.constraint_data = 'teacher'
						 AND $for_user_id IN (
							SELECT DISTINCT
							usr.user_id
							FROM
							enrollments AS enr
							JOIN course_roles AS crs_role ON crs_role.course_role_name = enr.course_role_name
							JOIN users AS usr ON usr.user_id = enr.user_id
							JOIN courses AS crs ON crs.course_idstr = enr.course_idstr
							WHERE
							crs_role.course_role_name = 'teacher'
							AND usr.user_id = $for_user_id))
						OR
						(a.type='byrole'
						 AND a.constraint_data = 'student'
						 AND $for_user_id IN (
							SELECT DISTINCT
							usr.user_id
							FROM
							enrollments AS enr
							JOIN course_roles AS crs_role ON crs_role.course_role_name = enr.course_role_name
							JOIN users AS usr ON usr.user_id = enr.user_id
							JOIN courses AS crs ON crs.course_idstr = enr.course_idstr
							WHERE
							crs_role.course_role_name = 'student'
							AND usr.user_id = $for_user_id))
						OR
						(a.type='bycourse'
						 AND a.constraint_data IN (
							SELECT DISTINCT
							crs.course_idstr
							FROM
							enrollments AS enr
							JOIN users AS usr ON usr.user_id = enr.user_id
							JOIN courses AS crs ON crs.course_idstr = enr.course_idstr
							WHERE
							usr.user_id = $for_user_id))
						OR
						(a.type='byinstr'
						 AND a.constraint_id IN (
							SELECT DISTINCT
							usr.user_id
							FROM
							enrollments AS enr
							JOIN course_roles AS crs_role ON crs_role.course_role_name = enr.course_role_name
							JOIN users AS usr ON usr.user_id = enr.user_id
							JOIN courses AS crs ON crs.course_idstr = enr.course_idstr
							WHERE
							crs_role.course_role_name = 'teacher'
							AND crs.course_id IN (
								SELECT DISTINCT
									crs.course_id
								FROM
									enrollments AS enr
									JOIN course_roles AS crs_role ON crs_role.course_role_name = enr.course_role_name
									JOIN users AS usr ON usr.user_id = enr.user_id
									JOIN courses AS crs ON crs.course_idstr = enr.course_idstr
								WHERE
									crs_role.course_role_name = 'student'
									AND usr.user_id = $for_user_id
							)))
				" .
				###############################
				# NOTE: this is Williams specific, and is how access by grad year is
				#	handled in our system (i.e. via Williams specific tables) - I've
				#	left this code in a comment in case you want to implement a similar
				#	thing on your own system.
				###############################
				#		"UNION
				#		SELECT DISTINCT
				#			a.sheet_id".
				#($includeAccessRecords?'
				#			,a.access_id AS id
				#			,a.created_at AS created_at
				#			,a.updated_at AS updated_at
				#			,a.type AS type
				#			,a.constraint_id AS constraint_id
				#			,a.constraint_data AS constraint_data
				#			,a.broadness AS broadness':'')."
				#		FROM
				#			sus_access AS a
				#			JOIN wms_card_ps_users AS wcpu ON wcpu.login_id = '{$this->username}' AND wcpu.wms_class=a.constraint_data
				#		WHERE
				#			a.type='bygradyear'".
				###############################
				") AS ac ON s.sheet_id = ac.sheet_id
				WHERE
					s.flag_delete != 1
					AND s.end_date > " . (time() - (1)) .
				//(($for_sheet_id>0 || $for_access_id>0)?"WHERE ":'').
				(($for_access_id > 0) ? "\n	AND ac.access_id = $for_access_id" : '') .
				//(($for_sheet_id>0 && $for_access_id>0)?" AND ":'').
				(($for_sheet_id > 0) ? "\n	AND s.sheet_id = $for_sheet_id" : '') . "
				ORDER BY
					s.name" .
				($includeAccessRecords ? '
				,ac.broadness DESC;' : ';');

			$resultsMyAvailableSheetOpenings = mysqli_query($connString, $sql) or
			die(mysqli_error($connString));

			// debugging
			// echo "\n<pre>$sql</pre>\n\n"; // debugging
			// echo "<br />rows_returned = " . $resultsMyAvailableSheetOpenings->num_rows . "<hr />";  // debugging

			$sheets_with_access_reasons = [];
			$last_sheet_id              = -1;
			// iterate over rs
			$resultsMyAvailableSheetOpenings->data_seek(0);
			while ($row = $resultsMyAvailableSheetOpenings->fetch_assoc()) {
				if ($row['s_id'] != $last_sheet_id) {
					$sheets_with_access_reasons[] = $row;
				}
				$last_sheet_id = $row['s_id'];
			}

			// debugging
			// util_prePrintR($sheets_with_access_reasons);
			// echo $sheets_with_access_reasons[0]['s_type'];

			// close DB connection
			mysqli_close($connString);

			// this returns a hash, not an object; retrieve values from this hash by referencing keys, instead of by using object properties
			$this->sheet_openings_all = $sheets_with_access_reasons;
		}

		#------------------------------------------------#
		# access permissions
		#------------------------------------------------#

		// determine this sheetgroup's max and pending signup limits, and current counts of each
		// determine this sheet's max and pending signup limits, and current counts of each
		public function fetchUserSignupUsageData($SheetId = 0) {

			// 1) sheetgroup: determine max and pending signup limits, and current counts of each

			$s  = SUS_Sheet::getOneFromDb(['sheet_id' => $SheetId], $this->dbConnection);
			$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $s->sheetgroup_id], $this->dbConnection);

			$sheets_in_sg         = SUS_Sheet::getAllFromDb(['sheetgroup_id' => $sg->sheetgroup_id], $this->dbConnection);
			$list_sheet_ids_in_sg = Db_Linked::arrayOfAttrValues($sheets_in_sg, 'sheet_id');

			$openings_in_sg_all         = SUS_Opening::getAllFromDb(['sheet_id' => $list_sheet_ids_in_sg], $this->dbConnection);
			$list_opening_ids_in_sg_all = Db_Linked::arrayOfAttrValues($openings_in_sg_all, 'opening_id');

			$openings_in_sg_future         = SUS_Opening::getAllFromDb(['sheet_id' => $list_sheet_ids_in_sg, 'begin_datetime >=' => util_currentDateTimeString_asMySQL()], $this->dbConnection);
			$list_opening_ids_in_sg_future = Db_Linked::arrayOfAttrValues($openings_in_sg_future, 'opening_id');

			if ($list_opening_ids_in_sg_all) {
				$sg_count_g_total_user_signups = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_sg_all, 'signup_user_id' => $this->user_id], $this->dbConnection));
			}
			if ($list_opening_ids_in_sg_future) {
				$sg_count_g_pending_user_signups = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_sg_future, 'signup_user_id' => $this->user_id], $this->dbConnection));
			}

			// 2) sheet: determine max and pending signup limits, and current counts of each
			$openings_in_one_sheet_all         = SUS_Opening::getAllFromDb(['sheet_id' => $s->sheet_id], $this->dbConnection);
			$list_opening_ids_in_one_sheet_all = Db_Linked::arrayOfAttrValues($openings_in_one_sheet_all, 'opening_id');

			$openings_in_one_sheet_future         = SUS_Opening::getAllFromDb(['sheet_id' => $s->sheet_id, 'begin_datetime >=' => util_currentDateTimeString_asMySQL()], $this->dbConnection);
			$list_opening_ids_in_one_sheet_future = Db_Linked::arrayOfAttrValues($openings_in_one_sheet_future, 'opening_id');

			if ($list_opening_ids_in_one_sheet_all) {
				$s_count_total_user_signups = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_one_sheet_all, 'signup_user_id' => $this->user_id], $this->dbConnection));
			}

			if ($list_opening_ids_in_one_sheet_future) {
				$s_count_pending_user_signups = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_one_sheet_future, 'signup_user_id' => $this->user_id], $this->dbConnection));
			}

			// build array and pass it back
			$resultant_array = [
				'sg_max_g_total_user_signups'     => $sg->max_g_total_user_signups,
				'sg_count_g_total_user_signups'   => $sg_count_g_total_user_signups,
				'sg_max_g_pending_user_signups'   => $sg->max_g_pending_user_signups,
				'sg_count_g_pending_user_signups' => $sg_count_g_pending_user_signups,
				's_max_total_user_signups'        => $s->max_total_user_signups,
				's_count_total_user_signups'      => $s_count_total_user_signups,
				's_max_pending_user_signups'      => $s->max_pending_user_signups,
				's_count_pending_user_signups'    => $s_count_pending_user_signups
			];

			return $resultant_array;
		}

		// SECURITY: enforce whether user may create a new signup
		public function isUserAllowedToAddNewSignup($SheetId) {

			// fetch usage details
			$usage_ary = $this->fetchUserSignupUsageData($SheetId);

			// notation: '_g_' signifies '_group_'
			if (($usage_ary['sg_max_g_total_user_signups'] != -1) && ($usage_ary['sg_count_g_total_user_signups'] >= $usage_ary['sg_max_g_total_user_signups'])) {
				return FALSE;
			}
			if (($usage_ary['sg_max_g_pending_user_signups'] != -1) && ($usage_ary['sg_count_g_pending_user_signups'] >= $usage_ary['sg_max_g_pending_user_signups'])) {
				return FALSE;
			}
			if (($usage_ary['s_max_total_user_signups'] != -1) && ($usage_ary['s_count_total_user_signups'] >= $usage_ary['s_max_total_user_signups'])) {
				return FALSE;
			}
			if (($usage_ary['s_max_pending_user_signups'] != -1) && ($usage_ary['s_count_pending_user_signups'] >= $usage_ary['s_max_pending_user_signups'])) {
				return FALSE;
			}

			return TRUE;
		}

		// SECURITY: check if user owns or manages this sheet (param required): return boolean value
		public function isUserAllowedToManageSheet($sheet_id = 0) {
			$this->cacheSheets();
			$this->cacheManagedSheets();

			// fetch relevant hashes of sheet_id values
			$fetch_sheet_ids         = Db_Linked::arrayOfAttrValues($this->sheets, 'sheet_id');
			$fetch_managed_sheet_ids = Db_Linked::arrayOfAttrValues($this->managed_sheets, 'sheet_id');
			// util_prePrintR($fetch_sheet_ids);
			// util_prePrintR($fetch_managed_sheet_ids);

			if (in_array($sheet_id, $fetch_sheet_ids)) {
				return TRUE;
			}

			if (in_array($sheet_id, $fetch_managed_sheet_ids)) {
				return TRUE;
			}

			return FALSE;
		}

		// SECURITY: check if user has been granted access to signup on this sheet_id
		public function isUserAllowedToAccessSheet($sheet_id = 0) {
			$this->cacheMyAvailableSheetOpenings();
			// util_prePrintR($this->sheet_openings_all);

			foreach ($this->sheet_openings_all as $sheet) {
				if ($sheet_id == $sheet['s_id']) {
					return TRUE;
				}
			}

			return FALSE;
		}

	}
