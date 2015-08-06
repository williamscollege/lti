<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class SUS_EventLog extends Db_Linked {
		public static $fields = array('eventlog_id', 'user_id', 'flag_success', 'event_action', 'event_action_id', 'event_note', 'event_dataset', 'event_filepath', 'user_agent_string', 'event_datetime');
		public static $primaryKeyField = 'eventlog_id';
		public static $dbTable = 'sus_eventlogs';
		public static $entity_type_label = 'sus_eventlogs';


		public function clearCaches() {

		}


		// static factory function to populate new object with desired base values
		public static function createNewEventLog($user_id, $flag_success, $event_action, $event_action_id, $event_note, $event_dataset, $dbConnection) {
			return new SUS_EventLog(['DB' => $dbConnection
				, 'user_id'               => $user_id
				, 'flag_success'          => $flag_success
				, 'event_action'          => $event_action
				, 'event_action_id'       => $event_action_id
				, 'event_note'            => substr($event_note, 0, 1990)                	// truncate to avoid exceeding db field limit
				, 'event_dataset'         => substr($event_dataset, 0, 1990)                // truncate to avoid exceeding db field limit
				, 'event_filepath'        => substr($_SERVER["REQUEST_URI"], 0, 990)        // truncate to avoid exceeding db field limit
				, 'user_agent_string'     => substr($_SERVER["HTTP_USER_AGENT"], 0, 990)    // truncate to avoid exceeding db field limit
				// , 'action_datetime'       => (new DateTime())->format('Y-m-d H:i:s')
			]);
		}

	}

	