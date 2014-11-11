<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class NotebookViewTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
        global $CUR_LANG_SET;
        $CUR_LANG_SET = 'en';
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function doLoginBasic() {
        $this->get('http://localhost/digitalfieldnotebooks/');
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

    function goToNotebookView($notebook_id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=view&notebook_id='.$notebook_id);
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testViewIsDefaultForANotebook() {
        $this->doLoginBasic();

        $this->goToNotebookView(1001);

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?notebook_id=1001');

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testMissingNotebookIdShowsNotebookListInstead() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=view');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_specified'));
//        $this->todo();
    }

    function testNonexistentNotebookShowsNotebookListInstead() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=view&notebook_id=999');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_found'));
//        $this->todo();
    }

    function testActionNotAllowedNotebookListInstead() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1004');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
//        $this->todo();
    }

    function testViewList_LoggedIn() {
        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=list');

        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        // number of notebooks shown
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','data-notebook-count','3');

        $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1001');
        $this->assertEltByIdHasAttrOfValue('notebook-item-2','data-notebook_id','1002');
        $this->assertEltByIdHasAttrOfValue('notebook-item-3','data-notebook_id','1004');

        $this->assertEltByIdHasAttrOfValue('notebook-item-1','class','owned-object');
        $this->assertEltByIdHasAttrOfValue('notebook-item-2','class','owned-object');
        $this->assertEltByIdDoesNotHaveAttr('notebook-item-3','data-can-edit');


        $this->assertLink('testnotebook1');
        $this->assertLink('testnotebook2');
        $this->assertLink('testnotebook4');

        ///////////////////////////////////////////
        // 'add notebook' control
        $this->assertPattern("/btn-add-notebook/");
        ///////////////////////////////////////////
    }

    function testViewList_Public() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=list');

        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        // number of notebooks shown
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','data-notebook-count','1');

//        $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1001');
//        $this->assertEltByIdHasAttrOfValue('notebook-item-2','data-notebook_id','1002');
        $this->assertEltByIdHasAttrOfValue('notebook-item-1','data-notebook_id','1004');
//
//        $this->assertEltByIdHasAttrOfValue('notebook-item-1','class','owned-object');
//        $this->assertEltByIdHasAttrOfValue('notebook-item-2','class','owned-object');
        $this->assertEltByIdDoesNotHaveAttr('notebook-item-1','data-can-edit');
//        $this->assertEltByIdDoesNotHaveAttr('notebook-item-2','data-can-edit');
//        $this->assertEltByIdDoesNotHaveAttr('notebook-item-3','data-can-edit');

//        $this->assertLink('testnotebook1');
//        $this->assertLink('testnotebook2');
        $this->assertLink('testnotebook4');

        ///////////////////////////////////////////
        // NO 'add notebook' control
        $this->assertNoPattern("/btn-add-notebook/");
        ///////////////////////////////////////////
    }

    function testViewEditable() {
        $this->doLoginBasic();

        $this->goToNotebookView(1001);

//        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        $n = Notebook::getOneFromDb(['notebook_id'=>1001],$this->DB);

//        util_prePrintR($n);

        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);
        $ap2 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5008],$this->DB);

        // page heading text
        $this->assertText(ucfirst(util_lang('notebook')).':');

        $this->assertText($n->name);
        $this->assertText($n->notes);

        // 'edit' control
        $this->assertEltByIdHasAttrOfValue('notebook-btn-edit-1001','href',APP_ROOT_PATH.'/app_code/notebook.php?action=edit&notebook_id=1001');
        $this->assertLink(util_lang('edit'));

        // number of notebook pages
        $this->assertEltByIdHasAttrOfValue('list-of-notebook-pages','data-notebook-page-count','2');
        $this->assertEltByIdHasAttrOfValue('notebook-page-item-1','data-notebook_page_id','1101');
        $this->assertEltByIdHasAttrOfValue('notebook-page-item-2','data-notebook_page_id','1102');

        $this->assertLink($ap1->renderAsShortText());
        $this->assertLink($ap2->renderAsShortText());

        // NO 'add page' control - only in edit mode!
//        $this->assertEltByIdHasAttrOfValue('btn-add-notebook-page','href',APP_ROOT_PATH.'/app_code/notebook_page.php?action=create&notebook_id=1001');
        $this->assertNoLink(util_lang('add_notebook_page'));
    }

    function testViewNotEditable() {
        $this->doLoginBasic();

        $this->goToNotebookView(1004);

//        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        $n = Notebook::getOneFromDb(['notebook_id'=>1004],$this->DB);

//        util_prePrintR($n);

        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        // page heading text
        $this->assertText(ucfirst(util_lang('notebook')));

        $this->assertText($n->name);
        $this->assertText($n->notes);

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // number of notebook pages
        $this->assertEltByIdHasAttrOfValue('list-of-notebook-pages','data-notebook-page-count','1');
        $this->assertEltByIdHasAttrOfValue('notebook-page-item-1','data-notebook_page_id','1104');

        $this->assertLink($ap1->renderAsShortText());

        // NO 'add page' control
        $this->assertNoLink(util_lang('add_notebook_page'));

    }

}