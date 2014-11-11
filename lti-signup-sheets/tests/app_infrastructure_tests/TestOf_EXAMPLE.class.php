<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfNotebook extends WMSUnitTestCaseDB {

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testNotebookAtributesExist() {
			$this->assertEqual(count(Notebook::$fields), 9);

			$this->assertTrue(in_array('notebook_id', Notebook::$fields));
            $this->assertTrue(in_array('created_at', Notebook::$fields));
            $this->assertTrue(in_array('updated_at', Notebook::$fields));
			$this->assertTrue(in_array('user_id', Notebook::$fields));
            $this->assertTrue(in_array('name', Notebook::$fields));
            $this->assertTrue(in_array('notes', Notebook::$fields));
			$this->assertTrue(in_array('flag_delete', Notebook::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = new Notebook(['notebook_id' => 50, 'name' => 'nA', 'user_id'=> 101, 'DB' => $this->DB]);
            $n2 = new Notebook(['notebook_id' => 60, 'name' => 'nB', 'user_id'=> 101, 'DB' => $this->DB]);
            $n3 = new Notebook(['notebook_id' => 70, 'name' => 'nB', 'user_id'=> 102, 'DB' => $this->DB]);

			$this->assertEqual(Notebook::cmp($n1, $n2), -1);
			$this->assertEqual(Notebook::cmp($n1, $n1), 0);
			$this->assertEqual(Notebook::cmp($n2, $n1), 1);

			$this->assertEqual(Notebook::cmp($n2, $n3), -1);
		}


        function testCreateNewNotebookForUser() {
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $n = Notebook::createNewNotebookForUser($USER->user_id, $this->DB);

            $this->assertEqual('NEW',$n->notebook_id);
            $this->assertNotEqual('',$n->created_at);
            $this->assertNotEqual('',$n->updated_at);
            $this->assertEqual($USER->user_id,$n->user_id);
            $this->assertPattern('/'.util_lang('new_notebook_title').'/',$n->name);
            $this->assertEqual(util_lang('new_notebook_notes'),$n->notes);
            $this->assertEqual('',$n->flag_workflow_published);
            $this->assertEqual('',$n->flag_workflow_validated);
            $this->assertEqual('',$n->flag_delete);
        }

		//// DB interaction tests

		function testNotebookDBInsert() {
			$n = new Notebook(['notebook_id' => 50, 'user_id' => 101, 'name' => 'testInsertNotebook', 'notes' => 'this is a test notebook', 'DB' => $this->DB]);

			$n->updateDb();

			$n2 = Notebook::getOneFromDb(['notebook_id' => 50], $this->DB);

			$this->assertTrue($n2->matchesDb);
            $this->assertEqual($n2->name, 'testInsertNotebook');
            $this->assertEqual($n2->notes, 'this is a test notebook');
		}

		function testNotebookRetrievedFromDb() {
			$n = new Notebook(['notebook_id' => 1001, 'DB' => $this->DB]);
			$this->assertNull($n->name);

			$n->refreshFromDb();
			$this->assertEqual($n->name, 'testnotebook1');
		}

        //// instance methods - related data

        function testLoadPages() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

            $this->assertEqual(0,count($n->pages));

            $n->loadPages();

            $this->assertEqual(2,count($n->pages));
            $this->assertEqual(1101,$n->pages[0]->notebook_page_id);
            $this->assertEqual(1102,$n->pages[1]->notebook_page_id);
        }

        //// instance methods - object itself

        function testRenderAsListItem_Owner() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

            global $USER;

            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

//            util_prePrintR($USER);

            $rendered = $n->renderAsListItem();
            $canonical = '<li class="owned-object" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1001">testnotebook1</a><span class="icon-pencil"></span>'.' '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

//            exit;

            $rendered = $n->renderAsListItem('testid');
            $canonical = '<li id="testid" class="owned-object" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1001">testnotebook1</a><span class="icon-pencil"></span>'.' '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';
            $this->assertEqual($canonical,$rendered);

            $rendered = $n->renderAsListItem('',['testclass']);
            $canonical = '<li class="testclass owned-object" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1001">testnotebook1</a><span class="icon-pencil"></span>'.' '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';
            $this->assertEqual($canonical,$rendered);

            $rendered = $n->renderAsListItem('',[],['data-first-arbitrary'=>'testarbitrary1','data-second-arbitrary'=>'testarbitrary2']);
            $canonical = '<li class="owned-object" data-first-arbitrary="testarbitrary1" data-second-arbitrary="testarbitrary2" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1001">testnotebook1</a><span class="icon-pencil"></span>'.' '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            $rendered = $n->renderAsListItem('',[],['data-second-arbitrary'=>'testarbitrary2','data-first-arbitrary'=>'testarbitrary1']);
            $canonical = '<li class="owned-object" data-first-arbitrary="testarbitrary1" data-second-arbitrary="testarbitrary2" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1001">testnotebook1</a><span class="icon-pencil"></span>'.' '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';
            $this->assertEqual($canonical,$rendered);

            unset($USER);
        }

        function testRenderAsListItem_NonOwner() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1004], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

//            util_prePrintR($USER);

            $rendered = $n->renderAsListItem();
            $canonical = '<li data-notebook_id="1004" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="110" data-name="testnotebook4" data-notes="this is generally viewable testnotebook4, owned by user 110" data-flag_workflow_published="1" data-flag_workflow_validated="1" data-flag_delete="0"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1004">testnotebook4</a>'.' '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            unset($USER);
        }

        function testRenderAsListItem_Admin() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1003], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $rendered = $n->renderAsListItem();
            $canonical = '<li data-notebook_id="1003" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="102" data-name="testnotebook3" data-notes="this is testnotebook3, owned by user 102" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1003">testnotebook3</a> '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            $USER->flag_is_system_admin = true;

//            util_prePrintR($USER);

            $rendered = $n->renderAsListItem();
            $canonical = '<li class="editable-object" data-notebook_id="1003" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="102" data-name="testnotebook3" data-notes="this is testnotebook3, owned by user 102" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1003">testnotebook3</a><span class="icon-pencil"></span> '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            unset($USER);
        }

        function testRenderAsListItem_Manager() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1003], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['user_id'=>110], $this->DB);

            $rendered = $n->renderAsListItem();
            $canonical = '<li class="editable-object" data-notebook_id="1003" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="102" data-name="testnotebook3" data-notes="this is testnotebook3, owned by user 102" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id=1003">testnotebook3</a><span class="icon-pencil"></span> '.util_lang('attribution').' '.$n->getUser()->renderMinimal(true).'</li>';

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            unset($USER);
        }

        function testRenderAsButtonEdit() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

            $canonical = '<a id="notebook-btn-edit-1001" href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=edit&notebook_id='.$n->notebook_id.'" class="edit_link btn"><i class="icon-edit"></i> '.util_lang('edit').'</a>';
            $rendered = $n->renderAsButtonEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsLink($action='view') {
            $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

            $canonical = '<a href="'.APP_ROOT_PATH.'/app_code/notebook.php?action='.$action.'&notebook_id='.$n->notebook_id.'">'.htmlentities($n->name).'</a>';
            $rendered = $n->renderAsLink();

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsView() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $n->loadPages();

            $canonical = '<div id="rendered_notebook_1001" class="rendered_notebook" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1">
  <h3 class="notebook_title"><a href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=list">'.ucfirst(util_lang('notebook')).'</a>: testnotebook1</h3>
  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($n->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($n->updated_at).'</span></div>
  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id='.$USER->user_id.'">'.$USER->screen_name.'</a></div>
  <div class="info-workflow"><span class="published_state">'.util_lang('published_false').'</span>, <span class="verified_state verified_state_false">'.util_lang('verified_false').'</span></div>
  <div class="notebook-notes">this is testnotebook1, owned by user 101</div>
  <h4>'.ucfirst(util_lang('pages')).'</h4>
  <ul id="list-of-notebook-pages" data-notebook-page-count="2">
';
            $page_counter = 0;
            foreach ($n->pages as $p) {
                $page_counter++;
                $canonical .= '    '.$p->renderAsListItem('notebook-page-item-'.$page_counter)."\n";
            }
            $canonical .=
'  </ul>
</div>';
            $rendered = $n->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }



        function testRenderAsEdit_owner() {
            $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $n->loadPages();

            $canonical = '<div id="edit_rendered_notebook_1001" class="edit_rendered_notebook" data-notebook_id="1001" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="testnotebook1" data-notes="this is testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1">
<form action="'.APP_ROOT_PATH.'/app_code/notebook.php">
<div id="actions">
  <button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control"><i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize').'</button>
  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=view&notebook_id=1001"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>
</div>
  <input type="hidden" name="action" value="update"/>
  <input type="hidden" name="notebook_id" value="1001"/>
  <h3 class="notebook_title">'.ucfirst(util_lang('notebook')).': <input id="notebook-name" type="text" name="name" value="testnotebook1"/></h3>
  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($n->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($n->updated_at).'</span></div>
  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id=101">'.$USER->screen_name.'</a></div>
<div class="control-workflows">  <span class="published_state workflow-control"><input id="notebook-workflow-publish-control" type="checkbox" name="flag_workflow_published" value="1" /> '.util_lang('publish').'</span>, <span class="verified_state verified_state_false workflow-info">'.util_lang('verified_false').'</span></div>
  <div class="notebook_notes"><textarea id="notebook-notes" name="notes" rows="4" cols="120">this is testnotebook1, owned by user 101</textarea></div>
</form>
  <h4>'.ucfirst(util_lang('pages')).'</h4>
  <ul id="list-of-notebook-pages" data-notebook-page-count="2">
    <li><a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=create&notebook_id=1001" id="btn-add-notebook-page" class="creation_link btn">+ Add Page / Entry +</a></li>
';
            $page_counter = 0;
            foreach ($n->pages as $p) {
                $page_counter++;
                $canonical .= '    '.$p->renderAsListItem('notebook-page-item-'.$page_counter)."\n";
            }
            $canonical .=
                '  </ul>
</div>';
            $rendered = $n->renderAsEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEdit_newNotebook() {
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $n = Notebook::createNewNotebookForUser($USER->user_id, $this->DB);

    //        $this->fail();

//            $canonical = '<div id="edit_rendered_notebook_NEW" class="edit_rendered_notebook" data-notebook_id="NEW" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="'.htmlentities($n->name).'" data-notes="'.htmlentities(util_lang('new_notebook_notes')).'" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1">
//<form action="'.APP_ROOT_PATH.'/app_code/notebook.php">
//  <input type="hidden" name="action" value="update"/>
//  <input type="hidden" name="notebook_id" value="NEW"/>
//  <h3 class="notebook_title">'.ucfirst(util_lang('notebook')).': <input id="notebook-name" type="text" name="name" value="'.htmlentities($n->name).'"/></h3>
//  <span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($n->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($n->updated_at).'</span><br/>
//  <span class="owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id=101">'.$USER->screen_name.'</a></span><br/>
//  <div class="notebook-notes"><textarea id="notebook-notes" name="notes" rows="4" cols="120">'.htmlentities(util_lang('new_notebook_notes')).'</textarea></div>
//  <input id="edit-submit-control" class="btn" type="submit" name="edit-submit-control" value="'.util_lang('save','properize').'"/>
//  <a id="edit-cancel-control" class="btn" href="/digitalfieldnotebooks/app_code/notebook.php?action=list">'.util_lang('cancel','properize').'</a>
//</form>
//  <h4>'.ucfirst(util_lang('pages')).'</h4>
//  '.util_lang('new_notebook_must_be_saved').'
//</div>';

            $canonical = '<div id="edit_rendered_notebook_NEW" class="edit_rendered_notebook" data-notebook_id="NEW" data-created_at="'.$n->created_at.'" data-updated_at="'.$n->updated_at.'" data-user_id="101" data-name="'.htmlentities($n->name).'" data-notes="'.htmlentities(util_lang('new_notebook_notes')).'" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1">
<form action="'.APP_ROOT_PATH.'/app_code/notebook.php">
<div id="actions">
  <button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control"><i class="icon-ok-sign icon-white"></i> '.util_lang('save','properize').'</button>
  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=list"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>
</div>
  <input type="hidden" name="action" value="update"/>
  <input type="hidden" name="notebook_id" value="NEW"/>
  <h3 class="notebook_title">'.ucfirst(util_lang('notebook')).': <input id="notebook-name" type="text" name="name" value="'.htmlentities($n->name).'"/></h3>
  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($n->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($n->updated_at).'</span></div>
  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id=101">'.$USER->screen_name.'</a></div>
<div class="control-workflows"></div>
  <div class="notebook_notes"><textarea id="notebook-notes" name="notes" rows="4" cols="120">'.htmlentities(util_lang('new_notebook_notes')).'</textarea></div>
</form>
  <h4>'.ucfirst(util_lang('pages')).'</h4>
  '.util_lang('new_notebook_must_be_saved').'
</div>';

            $rendered = $n->renderAsEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n-----------------\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

}