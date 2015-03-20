<?php
	require_once(dirname(__FILE__) . '/simpletest/autorun.php');
	SimpleTest::prefer(new TextReporter());

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');

	class TestOfAllSuite extends TestSuite {
		function TestOfAllSuite() {
			$this->TestSuite('Full application test');
			$this->addFile('TestOfAppInfrastructureSuite.php');
			$this->addFile('TestOfWebPageSuite.php');


			# Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

?>