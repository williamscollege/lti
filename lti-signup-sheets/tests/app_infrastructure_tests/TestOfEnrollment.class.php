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
			$this->assertTrue(in_array('section_idstr', Enrollment::$fields));
			$this->assertTrue(in_array('flag_delete', Enrollment::$fields));
		}

		//// static methods

		function testCmp() {
			$e1 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-ROCK-101-01', 'user_id' => 200, 'course_role_name' => 'teacher', 'section_idstr' => '25F-ROCK-101-01', 'DB' => $this->DB]);
			$e2 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-SCISSORS-101-01', 'user_id' => 200, 'course_role_name' => 'student', 'section_idstr' => '25F-SCISSORS-101-01', 'DB' => $this->DB]);
			$e3 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-ROCK-101-01', 'user_id' => 200, 'course_role_name' => 'student', 'section_idstr' => '25F-ROCK-101-01', 'DB' => $this->DB]);
			$e4 = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-PAPER-101-01', 'user_id' => 200, 'course_role_name' => 'student', 'section_idstr' => '25F-PAPER-101-01', 'DB' => $this->DB]);

			$this->assertEqual(Enrollment::cmp($e1, $e2), -1);
			$this->assertEqual(Enrollment::cmp($e1, $e1), 0);
			$this->assertEqual(Enrollment::cmp($e2, $e1), 1);
			$this->assertEqual(Enrollment::cmp($e3, $e4), 1);
		}

		//// DB interaction tests

		function testEnrollmentDBInsert() {
			$e = new Enrollment(['enrollment_id' => 50, 'course_idstr' => '25F-ROCK-101-01', 'user_id' => 200, 'course_role_name' => 'teacher', 'section_idstr' => '25F-ROCK-101-01', 'DB' => $this->DB]);

			$e->updateDb();

			$e2 = Enrollment::getOneFromDb(['enrollment_id' => 50], $this->DB);

			$this->assertTrue($e2->matchesDb);
			$this->assertEqual($e2->course_role_name, 'teacher');
		}

		function testEnrollmentRetrievedFromDb() {
			$e = new Enrollment(['enrollment_id' => 405, 'DB' => $this->DB]);
			$this->assertNull($e->course_idstr);

			$e->refreshFromDb();
			$this->assertEqual($e->course_role_name, 'student');
			$this->assertEqual($e->course_idstr, '15F-ARTH-101-01');
		}

		//// instance methods - object itself

		function testEnrollmentRenderMinimal() {
			$e = Enrollment::getOneFromDb(['enrollment_id' => 405], $this->DB);

			$canonical = '<div class="rendered-object enrollment-render enrollment-render-minimal enrollment-render-405" data-for-enrollment="405" data-course_idstr="15F-ARTH-101-01">15F-ARTH-101-01</div>';
			$rendered  = $e->renderMinimal();
			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
			$this->assertEqual($canonical, $rendered);

			$canonical = '<div class="rendered-object enrollment-render enrollment-render-minimal enrollment-render-405" data-for-enrollment="405" data-course_idstr="15F-ARTH-101-01"><a href="' . APP_ROOT_PATH . '/app_code/enrollment.php?enrollment_id=405">15F-ARTH-101-01</a></div>';
			$rendered  = $e->renderMinimal(TRUE);
			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
			$this->assertEqual($canonical, $rendered);
		}

	}