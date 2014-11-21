<?php
	// $Id: adapter_test.php 1748 2008-04-14 01:50:41Z lastcraft $
	require_once(dirname(__FILE__) . '/../autorun.php');
	require_once(dirname(__FILE__) . '/../extensions/pear_test_case.php');

	class SameTestClass {
	}

	class TestOfPearAdapter extends PHPUnit_TestCase {

		function testBoolean() {
			$this->assertTrue(TRUE, "PEAR true");
			$this->assertFalse(FALSE, "PEAR false");
		}

		function testName() {
			$this->assertTrue($this->getName() == get_class($this));
		}

		function testPass() {
			$this->pass("PEAR pass");
		}

		function testNulls() {
			$value = NULL;
			$this->assertNull($value, "PEAR null");
			$value = 0;
			$this->assertNotNull($value, "PEAR not null");
		}

		function testType() {
			$this->assertType("Hello", "string", "PEAR type");
		}

		function testEquals() {
			$this->assertEquals(12, 12, "PEAR identity");
			$this->setLooselyTyped(TRUE);
			$this->assertEquals("12", 12, "PEAR equality");
		}

		function testSame() {
			$same = new SameTestClass();
			$this->assertSame($same, $same, "PEAR same");
		}

		function testRegExp() {
			$this->assertRegExp('/hello/', "A big hello from me", "PEAR regex");
		}
	}

?>