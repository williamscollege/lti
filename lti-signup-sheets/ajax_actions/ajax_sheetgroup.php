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
	$sheetgroupID = htmlentities((isset($_REQUEST["ajaxVal_SheetgroupID"])) ? $_REQUEST["ajaxVal_SheetgroupID"] : 0);
	$name         = htmlentities((isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : 0);
	$description  = htmlentities((isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : 0);
	$maxTotal     = htmlentities((isset($_REQUEST["ajaxVal_Max_Total"])) ? $_REQUEST["ajaxVal_Max_Total"] : 0);
	$maxPending   = htmlentities((isset($_REQUEST["ajaxVal_Max_Pending"])) ? $_REQUEST["ajaxVal_Max_Pending"] : 0);


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
		$sg = SUS_Sheetgroup::getOneFromDb(['name' => $name], $DB);

		if ($sg->matchesDb) {
			// error: matching record already exists
			echo json_encode($results);
			exit;
		}
		$sg->name                       = $name;
		$sg->description                = $description;
		$sg->max_g_total_user_signups   = $maxTotal;
		$sg->max_g_pending_user_signups = $maxPending;

		$sg->updateDb();

		$sheetgroup = SUS_Sheetgroup::getOneFromDb(['name' => $name], $DB);

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'add-sheetgroup';
		$results['html_output']  = '';

		# This is injected into the DOM
		// TODO FINISH THIS HTML

//START NEW
		// sheetgroup header
		$results['html_output'] .= "<table class=\"table table-condensed table-bordered table-hover\">";
		$results['html_output'] .= "<tr class=\"bg-info\"><th class=\"col-sm-11\">";
		$results['html_output'] .= "<a href=\"#modalSheetgroup\" class=\"sus-edit-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" data-for-sheetgroup-name=\"" . $sheetgroup->name . "\" data-for-sheetgroup-description=\"" . $sheetgroup->description . "\" data-for-sheetgroup-max-total=\"" . $sheetgroup->max_g_total_user_signups . "\" data-for-sheetgroup-max-pending=\"" . $sheetgroup->max_g_pending_user_signups . "\" title=\"Edit group\">" . $sheetgroup->name . "</a>";
		$results['html_output'] .= "</th><th class=\"col-sm-1 text-right\">";
		if (!$sheetgroup->flag_is_default) {
			// TODO - jquery: confirm dialogue and action: confirm('Really delete this sheetgroup?')
			$results['html_output'] .= "<a class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" data-for-sheetgroup-name=\"" . $sheetgroup->name . "\" title=\"Delete group and all sheets in it\"><i class=\"glyphicon glyphicon-trash\"></i> Group</a>&nbsp;";
		} else {
			// show placeholder icon (disabled)
			$results['html_output'] .= "<a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete default group\"><i class=\"glyphicon glyphicon-minus-sign\"></i> Default</a>&nbsp;";
		}
		$results['html_output'] .= "</th></tr>";

		// list sheets
		$sheetgroup->cacheSheets();
		foreach ($sheetgroup->sheets as $sheet) {
			$results['html_output'] .= "<tr><td class=\"col-sm-11\">";
			$results['html_output'] .= "<a href=\"edit_sheet.php?sheetgroup=" . $sheet->sheetgroup_id . "&sheet=" . $sheet->sheet_id . "\" class=\"sus-edit-sheet\" title=\"Edit sheet\">" . $sheet->name . "</a>";
			$results['html_output'] .= "</td><td class=\"col-sm-1 text-right\">";
			// TODO - jquery: confirm dialogue and action: confirm('Really delete this sheet?')
			$results['html_output'] .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-sheet\" data-for-sheetgroup-id=\"" . $sheet->sheetgroup_id . "\" data-for-sheet-id=\"" . $sheet->sheet_id . "\" title=\"Delete sheet\"><i class=\"glyphicon glyphicon-trash\"></i></a>&nbsp;";
			$results['html_output'] .= "</td></tr>";
		}
//END NEW
//		$results['html_output'] .= "<ul id=\"ul-of-sheetgroup-" . $output->sheetgroup_id . "\" class=\"unstyled\">\n";
//		$results['html_output'] .= "<a id=\"btn-edit-sheetgroup-id-" . $output->sheetgroup_id . "\" href=\"#modalSheetgroup\" data-toggle=\"modal\" data-for-sheetgroup-id=\"" . $output->sheetgroup_id . "\" data-for-ismultiselect=\"" . $output->flag_is_multi_select . "\" data-for-sheetgroup-name=\"" . $output->name . "\" data-for-sheetgroup-description=\"" . $output->description . "\" class=\"manager-action btn btn-mini btn-primary eq-edit-sheetgroup\" title=\"Edit\"><i class=\"icon-pencil icon-white\"></i> </a> ";
//		$results['html_output'] .= "<a class=\"manager-action btn btn-mini btn-danger eq-delete-sheetgroup\" data-for-sheetgroup-id=\"" . $output->sheetgroup_id . "\" title=\"Delete\"><i class=\"icon-trash icon-white\"></i> </a> ";
//		$results['html_output'] .= "<span id=\"sheetgroupid-" . $output->sheetgroup_id . "\" data-for-sheetgroup-order=\"" . $output->ordering . "\"><strong>" . $output->name . ": </strong>" . $output->description . "</span>\n";
//		$results['html_output'] .= "<li class=\"manager-action\">";
//		$results['html_output'] .= "<span class=\"noItemsExist\"><em>No items exist.</em><br /></span>";
//		$results['html_output'] .= "<a href=\"#modalItem\" data-toggle=\"modal\" data-for-sheetgroup-id=\"" . $output->sheetgroup_id . "\" data-for-ismultiselect=\"" . $bitIsMultiSelect . "\" data-for-sheetgroup-name=\"" . $output->name . "\" class=\"btn btn-success btn-mini eq-add-item\" title=\"Add an item to this sheetgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
//		$results['html_output'] .= "</li>";
//		$results['html_output'] .= "</ul>";
	}
	//###############################################################
	elseif ($action == 'edit-sheetgroup') {
		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $sheetgroupID], $DB);

		if (!$sg->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}
		$sg->name                 = $name;
		$sg->description          = $description;
		$sg->max_g_total_user_signups   = $maxTotal;
		$sg->max_g_pending_user_signups = $maxPending;

		$sg->updateDb();

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-sheetgroup';
		$results['html_output']  = '';
	}
	//###############################################################
	elseif ($action == 'delete-sheetgroup') {
		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $sheetgroupID], $DB);

		if (!$sg->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}

		# Get equipment items (for sheetsequent removal)
		$sg->loadSheets();

		# Remove sheetgroup items
		foreach ($sg->sheets as $s) {
			$s->flag_deleted = TRUE;
			$s->updateDb();
		}

		# Remove sheetgroup
		$sg->flag_deleted = TRUE;
		$sg->updateDb();

		# Output
		if ($sg->matchesDb) {
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