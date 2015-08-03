<?php
	// ***************************
	// Enable localhost testing only!
	// ***************************
	$strServerName = $_SERVER['SERVER_NAME'];
	if (!($strServerName == "localhost") OR ($strServerName == "127.0.0.1")) {
		echo 'ZERO ACCESS';
		exit;
	}

	require_once(dirname(__FILE__) . '/simpletest/WMS_unit_tester_DB.php');

	class TestOfDataSetup extends WMSUnitTestCaseDB {

		function TestSetUp() {
			createAllTestData($this->DB);
			//			$this->assertTrue(true);
		}
	}