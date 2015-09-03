<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class SUS_Signup extends Db_Linked {
		public static $fields = array('signup_id', 'created_at', 'updated_at', 'flag_delete', 'opening_id', 'signup_user_id', 'admin_comment');
		public static $primaryKeyField = 'signup_id';
		public static $dbTable = 'sus_signups';
		public static $entity_type_label = 'sus_signup';


		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			// $this->flag_workflow_published = false;
			// $this->flag_workflow_validated = false;

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
		public function cascadeDelete($usr_object) {
			// mark signup as deleted (at this time, deleting a single opening has no dependencies worth pursuing)
			$this->doDelete();

			#------------------------------------------------#
			# BEGIN: now queue the message
			#------------------------------------------------#
			// alert only for changes to future events
			$o = SUS_Opening::getOneFromDb(['opening_id' => $this->opening_id, 'flag_delete' => [0, 1]], $this->dbConnection); // special condition: fetch the record, even if it is deleted
			if ($o->begin_datetime >= util_currentDateTimeString_asMySQL()) {

				// fetch sheet
				$sheet = SUS_Sheet::getOneFromDb(['sheet_id' => $o->sheet_id, 'flag_delete' => [0, 1]], $this->dbConnection); // special condition: fetch the record, even if it is deleted

				// fetch: user associated with the signup
				$signup_user = User::getOneFromDb(['user_id' => $this->signup_user_id], $this->dbConnection);

				$subject = "[Signup Sheets] " . $usr_object->first_name . " " . $usr_object->last_name . " cancelled your signup on " . $sheet->name;
				$body    = "Hi " . $signup_user->first_name . ",\n\n" . $usr_object->first_name . " " . $usr_object->last_name . " cancelled your signup for:\n\nOpening on: " . date_format(new DateTime($o->begin_datetime), "m/d/Y g:i A") . "\nOn sheet: " . $sheet->name;

				// send to: user whose signup was changed by owner or manager
				if ($usr_object->user_id != $this->signup_user_id) {
					create_and_send_QueuedMessage($this->dbConnection, $signup_user->user_id, $signup_user->email, $subject, $body, $this->opening_id, $sheet->sheet_id);
				}
			}
			#------------------------------------------------#
			# END: now queue the message
			#------------------------------------------------#
		}

		public function renderAsHtmlSignupWithFullControls($userDisplayFullname, $userDisplayUsername) {
			$rendered = '';

			$rendered .= "<a href=\"#\" class=\"sus-edit-signup\" data-for-opening-id=\"" . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($this->signup_id, ENT_QUOTES, 'UTF-8') . "\" data-for-username=\"" . htmlentities($userDisplayUsername, ENT_QUOTES, 'UTF-8') . "\"  data-for-signup-admin-comment=\"" . htmlentities($this->admin_comment, ENT_QUOTES, 'UTF-8') . "\" title=\"Edit signup\"><i class=\"glyphicon glyphicon-wrench\"></i></a>&nbsp;";
			$rendered .= "<a href=\"#\" class=\"sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($this->signup_id, ENT_QUOTES, 'UTF-8') . "\" data-for-signup-name=\"" . htmlentities($userDisplayFullname, ENT_QUOTES, 'UTF-8') . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>&nbsp;";
			$rendered .= htmlentities($userDisplayFullname, ENT_QUOTES, 'UTF-8');
			$rendered .= ' <span class="small">(' . htmlentities($userDisplayUsername, ENT_QUOTES, 'UTF-8') . ', ' . util_datetimeFormatted($this->created_at) . ')</span> ';
			if ($this->admin_comment) {
				$rendered .= '<div class="signup-admin-comment-display">' . htmlentities($this->admin_comment, ENT_QUOTES, 'UTF-8') . '</div>';
			}

			return $rendered;
		}

		public function renderAsListItemSignupWithControls($userForThisSignup = '') {
			if (!$userForThisSignup) {
				$userForThisSignup = User::getOneFromDb(['user_id' => $this->signup_user_id], $this->dbConnection);
			}

			$rendered = '';

			$rendered .= "<li  data-for-signup-created_at=\"" . htmlentities($this->created_at, ENT_QUOTES, 'UTF-8') . "\" data-for-firstname=\"" . htmlentities($userForThisSignup->first_name, ENT_QUOTES, 'UTF-8') . "\" data-for-lastname=\"" . htmlentities($userForThisSignup->last_name, ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($this->signup_id, ENT_QUOTES, 'UTF-8') . "\">";
			$rendered .= $this->renderAsHtmlSignupWithFullControls($userForThisSignup->first_name . " " . $userForThisSignup->last_name, $userForThisSignup->username);
			$rendered .= "</li>";

			return $rendered;
		}

	}
