<?php
	require_once(dirname(__FILE__) . '/../simpletest/WMS_web_tester.php');

	class IndexPageLoggedInTest extends WMSWebTestCase {

		function setUp() {
			createAllTestData($this->DB);
			global $CUR_LANG_SET;
			$CUR_LANG_SET = 'en';
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function doLoginBasic() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/');
			$this->assertCookie('PHPSESSID');
			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);

			$this->click('Sign in');

			$this->assertNoPattern('/warning/i');
			$this->assertNoPattern('/error/i');
		}

		function doLoginAdmin() {
			makeAuthedTestUserAdmin($this->DB);
			$this->doLoginBasic();
		}


		function testIndexBasic() {
			$this->doLoginBasic();

			$this->assertNoPattern('/UNKNOWN LANGUAGE LABEL/i');
			$this->assertFalse($this->setField('password', 'bar')); //$value
			$this->assertPattern('/\<a[^\>]*\>' . TESTINGUSER . '\<\/a\>/');
			$this->assertNoPattern('/Sign in failed/i');
			$this->assertEltByIdHasAttrOfValue('submit_signout', 'value', new PatternExpectation('/Sign\s?out/i'));

			//        // page heading text
			//        $this->assertText(ucfirst(util_lang('you_possesive')).' '.ucfirst(util_lang('notebooks')));
			//
			//        // number of notebooks shown
			//        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','data-notebook-count','3');
			//
			//        $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1001');
			//        $this->assertEltByIdHasAttrOfValue('notebook-item-2','data-notebook_id','1002');
			//        $this->assertEltByIdHasAttrOfValue('notebook-item-3','data-notebook_id','1004');
			//
			//        $this->assertLink('testnotebook1');
			//        $this->assertLink('testnotebook2');
			//        $this->assertLink('testnotebook4');
			//
			//        // 'add notebook' control
			//        $this->assertEltByIdHasAttrOfValue('btn-add-notebook','value',util_lang('add_notebook'));

			// link to main/front page
			$this->assertEltByIdHasAttrOfValue('nav-link-available-openings', 'href', APP_ROOT_PATH."/app_code/sheet_openings_all.php");
		}

		//    function testIndexBasicNoCreate() {
		//        $rat = Role_Action_Target::getOneFromDb(['role_action_target_link_id'=>220],$this->DB);
		//        $rat->doDelete();
		//
		//        $this->doLoginBasic();
		//
		//        // same as basic, but should have links to all four notebooks, with can-edit on all of them
		//        $this->assertNoPattern('/UNKNOWN LANGUAGE LABEL/i');
		//        $this->assertFalse($this->setField('password','bar')); //$value
		//        $this->assertPattern('/\<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
		//        $this->assertNoPattern('/Sign in failed/i');
		//        $this->assertEltByIdHasAttrOfValue('submit_signout','value',new PatternExpectation('/Sign\s?out/i'));
		//
		//        // page heading text
		//        $this->assertText(ucfirst(util_lang('you_possesive')).' '.ucfirst(util_lang('notebooks')));
		//
		//        // number of notebooks shown
		//        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','data-notebook-count','3');
		//
		//        $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1001');
		//        $this->assertEltByIdHasAttrOfValue('notebook-item-2','data-notebook_id','1002');
		//
		//        $this->assertEltByIdHasAttrOfValue('notebook-item-1','class','owned-object');
		//        $this->assertEltByIdHasAttrOfValue('notebook-item-2','class','owned-object');
		//        $this->assertEltByIdDoesNotHaveAttr('notebook-item-3','data-can-edit');
		//
		//        $this->assertLink('testnotebook1');
		//        $this->assertLink('testnotebook2');
		//        $this->assertLink('testnotebook4');
		//
		//        ///////////////////////////////////////////
		//        // NO 'add notebook' control
		//        $this->assertNoPattern("/btn-add-notebook/");
		//        ///////////////////////////////////////////
		//
		//        $this->assertEltByIdHasAttrOfValue('nav-link-available-openings','href',APP_FOLDER);
		//    }

		function testIndexAdmin() {
			$this->doLoginAdmin();

			// same as basic, but should have links to all four notebooks, with can-edit on all of them
			$this->assertNoPattern('/UNKNOWN LANGUAGE LABEL/i');
			$this->assertFalse($this->setField('password', 'bar')); //$value
			$this->assertPattern('/\<a[^\>]*\>' . TESTINGUSER . '\<\/a\>/');
			$this->assertNoPattern('/Sign in failed/i');
			$this->assertEltByIdHasAttrOfValue('submit_signout', 'value', new PatternExpectation('/Sign\s?out/i'));

			// check for admin tool links
		}

		function testIndexPageHasCorrectMenus() {
			$this->get('http://localhost' . APP_ROOT_PATH . '/app_code/signups_all.php');
			// echo 'http://localhost' . APP_ROOT_PATH . '/app_code/signups_all.php';

//			$this->assertEltByIdHasAttrOfValue('nav-link-my-signups', 'id');
//			$this->assertEltByIdHasAttrOfValue('nav-link-available-openings', 'id', 'nav-link-available-openings');
//			$this->assertEltByIdHasAttrOfValue('nav-link-my-sheets', 'id', 'nav-link-my-sheets');
//			$this->assertEltByIdHasAttrOfValue('nav-link-help', 'id', 'nav-link-help');
		}

	}