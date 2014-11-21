<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

	Mock::generate('Auth_Base');

	class TestOfEnrollment extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testEnrollmentAtributesExist() {
			$this->assertEqual(count(Enrollment::$fields), 6);

			$this->assertTrue(in_array('enrollment_id', Enrollment::$fields));
			$this->assertTrue(in_array('course_idstr', Enrollment::$fields));
			$this->assertTrue(in_array('user_id', Enrollment::$fields));
			$this->assertTrue(in_array('course_role_name', Enrollment::$fields));
			$this->assertTrue(in_array('section_id', Enrollment::$fields));
			$this->assertTrue(in_array('flag_delete', Enrollment::$fields));
		}

		//// static methods

		function testCmp() {
			$e1 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-ROCK-101-01', 'user_id' => 200, 'course_role_name' => 'teacher', 'section_id' => '25F-ROCK-101-01', 'DB' => $this->DB]);
			$e2 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-ROCK-101-01', 'user_id' => 200, 'course_role_name' => 'student', 'section_id' => '25F-ROCK-101-01', 'DB' => $this->DB]);
			$e3 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-SCISSORS-101-01', 'user_id' => 200, 'course_role_name' => 'student', 'section_id' => '25F-SCISSORS-101-01', 'DB' => $this->DB]);
			$e4 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-PAPER-101-01', 'user_id' => 200, 'course_role_name' => 'student', 'section_id' => '25F-PAPER-101-01', 'DB' => $this->DB]);

			$this->assertEqual(Enrollment::cmp($e1, $e2), 1);
			$this->assertEqual(Enrollment::cmp($e1, $e1), 0);
			$this->assertEqual(Enrollment::cmp($e2, $e1), -1);
			$this->assertEqual(Enrollment::cmp($e3, $e4), -1);
		}

		//// DB interaction tests

		function testEnrollmentDBInsert() {
			$e = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-DIAMOND-101-01', 'short_name' => '25F-DIAMOND-101-01 - Best Friend', 'long_name' => '25F-DIAMOND-101-01 - Best Friend', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);

			$e->updateDb();

			$e2 = Enrollment::getOneFromDb(['enrollment_id' => 50], $this->DB);

			$this->assertTrue($e2->matchesDb);
			$this->assertEqual($e2->short_name, '25F-DIAMOND-101-01 - Best Friend');
		}

		function testEnrollmentRetrievedFromDb() {
			$e = new Enrollment(['enrollment_id' => 5, 'DB' => $this->DB]);
			$this->assertNull($e->course_idstr);

			$e->refreshFromDb();
			$this->assertEqual($e->course_idstr, '15F-ECON-201-01');
		}

		//// instance methods - object itself

		function testEnrollmentRenderMinimal() {
			$e = Enrollment::getOneFromDb(['enrollment_id' => 5], $this->DB);

			$canonical = '<div class="rendered-object course-render course-render-minimal course-render-5" data-for-course="5" data-course_idstr="15F-ECON-201-01">15F-ECON-201-01 - Economy: Depression Era to WW II</div>';
			$rendered  = $e->renderMinimal();
			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
			$this->assertEqual($canonical, $rendered);

			$canonical = '<div class="rendered-object course-render course-render-minimal course-render-5" data-for-course="5" data-course_idstr="15F-ECON-201-01"><a href="' . APP_ROOT_PATH . '/app_code/course.php?enrollment_id=5">15F-ECON-201-01 - Economy: Depression Era to WW II</a></div>';
			$rendered  = $e->renderMinimal(TRUE);
			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
			$this->assertEqual($canonical, $rendered);
		}

		//// instance methods - related data

		function testLoadXXXXX() {
			$e1 = Enrollment::getOneFromDb(['course_idstr' => '15F-ARTH-101-01'], $this->DB);
			$e2 = Enrollment::getOneFromDb(['course_idstr' => '15F-BIOL-101-01'], $this->DB);
			$e3 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-BEEB-101-01', 'short_name' => '25F-BEEB-101-01 - Beeblebrox', 'long_name' => '25F-BEEB-101-01 - Beeblebrox', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);
			$e4 = Enrollment::getOneFromDb(['course_idstr' => '15F-CHEM-101-01'], $this->DB);

			$e1->loadXXXXX();
			$this->assertEqual(8, count($e1->enrollments));
			$this->assertEqual('15F-ARTH-101-01', $e1->enrollments[0]->course_idstr);
			$this->assertEqual('teacher', $e1->enrollments[0]->course_role_name);

			$e2->loadXXXXX();
			$this->assertEqual(4, count($e2->enrollments));
			$this->assertEqual('15F-BIOL-101-01', $e2->enrollments[0]->course_idstr);
			$this->assertEqual(104, $e2->enrollments[3]->user_id);

			$e3->loadXXXXX();
			$this->assertEqual(0, count($e3->enrollments));

			$e4->loadXXXXX();
			$this->assertEqual(2, count($e4->enrollments));
			$this->assertEqual('15F-CHEM-101-01', $e4->enrollments[0]->course_idstr);
			$this->assertEqual('student', $e4->enrollments[1]->course_role_name);
		}


	}