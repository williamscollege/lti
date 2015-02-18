<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Signup extends Db_Linked {
		public static $fields = array('signup_id', 'created_at', 'updated_at', 'flag_delete', 'opening_id', 'signup_user_id', 'admin_comment');
		public static $primaryKeyField = 'signup_id';
		public static $dbTable = 'sus_signups';
		public static $entity_type_label = 'sus_signup';


		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;

		}

		// static factory function to populate new object with desired base values
		public static function createNewSignup($dbConnection) {
			return new SUS_Signup([
				'signup_id'      => 'NEW',
				'created_at'     => util_currentDateTimeString_asMySQL(),
				'updated_at'     => util_currentDateTimeString_asMySQL(),
				'flag_delete'    => 0,
				'opening_id'     => 0,
				'signup_user_id' => 0,
				'admin_comment'  => '',
				'DB'             => $dbConnection
			]);
		}

		public function clearCaches() {

		}

		/* static functions */

		public static function cmp($a, $b) {
			if ($a->created_at == $b->created_at) {
				if ($a->created_at == $b->created_at) {
					return 0;
				}
				return ($a->created_at < $b->created_at) ? -1 : 1;
			}
			return ($a->created_at < $b->created_at) ? -1 : 1;
		}


		/* public functions */

		// mark this object as deleted as well as any lower dependent items
		public function cascadeDelete() {
			// mark signup as deleted (at this time, deleting a single opening has no dependencies worth pursuing)
			$this->doDelete();
		}

		public function renderAsHtmlShortWithControls($userDisplayFullname, $userDisplayUsername) {
			$rendered = '';

			$rendered .= "<a href=\"#\" class=\"sus-edit-signup\" data-for-opening-id=\"" . $this->opening_id . "\" data-for-signup-id=\"" . $this->signup_id . "\" data-for-username=\"" . $userDisplayUsername . "\"  data-for-signup-admin-comment=\"" . $this->admin_comment . "\" title=\"Edit signup\"><i class=\"glyphicon glyphicon-wrench\"></i> </a>";
			$rendered .= "<a href=\"#\" class=\"sus-delete-signup wms-custom-delete\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $this->opening_id . "\" data-for-signup-id=\"" . $this->signup_id . "\" data-for-signup-name=\"" . $userDisplayFullname . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i> </a>";
			$rendered .= $userDisplayFullname;
			$rendered .= ' <span class="small">('.util_datetimeFormatted($this->created_at).')</span> ';
			if ($this->admin_comment) {
				$rendered .= '<div class="signup-admin-comment-display">'.$this->admin_comment.'</div>';
			}

			return $rendered;
		}

		public function renderAsListItemShortWithControls($userForThisSignup = ''){
			if (! $userForThisSignup) {
				$userForThisSignup = User::getOneFromDb(['user_id'=>$this->signup_user_id],$this->dbConnection);
			}

			$rendered = '';

			$rendered .= "<li  data-for-signup-created_at=\"".$this->created_at."\" data-for-firstname=\"" . $userForThisSignup->first_name . "\" data-for-lastname=\"" . $userForThisSignup->last_name . "\" data-for-signup-id=\"" . $this->signup_id . "\">";
			$rendered .= $this->renderAsHtmlShortWithControls($userForThisSignup->first_name . " " . $userForThisSignup->last_name, $userForThisSignup->username);
			$rendered .= "</li>";

			return $rendered;
		}

	}
