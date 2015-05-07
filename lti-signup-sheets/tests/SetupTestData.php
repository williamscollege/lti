<?php
	require_once(dirname(__FILE__) . '/simpletest/autorun.php');
	require_once(dirname(__FILE__) . '/simpletest/WMS_web_tester.php');
	SimpleTest::prefer(new TextReporter());

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');

	class SetupTestData extends TestSuite {
		function SetupTestData() {
			$this->TestSuite('Signup Sheets test data creation');

			# Setup test data
			$this->addFile('TestOfSetupTestData.php');

		}
	}
