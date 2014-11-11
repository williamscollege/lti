<?php
	require_once dirname(__FILE__) . '/simpletest/WMS_unit_tester_DB.php';

	class TestOfDataSetup extends WMSUnitTestCaseDB {

		function TestSetUp() {
			createAllTestData($this->DB);
//			$this->assertTrue(true);
		}
	}