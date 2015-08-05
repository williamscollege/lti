<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class SUS_EventLog extends Db_Linked {
		public static $fields = array('eventlog_id', 'user_id', 'flag_success', 'event_action', 'event_action_id', 'event_details', 'event_filepath', 'user_agent_string', 'event_datetime');
		public static $primaryKeyField = 'eventlog_id';
		public static $dbTable = 'sus_eventlogs';
		public static $entity_type_label = 'sus_eventlogs';


		public function clearCaches() {

		}


		// static factory function to populate new object with desired base values
		public static function createNewEventLog($user_id, $flag_success, $event_action, $event_action_id, $event_details, $dbConnection) {
			return new SUS_EventLog(['DB' => $dbConnection
				, 'user_id'               => $user_id
				, 'flag_success'          => $flag_success
				, 'event_action'          => $event_action
				, 'event_action_id'       => $event_action_id
				, 'event_details'         => $event_details
				, 'event_filepath'        => $_SERVER["REQUEST_URI"]
				, 'user_agent_string'     => $_SERVER["HTTP_USER_AGENT"]
				// , 'action_datetime'       => (new DateTime())->format('Y-m-d H:i:s')
			]);
		}

	}

	