<?php
	require_once(dirname(__FILE__) . '/simpletest/autorun.php');
	require_once(dirname(__FILE__) . '/simpletest/WMS_unit_tester_DB.php');
	SimpleTest::prefer(new TextReporter());

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../lang.cfg.php');

	class TestOfAppInfrastructureSuite extends TestSuite {
		function TestOfAppInfrastructureSuite() {
			$this->TestSuite('App Infrastructure tests');

			$this->addFile('app_infrastructure_tests/TestOfUtil.php');

			$this->addFile('app_infrastructure_tests/TestOfDB_Linked.class.php');

			$this->addFile('app_infrastructure_tests/TestOfAuth_Base.class.php');
			$this->addFile('app_infrastructure_tests/TestOfAuth_LDAP.class.php');

			$this->addFile('app_infrastructure_tests/TestOfUser.class.php');
			$this->addFile('app_infrastructure_tests/TestOfCourseRole.class.php');

			$this->addFile('app_infrastructure_tests/TestOfAction.class.php');

			# Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

?>