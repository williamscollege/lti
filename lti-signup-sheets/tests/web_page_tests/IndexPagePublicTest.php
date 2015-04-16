<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_web_tester.php');

	class IndexPagePublicTest extends WMSWebTestCase {

		function setUp() {
			createAllTestData($this->DB);
			global $CUR_LANG_SET;
			$CUR_LANG_SET = 'en';
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testIndexPageLoad() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/');
			$this->assertResponse(200);
		}

		function testIndexPageLoadsErrorAndWarningFree() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/');
			$this->assertNoPattern('/error/i');
			$this->assertNoPattern('/warning/i');
		}

		function testIndexPageLoadsCorrectText() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/');

			$this->assertTitle(new PatternExpectation('/' . LANG_APP_NAME . '/'));

			$this->assertNoPattern('/' . util_lang('app_signed_in_status') . ': \<a[^\>]*\>' . TESTINGUSER . '\<\/a\>/');
			$this->assertPattern('/' . util_lang('app_sign_in_action') . '/');

			$this->assertPattern('/' . util_lang('app_short_description') . '/');
			$this->assertPattern('/' . util_lang('app_sign_in_msg') . '/');

			// check for published, verfied notebooks that are publically viewable
			// $this->assertText(ucfirst(util_lang('public')).' '.ucfirst(util_lang('notebooks')));
			// $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','data-notebook-count','1');
			// $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1004');
			// $this->assertLink('testnotebook4');
		}

		function testIndexPageHasNoNavMenusDisplayed() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/');

			$this->assertNoFieldById('nav-link-my-signups', 'there should be no nav buttons on public page');
			$this->assertNoFieldById('nav-link-available-openings', 'there should be no nav buttons on public page');
			$this->assertNoFieldById('nav-link-my-sheets', 'there should be no nav buttons on public page');
			$this->assertNoFieldById('nav-link-help', 'there should be no nav buttons on public page');
		}

		function testIndexPageHasSplashLinks() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/');

			$this->assertEltByIdHasAttrOfValue('footer-link-help', 'id', 'footer-link-help');
		}

		//		function testHelp() {
		//			$this->todo();
		//		}

	}