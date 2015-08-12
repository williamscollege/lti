<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	function __callbackRedirectValidateForDelivery($m) {
		return $m->validateForDelivery();
	}

	class QueuedMessage extends Db_Linked {
		public static $fields = array('queued_message_id', 'user_id', 'sheet_id', 'opening_id', 'delivery_type', 'flag_is_delivered',
			'target', 'summary', 'body',
			'action_datetime', 'action_status', 'action_notes',
			'flag_delete');
		// 'hold_until_datetime' /* not in use for this application */
		public static $primaryKeyField = 'queued_message_id';
		public static $dbTable = 'queued_messages';

		public static $ALLOWED_DELIVERY_TYPES = ['email'];
		public static $DEFAULT_DELIVERY_TYPE = 'email';

		public static $LOG_MSG_BAD_USER_ID = 'user_id is invalid';
		public static $LOG_MSG_BAD_DELIVERY_TYPE = 'delivery type is invalid';
		public static $LOG_MSG_BAD_TARGET_EMAIL = 'the email address is not valid';
		public static $LOG_MSG_BAD_SUMMARY_EMPTY = 'message summary may not be empty/false/blank';
		public static $LOG_MSG_BAD_SUMMARY_MULTILINE = 'message summary must be only a single line';
		public static $LOG_MSG_BAD_BODY_EMPTY = 'message body may not be empty/false/blank';
		public static $LOG_MSG_ALREADY_SENT = 'message has already been sent';
		public static $LOG_MSG_NO_ACTION_ON_DELETED_MESSAGES = 'no actions allowed on deleted messages';
		public static $LOG_MSG_HELD = 'the message is being held';

		//-------------------------------------------------------------------------------------

		public $validate_status = '';
		public $validate_message = '';

		//-------------------------------------------------------------------------------------

		public static function factory($db, $user_id, $target, $summary, $body, $opening_id = 0, $sheet_id = 0, $type = 'email') {
			$qm = new QueuedMessage(['DB' => $db
				, 'user_id'               => $user_id
				, 'sheet_id'              => $sheet_id
				, 'opening_id'            => $opening_id
				, 'delivery_type'         => $type
				, 'flag_is_delivered'     => FALSE
				//,'hold_until_datetime' => ''
				, 'target'                => $target
				, 'summary'               => $summary
				, 'body'                  => $body
				, 'action_datetime'       => (new DateTime())->format('Y-m-d H:i:s')
				, 'action_status'         => 'CREATED'
				, 'action_notes'          => 'CREATED: at ' . (new DateTime())->format('Y-m-d H:i:s')
				, 'flag_delete'           => FALSE
			]);
			return $qm;
		}


		public static function fetchMessagesReadyForDelivery($db, $asOfDateTime = '') {
			if (!$asOfDateTime) {
				$asOfDateTime = (new DateTime())->format('Y-m-d H:i:s');
			} // default to current datetime

			$qms = QueuedMessage::getAllFromDb(['flag_delete' => FALSE
				//                                               ,'hold_until_datetime <='=>$asOfDateTime
				, 'delivery_type'                             => QueuedMessage::$ALLOWED_DELIVERY_TYPES
				, 'flag_is_delivered'                         => FALSE
				, 'target !='                                 => ''
				, 'summary !='                                => ''
				, 'body !='                                   => ''
			], $db);

			$filtered_qms = array_filter($qms, '__callbackRedirectValidateForDelivery');

			return $filtered_qms;
		}

		//-------------------------------------------------------------------------------------

		public function trackAction($status, $note) {
			$this->action_datetime = (new DateTime())->format('Y-m-d H:i:s');
			$this->action_status   = $status;
			$this->action_notes .= "\n$status: at " . $this->action_datetime . " - $note";
		}

		public function validateForDelivery() {
			if (!in_array($this->delivery_type, QueuedMessage::$ALLOWED_DELIVERY_TYPES)) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_BAD_DELIVERY_TYPE;
				return FALSE;
			}

			if (!trim($this->user_id)) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_BAD_USER_ID;
				return FALSE;
			}

			if ($this->delivery_type == 'email') {
				if (!filter_var($this->target, FILTER_VALIDATE_EMAIL)) {
					$this->validate_status  = 'FAILURE';
					$this->validate_message = QueuedMessage::$LOG_MSG_BAD_TARGET_EMAIL;
					return FALSE;
				}
			}

			if (!trim($this->summary)) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_BAD_SUMMARY_EMPTY;
				return FALSE;
			}

			if ((strpos($this->summary, "\n") !== FALSE) || (strpos($this->summary, "\r") !== FALSE)) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_BAD_SUMMARY_MULTILINE;
				return FALSE;
			}

			if (!trim($this->body)) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_BAD_BODY_EMPTY;
				return FALSE;
			}

			if ($this->flag_is_delivered) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_ALREADY_SENT;
				return FALSE;
			}

			if ($this->flag_delete) {
				$this->validate_status  = 'FAILURE';
				$this->validate_message = QueuedMessage::$LOG_MSG_NO_ACTION_ON_DELETED_MESSAGES;
				return FALSE;
			}

			//	if (($this->hold_until_datetime) && ($this->hold_until_datetime > (new DateTime())->format('Y-m-d H:i:s'))) {
			//		$this->validate_status = 'FAILURE';
			//		$this->validate_message = QueuedMessage::$LOG_MSG_HELD;
			//		return false;
			//	}

			$this->validate_status  = 'SUCCESS';
			$this->validate_message = '';
			return TRUE;
		}

		function attemptDelivery() {
			$this->action_datetime = (new DateTime())->format('Y-m-d H:i:s');
			if (!$this->validateForDelivery()) {
				$this->trackAction($this->validate_status, $this->validate_message);
				$this->updateDb();
				return FALSE;
			}
			global $MAILER;
			if ($MAILER->send($this)) {
				$this->trackAction('SUCCESS', 'message sent via ' . $MAILER->label);
				$this->flag_is_delivered = TRUE;
				$this->updateDb();
				return TRUE;
			}
			else {
				$this->trackAction('FAILURE', 'message could not be sent via ' . $MAILER->label);
				$this->updateDb();
				return FALSE;
			}
		}
	}

?>