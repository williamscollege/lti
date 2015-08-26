<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php');
	require_once(dirname(__FILE__) . '/../../classes/auth_base.class.php');

	Mock::generate('Auth_Base');

	class TestOfCourse extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testCourseAtributesExist() {
			$this->assertEqual(count(Course::$fields), 10);

			$this->assertTrue(in_array('course_id', Course::$fields));
			$this->assertTrue(in_array('course_idstr', Course::$fields));
			$this->assertTrue(in_array('short_name', Course::$fields));
			$this->assertTrue(in_array('long_name', Course::$fields));
			$this->assertTrue(in_array('account_idstr', Course::$fields));
			$this->assertTrue(in_array('term_idstr', Course::$fields));
			$this->assertTrue(in_array('canvas_course_id', Course::$fields));
			$this->assertTrue(in_array('begins_at_str', Course::$fields));
			$this->assertTrue(in_array('ends_at_str', Course::$fields));
			$this->assertTrue(in_array('flag_delete', Course::$fields));
		}

		//// static methods

		function testCmp() {
			$c1 = new Course(['course_id' => 50, 'course_idstr' => '25F-ROCK-101-01', 'short_name' => '25F-ROCK-101-01 - Rock and Roll', 'long_name' => '25F-ROCK-101-01 - Rock and Roll', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);
			$c2 = new Course(['course_id' => 50, 'course_idstr' => '25F-PAPER-101-01', 'short_name' => '25F-PAPER-101-01 - Rock and Roll', 'long_name' => '25F-PAPER-101-01 - Rock and Roll', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);
			$c3 = new Course(['course_id' => 50, 'course_idstr' => '25F-SCISSORS-101-01', 'short_name' => '25F-SCISSORS-101-01 - Rock and Roll', 'long_name' => '25F-SCISSORS-101-01 - Rock and Roll', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);
			$c4 = new Course(['course_id' => 50, 'course_idstr' => '25F-TRICK-101-01', 'short_name' => '25F-TRICK-101-01 - Rock and Roll', 'long_name' => '25F-TRICK-101-01 - Rock and Roll', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);

			$this->assertEqual(Course::cmp($c1, $c2), 1);
			$this->assertEqual(Course::cmp($c1, $c1), 0);
			$this->assertEqual(Course::cmp($c2, $c1), -1);
			$this->assertEqual(Course::cmp($c3, $c4), -1);
		}

		//// DB interaction tests

		function testCourseDBInsert() {
			$c = new Course(['course_id' => 50, 'course_idstr' => '25F-DIAMOND-101-01', 'short_name' => '25F-DIAMOND-101-01 - Best Friend', 'long_name' => '25F-DIAMOND-101-01 - Best Friend', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'canvas_course_id' => '0', 'DB' => $this->DB]);

			$c->updateDb();

			$c2 = Course::getOneFromDb(['course_id' => 50], $this->DB);

			$this->assertTrue($c2->matchesDb);
			$this->assertEqual($c2->short_name, '25F-DIAMOND-101-01 - Best Friend');
		}

		function testCourseRetrievedFromDb() {
			$c = new Course(['course_id' => 205, 'DB' => $this->DB]);
			$this->assertNull($c->course_idstr);

			$c->refreshFromDb();
			$this->assertEqual($c->course_idstr, '15F-ECON-201-01');
		}

		//// instance methods - object itself

		function testCourseRenderMinimal() {
			$c = Course::getOneFromDb(['course_id' => 205], $this->DB);

			$canonical = '<div class="rendered-object" data-for-course_id="205" data-course_idstr="15F-ECON-201-01">15F-ECON-201-01 - Economy: Depression Era to WW II</div>';
			$rendered  = $c->renderMinimal();
			$this->assertEqual($canonical, $rendered);

			$canonical = '<div class="rendered-object" data-for-course_id="205" data-course_idstr="15F-ECON-201-01"><a href="' . APP_ROOT_PATH . '/app_code/course.php?course_id=205">15F-ECON-201-01 - Economy: Depression Era to WW II</a></div>';
			$rendered  = $c->renderMinimal(TRUE);
			$this->assertEqual($canonical, $rendered);
		}

		//// instance methods - related data

		function testCacheEnrollments() {
			$c1 = Course::getOneFromDb(['course_idstr' => '15F-ARTH-101-01'], $this->DB);
			$c2 = Course::getOneFromDb(['course_idstr' => '15F-BIOL-101-01'], $this->DB);
			$c3 = new Course(['course_id' => 50, 'course_idstr' => '25F-BEEB-101-01', 'short_name' => '25F-BEEB-101-01 - Beeblebrox', 'long_name' => '25F-BEEB-101-01 - Beeblebrox', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);
			$c4 = Course::getOneFromDb(['course_idstr' => '15F-CHEM-101-01'], $this->DB);

			$c1->cacheEnrollments();
			$this->assertEqual(8, count($c1->enrollments));
			$this->assertEqual('15F-ARTH-101-01', $c1->enrollments[0]->course_idstr);
			$this->assertEqual('student', $c1->enrollments[0]->course_role_name);

			$c2->cacheEnrollments();
			$this->assertEqual(4, count($c2->enrollments));
			$this->assertEqual('15F-BIOL-101-01', $c2->enrollments[0]->course_idstr);
			$this->assertEqual(101, $c2->enrollments[3]->canvas_user_id);

			$c3->cacheEnrollments();
			$this->assertEqual(0, count($c3->enrollments));

			$c4->cacheEnrollments();
			$this->assertEqual(1, count($c4->enrollments));
			$this->assertEqual('15F-CHEM-101-01', $c4->enrollments[0]->course_idstr);
			$this->assertEqual('student', $c4->enrollments[0]->course_role_name);
		}


		function testLoadEnrollments() {
			$c1 = Course::getOneFromDb(['course_idstr' => '15F-ARTH-101-01'], $this->DB);
			$c2 = Course::getOneFromDb(['course_idstr' => '15F-BIOL-101-01'], $this->DB);
			$c3 = new Course(['course_id' => 50, 'course_idstr' => '25F-BEEB-101-01', 'short_name' => '25F-BEEB-101-01 - Beeblebrox', 'long_name' => '25F-BEEB-101-01 - Beeblebrox', 'account_idstr' => 'courses', 'term_idstr' => '25F', 'DB' => $this->DB]);
			$c4 = Course::getOneFromDb(['course_idstr' => '15F-CHEM-101-01'], $this->DB);

			$c1->loadEnrollments();
			$this->assertEqual(8, count($c1->enrollments));
			$this->assertEqual('15F-ARTH-101-01', $c1->enrollments[0]->course_idstr);
			$this->assertEqual('student', $c1->enrollments[0]->course_role_name);

			$c2->loadEnrollments();
			$this->assertEqual(4, count($c2->enrollments));
			$this->assertEqual('15F-BIOL-101-01', $c2->enrollments[0]->course_idstr);
			$this->assertEqual(101, $c2->enrollments[3]->canvas_user_id);

			$c3->loadEnrollments();
			$this->assertEqual(0, count($c3->enrollments));

			$c4->loadEnrollments();
			$this->assertEqual(1, count($c4->enrollments));
			$this->assertEqual('15F-CHEM-101-01', $c4->enrollments[0]->course_idstr);
			$this->assertEqual('student', $c4->enrollments[0]->course_role_name);
		}


	}