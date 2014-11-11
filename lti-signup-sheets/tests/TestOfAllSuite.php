<?php
	require_once('simpletest/autorun.php');
	SimpleTest::prefer(new TextReporter());

    require_once('../institution.cfg.php');
    require_once('../lang.cfg.php');

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