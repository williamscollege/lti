<?php
	require_once(dirname(__FILE__) . '/mailer_base.class.php');

	class Mailer_Testing extends Mailer_Base {

		public $label = 'Mailer_Testing';

		public function send($queued_message_object) {
			$this->delivery_notes = "sending as
            " . $this->otherHeaders() . "
            To: " . $queued_message_object->target . "
            Subject: " . $queued_message_object->summary . "
            Body: " . $queued_message_object->body . "\n";
			return TRUE;
		}
	}
