<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class User extends Db_Linked {
		public static $fields = array('user_id', 'username', 'email', 'first_name', 'last_name', 'created_at', 'updated_at', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete');
		public static $primaryKeyField = 'user_id';
		public static $dbTable = 'users';
		public static $entity_type_label = 'user';

		public $course_roles;
		public $enrollments;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
			$this->course_roles = array();
			$this->enrollments  = array();
		}

		public function clearCaches() {
			$this->$cached_course_roles = array();
			$this->$cached_enrollments  = array();
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

		//  load course roles for user object
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

		public function cacheCourseRoles() {
			if (!$this->course_roles) {
				$this->loadCourseRoles();
			}
		}

		// load enrollments for user object
		public function loadEnrollments() {
			$this->enrollments = [];
			$this->enrollments = Enrollment::getAllFromDb(['user_id' => $this->user_id], $this->dbConnection);
		}

		public function cacheEnrollments() {
			if (!$this->enrollments) {
				$this->loadEnrollments();
			}
		}


	}
