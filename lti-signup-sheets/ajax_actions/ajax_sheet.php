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
	$action       = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	$ownerUserID  = htmlentities((isset($_REQUEST["ajaxVal_OwnerUserID"])) ? $_REQUEST["ajaxVal_OwnerUserID"] : 0);
	$sheetgroupID = htmlentities((isset($_REQUEST["ajaxVal_SheetgroupID"])) ? $_REQUEST["ajaxVal_SheetgroupID"] : 0);
	$sheetID = htmlentities((isset($_REQUEST["ajaxVal_SheetID"])) ? $_REQUEST["ajaxVal_SheetID"] : 0);
	$name         = htmlentities((isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : 0);
	$description  = htmlentities((isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : 0);
	$maxTotal     = htmlentities((isset($_REQUEST["ajaxVal_MaxTotal"])) ? $_REQUEST["ajaxVal_MaxTotal"] : 0);
	$maxPending   = htmlentities((isset($_REQUEST["ajaxVal_MaxPending"])) ? $_REQUEST["ajaxVal_MaxPending"] : 0);
	$deleteID     = htmlentities((isset($_REQUEST["ajaxVal_Delete_ID"])) ? $_REQUEST["ajaxVal_Delete_ID"] : 0);

	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];

# TODO Search for 'sheetgroup' and update where approprate to 'sheet'
	#------------------------------------------------#
	# Identify and process requested action
	#------------------------------------------------#
	//###############################################################
	if ($action == 'add-sheet') {
		$s = SUS_Sheet::getOneFromDb(['name' => $name], $DB);

		if ($s->matchesDb) {
			// error: matching record already exists
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
		$results['html_output'] .= "<a href=\"add_sheet.php?sheetgroup=" . $sheetgroup->sheetgroup_id . "\" class=\"btn btn-xs btn-success sus-add-sheet\" title=\"Add new sheet\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new sheet to this group</a>";
		$results['html_output'] .= "</td></tr>";
		$results['html_output'] .= "</tbody></table>";
	}
	//###############################################################
	elseif ($action == 'edit-sheetgroup') {
		$s = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $sheetgroupID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}
		$s->name                       = $name;
		$s->description                = $description;
		$s->max_g_total_user_signups   = $maxTotal;
		$s->max_g_pending_user_signups = $maxPending;
		$s->updated_at                 = date("Y-m-d H:i:s");

		$s->updateDb();

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-sheetgroup';
		$results['html_output']  = '';
	}
	//###############################################################
	elseif ($action == 'delete-sheet') {
		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $deleteID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}

		# Get any sheets belonging to this sheetgroup(for subsequent removal)
		$s->loadSheets();

		# TODO - NEED TO REMOVE OPENINGS and access?
		# TODO - implement doDelete() to cascade deletes

		# Remove sheetgroup
		$s->flag_delete = TRUE;
		$s->updateDb();

		# Output
		if ($s->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################


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