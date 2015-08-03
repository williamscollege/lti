<?php
	// ***************************
	// Enable localhost testing only!
	// ***************************
	$strServerName = $_SERVER['SERVER_NAME'];
	if (!($strServerName == "localhost") OR ($strServerName == "127.0.0.1")) {
		echo 'ZERO ACCESS';
		exit;
	}

	require_once(dirname(__FILE__) . '/simpletest/autorun.php');
	require_once(dirname(__FILE__) . '/simpletest/WMS_web_tester.php');
	SimpleTest::prefer(new TextReporter());

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');

	class TestOfWebSuite extends TestSuite {
		function TestOfWebSuite() {
			$this->TestSuite('Web page tests');

			# Tests: Index page
			$this->addFile('web_page_tests/IndexPagePublicTest.php');
			$this->addFile('web_page_tests/IndexPageAuthTest.php');
			$this->addFile('web_page_tests/IndexPageLoggedInTest.php');

			# Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

