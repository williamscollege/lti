<?php
require_once('simpletest/autorun.php');
require_once('simpletest/WMS_web_tester.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');
require_once('../lang.cfg.php');

class SetupTestData extends TestSuite {
	function SetupTestData() {
		$this->TestSuite('Digital Field Notebooks test data creation');

		# Setup test data
		$this->addFile('TestOfSetupTestData.php');

	}
}

?>