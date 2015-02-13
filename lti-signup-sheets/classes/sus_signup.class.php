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

		// factory function
		public static function createNewSignup($dbConnection) {
			return new SUS_Signup([
					'signup_id'      => 'NEW',
					'created_at'     => util_currentDateTimeString_asMySQL(),
					'updated_at'     => util_currentDateTimeString_asMySQL(),
					'flag_delete'    => FALSE,
					'opening_id'     => 0,
					'signup_user_id' => 0,
					'admin_comment'  => '',
					'DB'             => $dbConnection]
			);
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


	}
