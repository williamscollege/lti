<?php
	// $Id: web_tester_test.php 1748 2008-04-14 01:50:41Z lastcraft $
	require_once(dirname(__FILE__) . '/../autorun.php');
	require_once(dirname(__FILE__) . '/../web_tester.php');

	class TestOfFieldExpectation extends UnitTestCase {

		function testStringMatchingIsCaseSensitive() {
			$expectation = new FieldExpectation('a');
			$this->assertTrue($expectation->test('a'));
			$this->assertTrue($expectation->test(array('a')));
			$this->assertFalse($expectation->test('A'));
		}

		function testMatchesInteger() {
			$expectation = new FieldExpectation('1');
			$this->assertTrue($expectation->test('1'));
			$this->assertTrue($expectation->test(1));
			$this->assertTrue($expectation->test(array('1')));
			$this->assertTrue($expectation->test(array(1)));
		}

		function testNonStringFailsExpectation() {
			$expectation = new FieldExpectation('a');
			$this->assertFalse($expectation->test(NULL));
		}

		function testUnsetFieldCanBeTestedFor() {
			$expectation = new FieldExpectation(FALSE);
			$this->assertTrue($expectation->test(FALSE));
		}

		function testMultipleValuesCanBeInAnyOrder() {
			$expectation = new FieldExpectation(array('a', 'b'));
			$this->assertTrue($expectation->test(array('a', 'b')));
			$this->assertTrue($expectation->test(array('b', 'a')));
			$this->assertFalse($expectation->test(array('a', 'a')));
			$this->assertFalse($expectation->test('a'));
		}

		function testSingleItemCanBeArrayOrString() {
			$expectation = new FieldExpectation(array('a'));
			$this->assertTrue($expectation->test(array('a')));
			$this->assertTrue($expectation->test('a'));
		}
	}

	class TestOfHeaderExpectations extends UnitTestCase {

		function testExpectingOnlyTheHeaderName() {
			$expectation = new HttpHeaderExpectation('a');
			$this->assertIdentical($expectation->test(FALSE), FALSE);
			$this->assertIdentical($expectation->test('a: A'), TRUE);
			$this->assertIdentical($expectation->test('A: A'), TRUE);
			$this->assertIdentical($expectation->test('a: B'), TRUE);
			$this->assertIdentical($expectation->test(' a : A '), TRUE);
		}

		function testHeaderValueAsWell() {
			$expectation = new HttpHeaderExpectation('a', 'A');
			$this->assertIdentical($expectation->test(FALSE), FALSE);
			$this->assertIdentical($expectation->test('a: A'), TRUE);
			$this->assertIdentical($expectation->test('A: A'), TRUE);
			$this->assertIdentical($expectation->test('A: a'), FALSE);
			$this->assertIdentical($expectation->test('a: B'), FALSE);
			$this->assertIdentical($expectation->test(' a : A '), TRUE);
			$this->assertIdentical($expectation->test(' a : AB '), FALSE);
		}

		function testHeaderValueWithColons() {
			$expectation = new HttpHeaderExpectation('a', 'A:B:C');
			$this->assertIdentical($expectation->test('a: A'), FALSE);
			$this->assertIdentical($expectation->test('a: A:B'), FALSE);
			$this->assertIdentical($expectation->test('a: A:B:C'), TRUE);
			$this->assertIdentical($expectation->test('a: A:B:C:D'), FALSE);
		}

		function testMultilineSearch() {
			$expectation = new HttpHeaderExpectation('a', 'A');
			$this->assertIdentical($expectation->test("aa: A\r\nb: B\r\nc: C"), FALSE);
			$this->assertIdentical($expectation->test("aa: A\r\na: A\r\nb: B"), TRUE);
		}

		function testMultilineSearchWithPadding() {
			$expectation = new HttpHeaderExpectation('a', ' A ');
			$this->assertIdentical($expectation->test("aa:A\r\nb:B\r\nc:C"), FALSE);
			$this->assertIdentical($expectation->test("aa:A\r\na:A\r\nb:B"), TRUE);
		}

		function testPatternMatching() {
			$expectation = new HttpHeaderExpectation('a', new PatternExpectation('/A/'));
			$this->assertIdentical($expectation->test('a: A'), TRUE);
			$this->assertIdentical($expectation->test('A: A'), TRUE);
			$this->assertIdentical($expectation->test('A: a'), FALSE);
			$this->assertIdentical($expectation->test('a: B'), FALSE);
			$this->assertIdentical($expectation->test(' a : A '), TRUE);
			$this->assertIdentical($expectation->test(' a : AB '), TRUE);
		}

		function testCaseInsensitivePatternMatching() {
			$expectation = new HttpHeaderExpectation('a', new PatternExpectation('/A/i'));
			$this->assertIdentical($expectation->test('a: a'), TRUE);
			$this->assertIdentical($expectation->test('a: B'), FALSE);
			$this->assertIdentical($expectation->test(' a : A '), TRUE);
			$this->assertIdentical($expectation->test(' a : BAB '), TRUE);
			$this->assertIdentical($expectation->test(' a : bab '), TRUE);
		}

		function testUnwantedHeader() {
			$expectation = new NoHttpHeaderExpectation('a');
			$this->assertIdentical($expectation->test(''), TRUE);
			$this->assertIdentical($expectation->test('stuff'), TRUE);
			$this->assertIdentical($expectation->test('b: B'), TRUE);
			$this->assertIdentical($expectation->test('a: A'), FALSE);
			$this->assertIdentical($expectation->test('A: A'), FALSE);
		}

		function testMultilineUnwantedSearch() {
			$expectation = new NoHttpHeaderExpectation('a');
			$this->assertIdentical($expectation->test("aa:A\r\nb:B\r\nc:C"), TRUE);
			$this->assertIdentical($expectation->test("aa:A\r\na:A\r\nb:B"), FALSE);
		}

		function testLocationHeaderSplitsCorrectly() {
			$expectation = new HttpHeaderExpectation('Location', 'http://here/');
			$this->assertIdentical($expectation->test('Location: http://here/'), TRUE);
		}
	}

	class TestOfTextExpectations extends UnitTestCase {

		function testMatchingSubString() {
			$expectation = new TextExpectation('wanted');
			$this->assertIdentical($expectation->test(''), FALSE);
			$this->assertIdentical($expectation->test('Wanted'), FALSE);
			$this->assertIdentical($expectation->test('wanted'), TRUE);
			$this->assertIdentical($expectation->test('the wanted text is here'), TRUE);
		}

		function testNotMatchingSubString() {
			$expectation = new NoTextExpectation('wanted');
			$this->assertIdentical($expectation->test(''), TRUE);
			$this->assertIdentical($expectation->test('Wanted'), TRUE);
			$this->assertIdentical($expectation->test('wanted'), FALSE);
			$this->assertIdentical($expectation->test('the wanted text is here'), FALSE);
		}
	}

	class TestOfGenericAssertionsInWebTester extends WebTestCase {
		function testEquality() {
			$this->assertEqual('a', 'a');
			$this->assertNotEqual('a', 'A');
		}
	}

?>