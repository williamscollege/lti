<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class User extends Db_Linked {
		public static $fields = array('user_id', 'username', 'email', 'first_name', 'last_name', 'created_at', 'updated_at', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete');
		public static $primaryKeyField = 'user_id';
		public static $dbTable = 'users';
		public static $entity_type_label = 'user';

		public $course_roles;
		public $enrollments;
		public $sheetgroups;
		public $managed_sheets;
		public $my_signups;
		public $signups_on_my_sheets;

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
			$this->managed_sheets       = array();
			$this->my_signups           = array();
			$this->signups_on_my_sheets = array();
		}

		public function clearCaches() {
			$this->course_roles         = array();
			$this->enrollments          = array();
			$this->sheetgroups          = array();
			$this->managed_sheets       = array();
			$this->my_signups           = array();
			$this->signups_on_my_sheets = array();
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
			//echo "doing db update<br/>\n";
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
				array_push($this->managed_sheets, SUS_Sheet::getOneFromDb(['sheet_id' => $sheet->sheet_id], $this->dbConnection));
			}
			usort($this->managed_sheets, 'SUS_Sheet::cmp');
		}

		public function cacheMySignups() {
			if (!$this->my_signups) {
				$this->loadMySignups();
			}
		}

		public function loadMySignups() {
			$this->my_signups = [];

			// get my signups
			$tmpMySignups = SUS_Signup::getAllFromDb(['signup_user_id' => $this->user_id], $this->dbConnection);

			// create hash of opening_id's
			$tmpOpeningIDs = [];
			foreach ($tmpMySignups as $signup) {
				array_push($tmpOpeningIDs, $signup->opening_id);
			}

			// get openings (using hash of IDs)
			$tmpOpenings = SUS_Opening::getAllFromDb(['opening_id' => $tmpOpeningIDs], $this->dbConnection);

			// get everyone's signups for each opening (using hash of IDs)
			$tmpAllSignups = SUS_Signup::getAllFromDb(['opening_id' => $tmpOpeningIDs], $this->dbConnection);

			// count total signup_id's per each opening_id
			$countSignupsPerOpening = array_count_values(array_map(function ($item) {
				return $item->opening_id;
			}, $tmpAllSignups));

			// create hash for output and trim out cruft
			$trimmed_array = [];
			foreach ($tmpOpenings as $opening) {
				array_push($trimmed_array,
					array(
						'opening_id'      => $opening->opening_id,
						'begin_datetime'  => $opening->begin_datetime,
						'end_datetime'    => $opening->end_datetime,
						'current_signups' => $countSignupsPerOpening[$opening->opening_id],
						'max_signups'     => $opening->max_signups,
						'description'     => $opening->description,
						'location'        => $opening->location,
						'name'            => $opening->name
					)
				);
			}

			// this returns a hash, not an object; retrieve values from this hash by referencing keys, instead of by using object properties
			$this->my_signups = $trimmed_array;

			// sort using the hash comparator fxn
			usort($this->my_signups, 'SUS_Opening::cmp_hash');
		}

		public function cacheSignupsOnMySheets() {
			if (!$this->signups_on_my_sheets) {
				$this->loadSignupsOnMySheets();
			}
		}

		public function loadSignupsOnMySheets() {
			$this->signups_on_my_sheets = [];

			// get my sheets
			$tmpSheets = SUS_Sheet::getAllFromDb(['owner_user_id' => $this->user_id], $this->dbConnection);

			// create hash of sheet_id's
			$tmpSheetIDs = [];
			foreach ($tmpSheets as $sheet) {
				array_push($tmpSheetIDs, $sheet->sheet_id);
			}

			// get openings on my sheets
			$tmpOpenings = SUS_Opening::getAllFromDb(['sheet_id' => $tmpSheetIDs], $this->dbConnection);

			// create hash of opening_id's
			$tmpOpeningIDs = [];
			foreach ($tmpOpenings as $opening) {
				array_push($tmpOpeningIDs, $opening->opening_id);
			}

			// get signups on my openings
			$tmpSignups = SUS_Signup::getAllFromDb(['opening_id' => $tmpOpeningIDs], $this->dbConnection);

			// create hash of signup_user_id's
			$tmpSignupUserIDs = [];
			foreach ($tmpSignups as $signup) {
				array_push($tmpSignupUserIDs, $signup->signup_user_id);
			}

			// get user names
			$tmpUsers = User::getAllFromDb(['user_id' => $tmpSignupUserIDs], $this->dbConnection);

			// create hash of user information
			$tmpUserInfo = [];
			foreach ($tmpUsers as $user) {
				$tmpUserInfo[$user->user_id] = array(
					'user_id'   => $user->user_id,
					'full_name' => $user->first_name . ' ' . $user->last_name,
					'email'     => $user->email,
					'username'  => $user->username
				);
			}

			// count total signup_id's per each opening_id
			$countSignupsPerOpening = array_count_values(array_map(function ($item) {
				return $item->opening_id;
			}, $tmpSignups));

			// create hash of signups, include user information (hash), and trim out cruft
			$tmpSignupsWithUserInfo = [];
			foreach ($tmpSignups as $signup) {
				array_push($tmpSignupsWithUserInfo,
					array(
						// signup information
						'opening_id'        => $signup->opening_id,
						'signup_id'         => $signup->signup_id,
						'signup_created_at' => $signup->created_at,
						// user information
						'user_id'           => $tmpUserInfo[$signup->signup_user_id]['user_id'],
						'full_name'         => $tmpUserInfo[$signup->signup_user_id]['full_name'],
						'email'             => $tmpUserInfo[$signup->signup_user_id]['email'],
						'username'          => $tmpUserInfo[$signup->signup_user_id]['username']
					)
				);
			}

			// create hash for output and trim out cruft
			$trimmed_array = [];
			foreach ($tmpOpenings as $opening) {
				// create a hash of signups for each opening
				$signups_for_this_opening = [];
				foreach ($tmpSignupsWithUserInfo as $item) {
					if ($item['opening_id'] == $opening->opening_id) {
						$signups_for_this_opening[] = $item;  // 'user_id' => $item['user_id']
					}
				}
				// omit openings that contain zero signups
				if (isset($countSignupsPerOpening[$opening->opening_id])) {
					$trimmed_array[] = array(
						'opening_id'      => $opening->opening_id,
						'begin_datetime'  => $opening->begin_datetime,
						'end_datetime'    => $opening->end_datetime,
						'current_signups' => $countSignupsPerOpening[$opening->opening_id],
						'max_signups'     => $opening->max_signups,
						'description'     => $opening->description,
						'location'        => $opening->location,
						'name'            => $opening->name,
						'array_signups'   => $signups_for_this_opening
					);
				}
			}
			// util_prePrintR($trimmed_array);

			// this returns a hash, not an object; retrieve values from this hash by referencing keys, instead of by using object properties
			$this->signups_on_my_sheets = $trimmed_array;

			// sort using the hash comparator fxn
			usort($this->signups_on_my_sheets, 'SUS_Opening::cmp_hash');
		}

	}
