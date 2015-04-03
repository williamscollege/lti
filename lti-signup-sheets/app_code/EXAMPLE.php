<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('notebook'));
	require_once(dirname(__FILE__) . '/../app_head.php');

	#############################
	# 0. example of custom SQL using PDO
	# 1. figure out what action is being attempted (none/default is view for a single notebook, list for none specified)
	# 2. figure out which notebook is being acted on (if none specified then redirect to home page for actions other than list)
	# 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
	# 4. branch behavior based on the action
	#############################

	# 0. example of custom SQL using PDO
	// alternate way:
	// Prepare SQL using PDO
	//$sql = "SELECT * FROM ".SUS_Sheetgroup::$dbTable;
	//$sql  = "SELECT * FROM sus_sheetgroups INNER JOIN sus_sheets ON sus_sheetgroups.sheetgroup_id = sus_sheets.sheetgroup_id INNER JOIN sus_openings ON sus_openings.sheet_id = sus_sheets.sheet_id INNER JOIN sus_signups ON sus_signups.opening_id = sus_openings.opening_id WHERE sus_sheetgroups.sheetgroup_id = " . htmlentities($s->sheetgroup_id, ENT_QUOTES, 'UTF-8') . " AND  sus_signups.signup_user_id = " . htmlentities($USER->user_id, ENT_QUOTES, 'UTF-8');
	//$stmt = $DB->prepare($sql);
	//$stmt->execute();
	//$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//util_prePrintR($res);

	# 1. figure out what action is being attempted (none/default is view); also, a bit of param validation
	$action = 'view';
	if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], Action::$VALID_ACTIONS)) {
		$action = $_REQUEST['action'];
	}
	if ((($action == 'edit') || ($action == 'view')) && ((!isset($_REQUEST['notebook_id'])) || (!is_numeric($_REQUEST['notebook_id'])))) {
		util_redirectToAppPage(APP_ROOT_PATH . '/app_code/notebook.php?action=list', 'failure', util_lang('no_notebook_specified'));
	}

	# 2. figure out which notebook is being acted on (if none specified then redirect to home page for actions other than list)
	$notebook                 = '';
	$all_accessible_notebooks = '';
	if ($action == 'create') {

		if ((!isset($_REQUEST['user_id'])) || (!is_numeric($_REQUEST['user_id']))) {
			util_redirectToAppPage(APP_ROOT_PATH . '/app_code/notebook.php?action=list', 'failure', util_lang('no_user_specified'));
		}

		//        $notebook = new Notebook(['user_id' => $USER->user_id, 'name'=>util_lang('new_notebook_title').' '.util_currentDateTimeString(),'DB'=>$DB]);
		$notebook = Notebook::createNewNotebookForUser($USER->user_id, $DB);
	}
	elseif ($action == 'list') {
		$all_accessible_notebooks = $USER->getAccessibleNotebooks($ACTIONS['view']);
		if (count($all_accessible_notebooks) < 1) {
			util_redirectToAppHome('failure', util_lang('no_notebooks_found'));
		}
		$notebook = $all_accessible_notebooks[0];
	}
	else {
		//        if ((! isset($_REQUEST['notebook_id'])) || (! is_numeric($_REQUEST['notebook_id']))) {
		////            util_redirectToAppHome('failure',util_lang('no_notebook_specified'));
		//            util_redirectToAppPage(APP_ROOT_PATH . '/app_code/notebook.php?action=list','failure',util_lang('no_notebook_specified'));
		//        }
		$notebook = Notebook::getOneFromDb(['notebook_id' => $_REQUEST['notebook_id']], $DB);
		if (!$notebook->matchesDb) {
			//            util_redirectToAppHome('failure',util_lang('no_notebook_found'));
			util_redirectToAppPage(APP_ROOT_PATH . '/app_code/notebook.php?action=list', 'failure', util_lang('no_notebook_found'));
		}
	}

	# 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
	if (!$USER->canActOnTarget($ACTIONS[$action], $notebook)) {
		//        util_redirectToAppHome('failure',util_lang('no_permission'));
		if ($action == 'edit') {
			util_redirectToAppPage(APP_ROOT_PATH . '/app_code/notebook.php?action=view&notebook_id=' . htmlentities($notebook->notebook_id, ENT_QUOTES, 'UTF-8'), 'failure', util_lang('no_permission'));
		}
		util_redirectToAppPage(APP_ROOT_PATH . '/app_code/notebook.php?action=list', 'failure', util_lang('no_permission'));
	}


	# 4. branch behavior based on the action
	#      update - update the object with the data coming in, then show the object (w/ 'saved' message)
	#      verify/publish - set the appropriate flag (true or false, depending on data coming in), then show the object (w/ 'saved' message)
	#      view - show the object
	#      create/edit - show a form with the object's current values ($action is 'update' on form submit)
	#      delete - delete the notebook, then go to home page w/ 'deleted' message

	if (($action == 'update') || ($action == 'verify') || ($action == 'publish')) {
		//        echo 'TO BE IMPLEMENTED:: implement update action';
		$changed = FALSE;
		if ($notebook->name != $_REQUEST['name']) {
			$changed        = TRUE;
			$notebook->name = $_REQUEST['name']; // NOTE: this seems dangerous, but the data is sanitized on the way back out
		}
		if ($notebook->notes != $_REQUEST['notes']) {
			$changed         = TRUE;
			$notebook->notes = $_REQUEST['notes']; // NOTE: this seems dangerous, but the data is sanitized on the way back out
		}

		if ($USER->canActOnTarget($ACTIONS['publish'], $notebook)) {
			if (isset($_REQUEST['flag_workflow_published'])) {
				if ($_REQUEST['flag_workflow_published'] && !$notebook->flag_workflow_published) {
					$changed                           = TRUE;
					$notebook->flag_workflow_published = TRUE;
				}
			}
			else {
				if ($notebook->flag_workflow_published) {
					$changed                           = TRUE;
					$notebook->flag_workflow_published = FALSE;
				}
			}
		}

		if ($USER->canActOnTarget($ACTIONS['verify'], $notebook)) {
			if (isset($_REQUEST['flag_workflow_validated'])) {
				if ($_REQUEST['flag_workflow_validated'] && !$notebook->flag_workflow_validated) {
					$changed                           = TRUE;
					$notebook->flag_workflow_validated = TRUE;
				}
			}
			else {
				if ($notebook->flag_workflow_validated) {
					$changed                           = TRUE;
					$notebook->flag_workflow_validated = FALSE;
				}
			}
		}

		if ($changed) {
			$notebook->updateDb();
		}

		//        echo $notebook->renderAsEdit();
		$action = 'view';
	}

	if ($action == 'view') {
		if ($USER->canActOnTarget($ACTIONS['edit'], $notebook)) {
			echo '<div id="actions">' . $notebook->renderAsButtonEdit() . '</div>' . "\n";
		}
		echo $notebook->renderAsView();
	}
	elseif (($action == 'edit') || ($action == 'create')) {
		//echo 'TO BE IMPLEMENTED:: implement edit and create actions';
		echo $notebook->renderAsEdit();
	}
	elseif ($action == 'delete') {
		echo 'TO BE IMPLEMENTED:: implement delete action';
	}
	elseif ($action == 'list') {
		$counter       = 0;
		$num_notebooks = count($all_accessible_notebooks);
		echo '<h2>' . ucfirst(util_lang('notebooks')) . '</h2>';
		echo "<ul id=\"list-of-user-notebooks\" data-notebook-count=\"$num_notebooks\">\n";
		foreach ($all_accessible_notebooks as $notebook) {
			$counter++;
			echo $notebook->renderAsListItem('notebook-item-' . $counter) . "\n";
		}
		echo "</ul>\n";
		if ($USER->canActOnTarget($ACTIONS['create'], new Notebook(['DB' => $DB]))) {
			?>
			<a href="<?php echo APP_ROOT_PATH . '/app_code/notebook.php?action=create&user_id=' . htmlentities($USER->user_id, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default" id="btn-add-notebook"><?php echo util_lang('add_notebook'); ?></a><?php
		}
	}
	require_once(dirname(__FILE__) . '/../foot.php');
?>