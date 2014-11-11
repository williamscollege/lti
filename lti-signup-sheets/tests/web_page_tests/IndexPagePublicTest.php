<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

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
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertResponse(200);
    }

    function testIndexPageLoadsErrorAndWarningFree() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertNoPattern('/error/i');
        $this->assertNoPattern('/warning/i');
    }

    function testIndexPageLoadsCorrectText() {
        $this->get('http://localhost/digitalfieldnotebooks/');

        $this->assertTitle(new PatternExpectation('/'.LANG_APP_NAME.': /'));

        $this->assertNoPattern('/'.util_lang('app_signed_in_status').': \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
        $this->assertPattern('/'.util_lang('app_sign_in_action').'/');

        $this->assertPattern('/'.util_lang('app_short_description').'/');
        $this->assertPattern('/'.util_lang('app_sign_in_msg').'/');

        // check for published, verfied notebooks that are publically viewable
//        $this->assertText(ucfirst(util_lang('public')).' '.ucfirst(util_lang('notebooks')));
//        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','data-notebook-count','1');
//        $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1004');
//        $this->assertLink('testnotebook4');
    }

    function testIndexPageHasCorrectMenus() {
        $this->get('http://localhost/digitalfieldnotebooks/');

        $this->assertEltByIdHasAttrOfValue('nav-notebooks','id','nav-notebooks');
        $this->assertEltByIdHasAttrOfValue('nav-metadata-structures','id','nav-metadata-structures');
        $this->assertEltByIdHasAttrOfValue('nav-metadata-values','id','nav-metadata-values');
        $this->assertEltByIdHasAttrOfValue('nav-authoritative-plants','id','nav-authoritative-plants');
    }

    function testIndexPageHasSplashLinks() {
        $this->get('http://localhost/digitalfieldnotebooks/');

        $this->assertEltByIdHasAttrOfValue('notebooks-splash-link','id','notebooks-splash-link');
        $this->assertEltByIdHasAttrOfValue('plants-splash-link','id','plants-splash-link');
        $this->assertEltByIdHasAttrOfValue('metadata-structures-splash-link','id','metadata-structures-splash-link');
        $this->assertEltByIdHasAttrOfValue('metadata-term-sets-splash-link','id','metadata-term-sets-splash-link');
    }

    function testHelp() {
        $this->todo();
    }

}