<?php
	require_once(dirname(__FILE__) . '/simpletest/WMS_web_tester.php');

	class soundForTesting extends TestSuite {
		public function TestOfSoundCompleted() {
			$this->assertEqual(1, 1);
		}
	}

?>

<embed height="1" width="1" src="soundForTesting.mp3">