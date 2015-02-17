<?php
	require_once('head_ajax.php');

	//	# tests
	//	$action        = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	//	# Output
	//	$results['status']       = 'success';
	//	$results['which_action'] = $action;
	//	$results['html_output']  = 'smiling now';
	//	# Return JSON array
	//	echo json_encode($results);
	//	exit;


	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	// TODO - Can generalize some of these passed param values and reduce the number found here...
	$action       = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	$ownerUserID  = htmlentities((isset($_REQUEST["ajaxVal_OwnerUserID"])) ? $_REQUEST["ajaxVal_OwnerUserID"] : 0);
	$sheetgroupID = htmlentities((isset($_REQUEST["ajaxVal_SheetgroupID"])) ? $_REQUEST["ajaxVal_SheetgroupID"] : 0);
	$sheetID      = htmlentities((isset($_REQUEST["ajaxVal_SheetID"])) ? $_REQUEST["ajaxVal_SheetID"] : 0);
	$name         = htmlentities((isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : 0);
	$description  = htmlentities((isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : 0);
	$maxTotal     = htmlentities((isset($_REQUEST["ajaxVal_MaxTotal"])) ? $_REQUEST["ajaxVal_MaxTotal"] : 0);
	$maxPending   = htmlentities((isset($_REQUEST["ajaxVal_MaxPending"])) ? $_REQUEST["ajaxVal_MaxPending"] : 0);
	$deleteID     = htmlentities((isset($_REQUEST["ajaxVal_Delete_ID"])) ? $_REQUEST["ajaxVal_Delete_ID"] : 0);
	$editID       = htmlentities((isset($_REQUEST["ajaxVal_Edit_ID"])) ? $_REQUEST["ajaxVal_Edit_ID"] : 0);
	$editValue    = htmlentities((isset($_REQUEST["ajaxVal_Edit_Value"])) ? $_REQUEST["ajaxVal_Edit_Value"] : 0);


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];


	#------------------------------------------------#
	# Identify and process requested action
	#------------------------------------------------#
	//###############################################################
	if ($action == 'add-sheetgroup') {
		# TODO UNSURE ABOUT Necessity of this: NEED TO ENSURE THAT ADD Sheetgroup cannot add a new group with same name of pre-existing sheetgroup
		# TODO maybe just create a createSheetgroup fxn, instead and allow duplicate names?
		$sg = SUS_Sheetgroup::getOneFromDb(['name' => $name], $DB);

		if ($sg->matchesDb) {
			// error: matching record already exists
			$results["notes"] = "matching record already exists";
			echo json_encode($results);
			exit;
		}
		$sg->owner_user_id              = $ownerUserID;
		$sg->name                       = $name;
		$sg->description                = $description;
		$sg->max_g_total_user_signups   = $maxTotal;
		$sg->max_g_pending_user_signups = $maxPending;
		$sg->updated_at                 = date("Y-m-d H:i:s");

		$sg->updateDb();

		$sheetgroup = SUS_Sheetgroup::getOneFromDb(['name' => $name], $DB);

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'add-sheetgroup';
		# inject into DOM
		$results['html_output'] = '';
		$results['html_output'] .= "<table class=\"table table-condensed table-bordered table-hover\"><tbody>";
		$results['html_output'] .= "<tr class=\"info\"><th class=\"col-sm-11\">";
		$results['html_output'] .= "<a href=\"#modalSheetgroup\" id=\"btn-edit-sheetgroup-id-" . $sheetgroup->sheetgroup_id . "\" class=\"sus-edit-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" data-for-sheetgroup-name=\"" . $sheetgroup->name . "\" data-for-sheetgroup-description=\"" . $sheetgroup->description . "\" data-for-sheetgroup-max-total=\"" . $sheetgroup->max_g_total_user_signups . "\" data-for-sheetgroup-max-pending=\"" . $sheetgroup->max_g_pending_user_signups . "\" title=\"Edit group\">" . $sheetgroup->name . "</a></th><th class=\"col-sm-1 text-right\"><a class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" title=\"Delete group and all sheets in it\"><i class=\"glyphicon glyphicon-trash\"></i> Group</a>&nbsp;";
		$results['html_output'] .= "</th></tr>";
		$results['html_output'] .= "<tr><td class=\"col-sm-12\" colspan=\"2\">";
		$results['html_output'] .= "<a href=\"edit_sheet.php?sheetgroup=" . $sheetgroup->sheetgroup_id . "&sheet=new\" class=\"btn btn-xs btn-success sus-add-sheet\" title=\"Add new sheet\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new sheet to this group</a>";
		$results['html_output'] .= "</td></tr>";
		$results['html_output'] .= "</tbody></table>";
	}
	//###############################################################
	elseif ($action == 'edit-sheetgroup') {
		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $sheetgroupID], $DB);

		if (!$sg->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}
		$sg->name                       = $name;
		$sg->description                = $description;
		$sg->max_g_total_user_signups   = $maxTotal;
		$sg->max_g_pending_user_signups = $maxPending;
		$sg->updated_at                 = date("Y-m-d H:i:s");

		$sg->updateDb();

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-sheetgroup';
		$results['html_output']  = '';
	}
	//###############################################################
	elseif ($action == 'delete-sheetgroup') {
		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $deleteID], $DB);

		if (!$sg->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		# mark this object as deleted as well as any lower dependent items
		$sg->cascadeDelete();

		# Output
		if ($sg->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'add-sheet') {
		$s = SUS_Sheet::getOneFromDb(['name' => $name], $DB);

		# TODO this is NOT YET IMPLEMENTED... JUST EXAMPLE CODE BELOW...

		if ($s->matchesDb) {
			// error: matching record already exists
			$results["notes"] = "matching record already exists";
			echo json_encode($results);
			exit;
		}
		$s->owner_user_id              = $ownerUserID;
		$s->name                       = $name;
		$s->description                = $description;
		$s->max_g_total_user_signups   = $maxTotal;
		$s->max_g_pending_user_signups = $maxPending;
		$s->updated_at                 = date("Y-m-d H:i:s");

		$s->updateDb();

		# TODO Search for 'sheetgroup' and update where approprate to 'sheet'
		# TODO NEED TO ENSURE THAT ADD Sheetgroup cannot add a new group with same name of pre-existing sheetgroup
		$sheetgroup = SUS_Sheetgroup::getOneFromDb(['name' => $name], $DB);

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'add-sheetgroup';
		# inject into DOM
		$results['html_output'] = '';
		$results['html_output'] .= "<table class=\"table table-condensed table-bordered table-hover\"><tbody>";
		$results['html_output'] .= "<tr class=\"info\"><th class=\"col-sm-11\">";
		$results['html_output'] .= "<a href=\"#modalSheetgroup\" id=\"btn-edit-sheetgroup-id-" . $sheetgroup->sheetgroup_id . "\" class=\"sus-edit-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" data-for-sheetgroup-name=\"" . $sheetgroup->name . "\" data-for-sheetgroup-description=\"" . $sheetgroup->description . "\" data-for-sheetgroup-max-total=\"" . $sheetgroup->max_g_total_user_signups . "\" data-for-sheetgroup-max-pending=\"" . $sheetgroup->max_g_pending_user_signups . "\" title=\"Edit group\">" . $sheetgroup->name . "</a></th><th class=\"col-sm-1 text-right\"><a class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" title=\"Delete group and all sheets in it\"><i class=\"glyphicon glyphicon-trash\"></i> Group</a>&nbsp;";
		$results['html_output'] .= "</th></tr>";
		$results['html_output'] .= "<tr><td class=\"col-sm-12\" colspan=\"2\">";
		$results['html_output'] .= "<a href=\"edit_sheet.php?sheetgroup=" . $sheetgroup->sheetgroup_id . "\" class=\"btn btn-xs btn-success sus-add-sheet\" title=\"Add new sheet\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new sheet to this group</a>";
		$results['html_output'] .= "</td></tr>";
		$results['html_output'] .= "</tbody></table>";
	}
	//###############################################################
	elseif ($action == 'delete-sheet') {
		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $deleteID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		# mark this object as deleted as well as any lower dependent items
		$s->cascadeDelete();

		# Output
		if ($s->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'delete-opening') {
		$o = SUS_Opening::getOneFromDb(['opening_id' => $deleteID], $DB);

		if (!$o->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		# mark this object as deleted as well as any lower dependent items
		$o->cascadeDelete();

		# Output
		if ($o->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'delete-signup' || $action == 'delete-signup-from-edit-opening-modal') {
		$s = SUS_Signup::getOneFromDb(['signup_id' => $deleteID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		# mark this object as deleted as well as any lower dependent items
		$s->cascadeDelete();

		# Output
		if ($s->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-flag-private-signups') {
		// values: $editID, $editValue

		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $editID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		# mark this object as deleted as well as any lower dependent items
		$s->flag_private_signups = $editValue;
		$s->updateDB();

		# Output
		if ($s->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-course-remove') {
		doAccessRemove('bycourse', $editID, $editValue, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-course-add') {
		doAccessAdd('bycourse', $editID, $editValue, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-instructor-remove') {
		doAccessRemove('byinstr', $editID, $editValue, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-instructor-add') {
		doAccessAdd('byinstr', $editID, $editValue, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-role-remove') {
		doAccessRemove('byrole', $editID, $editValue, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-role-add') {
		doAccessAdd('byrole', $editID, $editValue, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-any-remove') {
		doAccessRemove('byhasaccount', $editID, 'all', $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-any-add') {
		doAccessAdd('byhasaccount', $editID, 'all', $results);
	}
	//###############################################################
	elseif (($action == 'editSheetAccess-access-by-user') || ($action == 'editSheetAccess-admin-by-user')) {
		$access_type = 'byuser';
		if ($action == 'editSheetAccess-admin-by-user') {
			$access_type = 'adminbyuser';
		}
		// values: $editID, $editValue

		// 1 clean incoming user namea and split into array
		// 2 get existing byuser records
		// 3 generate to-add and to-remove sets
		// 4 do adds
		// 5 do removes
		// 6 note results

		// 1 clean incoming user namea and split into array
		$usernames_str = $editValue;
		$usernames_str = preg_replace('/,/', ' ', $usernames_str); // convert commas to single white space
		$usernames_str = preg_replace('/\\s+/', ' ', $usernames_str); // convert all white space to single white space
		$usernames_str = preg_replace('/^\\s+|\\s+$/', '', $usernames_str); // trim leading and trailing space
		$usernames_ary = explode(' ', $usernames_str);

		// 2 get existing byuser records
		$existing_access_records   = SUS_Access::getAllFromDb(['sheet_id' => $editID, 'type' => $access_type], $DB);
		$existing_access_usernames = Db_Linked::arrayOfAttrValues($existing_access_records, 'constraint_data');

		// 3 generate to-add and to-remove sets
		$to_add = [];
		foreach ($usernames_ary as $username) {
			if (!in_array($username, $existing_access_usernames)) {
				array_push($to_add, $username);
			}
		}
		$to_remove = [];
		foreach ($existing_access_usernames as $username) {
			if (!in_array($username, $usernames_ary)) {
				array_push($to_remove, $username);
			}
		}

		$results["notes"] = '';

		// 4 do adds

		if (count($to_add) > 0) {
			// only fetch big user object if we are going to add a username
			$obj_all_users = User::getAllFromDb([], $DB);
			// remove cruft
			$obj_all_attr_usernames = Db_Linked::arrayOfAttrValues($obj_all_users, 'username');
			foreach ($to_add as $username_to_add) {
				if (!in_array($username_to_add, $obj_all_attr_usernames)) {
					// username does not exist
					$results["notes"] .= "invalid username: " . $username_to_add . "<br/>\n";
				}
				else {
					$access_record = SUS_Access::createNewAccess($access_type, $editID, 0, $username_to_add, $DB);
					$access_record->updateDb();
					if (!$access_record->matchesDb) {
						$results["notes"] .= "could not save access for " . $username_to_add . "<br/>\n";
					}
				}
			}
		}

		// 5 do removes
		foreach ($to_remove as $username_to_remove) {
			$access_record = SUS_Access::getOneFromDb(['type' => $access_type, 'sheet_id' => $editID, 'constraint_data' => $username_to_remove], $DB);
			if (!$access_record->matchesDb) {
				$results["notes"] .= "no existing access record found for " . $username_to_remove . "<br/>\n";
				continue;
			}
			$access_record->doDelete();

			$check_access_record = SUS_Access::getOneFromDb(['type' => $access_type, 'sheet_id' => $editID, 'constraint_data' => $username_to_remove], $DB);
			if ($check_access_record->matchesDb) {
				$results["notes"] .= "could not remove access for " . $username_to_remove . "<br/>\n";
			}
		}

		// 6 note results
		if ($results["notes"]) {
			$results["notes"] = "<br/>Problems saving one or more usernames:<br/>\n" . $results["notes"];
		}
		else {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'edit-opening-add-signup-user') {


		// 1. Is username valid (against big table of Williams usernames)
		$u = User::getOneFromDb(['username' => $name], $DB);

		if (!$u->matchesDb) {
			// error: no matching record found
			$results["notes"] = "that username does not exist";
			echo json_encode($results);
			exit;
		}

		// check if submitted user already has a signup for this opening
		$s = SUS_Signup::getOneFromDb(['opening_id' => $editID, 'signup_user_id' => $u->user_id], $DB);

		// update or create signup record
		if ($s->matchesDb) {
			// update preexisting record
			//'signup_id', 'created_at', 'updated_at', 'flag_delete', 'opening_id', 'signup_user_id', 'admin_comment'
			$s->flag_delete    = 0;
			$s->updated_at     = util_currentDateTimeString_asMySQL();
			$s->opening_id     = $editID;
			$s->signup_user_id = $u->user_id;
			$s->admin_comment  = $description;

			$s->updateDb();

			if (!$s->matchesDb) {
				// update record failed
				$results["notes"] = "database error: could not update signup";
				echo json_encode($results);
				exit;
			}
		}
		else {
			// create new record
			$s = SUS_Signup::createNewSignup($DB);

			$s->opening_id     = $editID;
			$s->signup_user_id = $u->user_id;
			$s->admin_comment  = $description;

			$s->updateDb();

			if (!$s->matchesDb) {
				// create record failed
				$results["notes"] = "database error: could not save signup";
				echo json_encode($results);
				exit;
			}
		}

		/*		// get all signups for this opening
				$o = SUS_Opening::getAllFromDb(['opening_id'=>$s->opening_id], $DB);

				if ($o) {
					$o->cacheSignups();

					foreach ($o->signups as $signup) {
						$list_of_signups .= "<li data-for-firstname=\"" . $u->firstname . "\" data-for-lastname=\"" . $u->lastname . "\" data-for-signup-id=\"" . $s->signup_id . "\">";
						$list_of_signups .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-signup-id=\"" . $s->signup_id . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i> </a>&nbsp;";
						$list_of_signups .=  $u->first_name . " " . $u->last_name . "</li>";
					}
				}*/


		// TODO - return name
		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-opening-add-signup-user';
		# inject into DOM
		$results['html_output'] = "<li data-for-firstname=\"" . $u->firstname . "\" data-for-lastname=\"" . $u->lastname . "\" data-for-signup-id=\"" . $s->signup_id . "\">";
		$results['html_output'] .= "<a href=\"#\" class=\"sus-delete-signup\" data-bb=\"alert_callback\" data-for-signup-id=\"" . $s->signup_id . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i> </a>&nbsp;";
		$results['html_output'] .= $u->first_name . " " . $u->last_name . "</li>";
		//<li signuptime="1424113532" fname="David" lname="Keiser-Clark" id="signee_list_item_for_33305" signup_id="33305" class="signee_list_item"> <img for_opening="82024" for_signup="33305" title="remove signup" alt="remove signup" src="image/pix/t/delete.png" class="remove_signup_link nukeit">David Keiser-Clark
		//		<span class="sus_very_small">(signed up 2015-02-16 02:05 PM)</span><br>
		//    <label for="su_admin_comment_33305">Admin Note:</label><textarea id="su_admin_comment_33305" class="su_admin_comment" name="su_admin_comment_33305">note 1</textarea>  </li>
		//$results['html_output'] = "placeholder name (and signed up date)";
	}    //###############################################################
	elseif ($action == 'fetch-signups-for-opening-id') {

		// TODO - Create this at the class level, instead?

		// get all signups for this opening
		$o = SUS_Opening::getOneFromDb(['opening_id' => $editID], $DB);

		if (!$o->matchesDb) {
			// error: no matching record found
			$results["notes"] = "that opening does not exist";
			echo json_encode($results);
			exit;
		}

		$o->cacheSignups();
		//util_prePrintR($o->signups);
//		util_prePrintR($o);

		// create hash of signup user_ids
		$signupUserIdsAry = [];
		foreach ($o->signups as $signup) {
			if (!in_array($signup->signup_user_id, $signupUserIdsAry)) {
				array_push($signupUserIdsAry, $signup->signup_user_id);
			}
		}
		if (! $signupUserIdsAry) {
			$results['status']       = 'success';
			$results['which_action'] = 'fetch-signups-for-opening-id';
			# inject into DOM
			$results['html_output'] = '<li>no signups</li>';
			echo json_encode($results);
			exit;
		}

		// fetch users
		$users_info = User::getAllFromDb(['user_id' => $signupUserIdsAry], $DB);
//		util_prePrintR($users_info);

		$signups_list = "";
//		$user_info_reference = Db_Linked::arrayToPkHash($users_info);
//		util_prePrintR($user_info_reference);

		foreach ($o->signups as $signup) {
//			$u = $user_info_reference[$signup->signup_user_id];
//			$signups_list .= "<li data-for-firstname=\"" . $u->first_name . "\" data-for-lastname=\"" . $u->last_name . "\" data-for-signup-id=\"" . $signup->signup_id . "\">";
//			$signups_list .= "<a href=\"#\" class=\"sus-delete-signup wms-custom-delete\" data-bb=\"alert_callback\" data-for-signup-id=\"" . $signup->signup_id . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i> </a>&nbsp;";
//			$signups_list .= $u->first_name . " " . $u->last_name . "</li>";

			foreach ($users_info as $user) {
				if ($signup->signup_user_id == $user->user_id) {
// TODO - refactor this as a render function
					$signups_list .= "<li data-for-firstname=\"" . $user->first_name . "\" data-for-lastname=\"" . $user->last_name . "\" data-for-signup-id=\"" . $signup->signup_id . "\">";
					$signups_list .= "<a href=\"#\" class=\"sus-delete-signup wms-custom-delete\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $o->opening_id . "\" data-for-signup-id=\"" . $signup->signup_id . "\" data-for-signup-name=\"" . $user->first_name . " " . $user->last_name . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i> </a>&nbsp;";
					$signups_list .= $user->first_name . " " . $user->last_name . "</li>";
				}
			}

		}


		// TODO - return signup names
		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'fetch-signups-for-opening-id';
		# inject into DOM
		$results['html_output'] = $signups_list;
		$results['html_render_opening'] = $o->renderAsHtmlShortWithControls();

		//<li signuptime="1424113532" fname="David" lname="Keiser-Clark" id="signee_list_item_for_33305" signup_id="33305" class="signee_list_item"> <img for_opening="82024" for_signup="33305" title="remove signup" alt="remove signup" src="image/pix/t/delete.png" class="remove_signup_link nukeit">David Keiser-Clark
		//		<span class="sus_very_small">(signed up 2015-02-16 02:05 PM)</span><br>
		//    <label for="su_admin_comment_33305">Admin Note:</label><textarea id="su_admin_comment_33305" class="su_admin_comment" name="su_admin_comment_33305">note 1</textarea>  </li>
		//$results['html_output'] = "placeholder name (and signed up date)";
	}
	//###############################################################


	#------------------------------------------------#
	# Helper functions
	#------------------------------------------------#
	function constraintForAccessTypeIsById($type) {
		return ($type == 'byinstr') || ($type == 'bygradyear');
	}

	function doAccessAdd($type, $sheetId, $constraintInfo, &$results) {
		global $DB;
		$access_record = '';
		if (constraintForAccessTypeIsById($type)) {
			$access_record = SUS_Access::createNewAccess($type, $sheetId, $constraintInfo, '', $DB);
		}
		else {
			$access_record = SUS_Access::createNewAccess($type, $sheetId, 0, $constraintInfo, $DB);
		}
		$access_record->updateDb();

		if (!$access_record->matchesDb) {
			$results["notes"] = "could not save that access";
			echo json_encode($results);
			exit;
		}
		$results['status'] = 'success';
	}

	function doAccessRemove($type, $sheetId, $constraintInfo, &$results) {
		global $DB;
		$access_record = '';
		if (constraintForAccessTypeIsById($type)) {
			$access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_id' => $constraintInfo], $DB);
		}
		else {
			$access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_data' => $constraintInfo], $DB);
		}

		if (!$access_record->matchesDb) {
			$results["notes"] = "no matching record found in database";
			echo json_encode($results);
			exit;
		}

		$access_record->doDelete();

		$check_access_record = '';
		if (constraintForAccessTypeIsById($type)) {
			$check_access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_id' => $constraintInfo], $DB);
		}
		else {
			$check_access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_data' => $constraintInfo], $DB);
		}

		# Output
		if ($check_access_record->matchesDb) {
			$results["notes"] = "could not remove that access";
			echo json_encode($results);
			exit;
		}

		$results['status'] = 'success';
	}


	#------------------------------------------------#
	# Debugging output
	#------------------------------------------------#
	//	echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();


	#------------------------------------------------#
	# Return JSON array
	#------------------------------------------------#
	echo json_encode($results);
	exit;

?>