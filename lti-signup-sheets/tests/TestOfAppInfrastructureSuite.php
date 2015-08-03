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
	require_once(dirname(__FILE__) . '/simpletest/WMS_unit_tester_DB.php');
	SimpleTest::prefer(new TextReporter());

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');

	class TestOfAppInfrastructureSuite extends TestSuite {
		function TestOfAppInfrastructureSuite() {
			$this->TestSuite('App Infrastructure tests');

			$this->addFile('app_infrastructure_tests/TestOfDB_Linked.class.php');
			$this->addFile('app_infrastructure_tests/TestOfAuth_Base.class.php');
			// $this->addFile('app_infrastructure_tests/TestOfAuth_LDAP.class.php'); // intentionally removed

			$this->addFile('app_infrastructure_tests/TestOfUser.class.php');
			$this->addFile('app_infrastructure_tests/TestOfTerm.class.php');
			$this->addFile('app_infrastructure_tests/TestOfCourse.class.php');
			$this->addFile('app_infrastructure_tests/TestOfEnrollment.class.php');

			$this->addFile('app_infrastructure_tests/TestOfSUS_Sheetgroup.class.php');
			$this->addFile('app_infrastructure_tests/TestOfSUS_Sheet.class.php');
			$this->addFile('app_infrastructure_tests/TestOfSUS_Opening.class.php');
			$this->addFile('app_infrastructure_tests/TestOfSUS_Signup.class.php');
			$this->addFile('app_infrastructure_tests/TestOfSUS_Access.class.php');

			$this->addFile('app_infrastructure_tests/TestOfUtil.php');

			$this->addFile('app_infrastructure_tests/TestOfQueued_message.class.php');

			$this->addFile('app_infrastructure_tests/TestOfMailer_base.class.php');
			$this->addFile('app_infrastructure_tests/TestOfMailer_php_standard.class.php');

			# Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}
