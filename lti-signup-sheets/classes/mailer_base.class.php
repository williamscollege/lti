<?php

	class Mailer_Base {

		public $label = 'Mailer_Base';

		public $from_address_name = 'signup_sheets-no-reply';

		public $delivery_notes = '';

		public function otherHeaders() {
			return 'From: ' . $this->from_address_name . '@' . INSTITUTION_DOMAIN;
		}

		public function send($queued_message_object) {
			echo "You must override send in your local Mailer implementation class<br/>\n";
			return FALSE;
		}

	}
