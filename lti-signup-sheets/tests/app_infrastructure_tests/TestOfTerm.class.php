<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

	Mock::generate('Auth_Base');

	class TestOfTerm extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testTermAtributesExist() {
			$this->assertEqual(count(Term::$fields), 6);

			$this->assertTrue(in_array('term_id', Term::$fields));
			$this->assertTrue(in_array('term_idstr', Term::$fields));
			$this->assertTrue(in_array('name', Term::$fields));
			$this->assertTrue(in_array('start_date', Term::$fields));
			$this->assertTrue(in_array('end_date', Term::$fields));
			$this->assertTrue(in_array('flag_delete', Term::$fields));
		}

		//// static methods

		function testCmp() {
			$t1 = new Term(['term_id' => 50, 'term_idstr' => '24F', 'name' => 'Fall 2023', 'start_date' => '2023-09-10T00:00:00-05:00', 'end_date' => '2023-12-21T00:00:00-05:00', 'DB' => $this->DB]);
			$t2 = new Term(['term_id' => 50, 'term_idstr' => '24W', 'name' => 'Winter Study 2024', 'start_date' => '2024-01-06T00:00:00-05:00', 'end_date' => '2024-01-30T00:00:00-05:00', 'DB' => $this->DB]);
			$t3 = new Term(['term_id' => 50, 'term_idstr' => '24S', 'name' => 'Spring 2024', 'start_date' => '2024-02-06T00:00:00-05:00', 'end_date' => '2023-05-26T00:00:00-05:00', 'DB' => $this->DB]);
			$t4 = new Term(['term_id' => 50, 'term_idstr' => '23F', 'name' => 'Fall 2022', 'start_date' => '2022-09-10T00:00:00-05:00', 'end_date' => '2022-12-21T00:00:00-05:00', 'DB' => $this->DB]);

			$this->assertEqual(Term::cmp($t1, $t2), -1);
			$this->assertEqual(Term::cmp($t1, $t1), 0);
			$this->assertEqual(Term::cmp($t2, $t1), 1);
			$this->assertEqual(Term::cmp($t3, $t4), 1);
		}

		//// DB interaction tests

		function testTermDBInsert() {
			$t = new Term(['term_id' => 50, 'term_idstr' => '23F', 'name' => 'Fall 2022', 'start_date' => '2022-09-10T00:00:00-05:00', 'end_date' => '2022-12-21T00:00:00-05:00', 'DB' => $this->DB]);

			$t->updateDb();

			$t2 = Term::getOneFromDb(['term_id' => 50], $this->DB);

			$this->assertTrue($t2->matchesDb);
			$this->assertEqual($t2->term_idstr, '23F');
		}

		function testTermRetrievedFromDb() {
			$t = new Term(['term_id' => 50, 'DB' => $this->DB]);
			$this->assertNull($t->term_idstr);

			$t->term_idstr = '23F';
			$t->updateDb();

			$t->refreshFromDb();
			$this->assertEqual($t->term_idstr, '23F');
		}

		//// instance methods - object itself

		//		function testTermRenderMinimal() {
		//			$t = Term::getOneFromDb(['term_id' => 5], $this->DB);
		//
		//			$canonical = '<div class="rendered-object term-render term-render-minimal term-render-5" data-for-term="5" data-term_full_name="' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '">' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '</div>';
		//			$rendered  = $t->renderMinimal();
		//			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
		//			$this->assertEqual($canonical, $rendered);
		//
		//			$canonical = '<div class="rendered-object term-render term-render-minimal term-render-5" data-for-term="5" data-term_full_name="' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '"><a href="' . APP_ROOT_PATH . '/app_code/term.php?term_id=101">' . Auth_Base::$TEST_LNAME . ', ' . Auth_Base::$TEST_FNAME . '</a></div>';
		//			$rendered  = $t->renderMinimal(TRUE);
		//			//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
		//			$this->assertEqual($canonical, $rendered);
		//		}

		//// instance methods - related data

		function testLoadCourses() {
			$t1 = Term::getOneFromDb(['term_id' => 4], $this->DB);
			$t2 = Term::getOneFromDb(['term_id' => 5], $this->DB);
			$t3 = new Term(['term_id' => 50, 'term_idstr' => '24S', 'name' => 'Spring 2024', 'start_date' => '2024-02-06T00:00:00-05:00', 'end_date' => '2023-05-26T00:00:00-05:00', 'DB' => $this->DB]);
			$t4 = Term::getOneFromDb(['term_id' => 6], $this->DB);

			$t1->loadCourses();
			$this->assertEqual(9, count($t1->courses));
			$this->assertEqual('15F-ARTH-101-01', $t1->courses[0]->course_idstr);
			$this->assertEqual('15F-ECON-301-02', $t1->courses[6]->course_idstr);

			$t2->loadCourses();
			$this->assertEqual(5, count($t2->courses));
			$this->assertEqual('15W-CHIN-101-01', $t2->courses[0]->course_idstr);
			$this->assertEqual('15W-RELI-101-01', $t2->courses[3]->course_idstr);

			$t3->loadCourses();
			$this->assertEqual(0, count($t3->courses));

			$t4->loadCourses();
			$this->assertEqual(9, count($t4->courses));
			$this->assertEqual('15S-ARTH-101-01', $t4->courses[0]->course_idstr);
			$this->assertEqual('15S-ECON-301-01', $t4->courses[5]->course_idstr);
		}


	}