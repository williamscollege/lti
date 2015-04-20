<?php
	require_once(dirname(__FILE__) . '/mailer_base.class.php');

	class Mailer_Php_Standard extends Mailer_Base {

		public $label = 'Mailer_Php_Standard';

		public function send($queued_message_object) {
			return mail($queued_message_object->target, $queued_message_object->summary, $queued_message_object->body, $this->otherHeaders());
		}
	}

