<?php

	$results['status'] = 'success';
	$results['test'] = 'json test value was returned';
	echo json_encode($results);
	exit;

//	require_once('../classes/eq_group.class.php');
//	require_once('../classes/eq_subgroup.class.php');
//	require_once('../classes/eq_item.class.php');

//	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	$strAction        = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	$intSheetgroupID      = htmlentities((isset($_REQUEST["ajaxVal_SheetgroupID"])) ? $_REQUEST["ajaxVal_SheetgroupID"] : 0);
	$intName       = htmlentities((isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : 0);
	$intDescription    = htmlentities((isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : 0);
	$intMaxTotal         = htmlentities((isset($_REQUEST["ajaxVal_Max_Total"])) ? $_REQUEST["ajaxVal_Max_Total"] : 0);
	$strMaxPending          = htmlentities((isset($_REQUEST["ajaxVal_Max_Pending"])) ? $_REQUEST["ajaxVal_Max_Pending"] : 0);


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
	if ($strAction == 'add-subgroup') {
		$esg = EqSubgroup::getOneFromDb(['name' => $strName], $DB);

		if ($esg->matchesDb) {
			// error: matching record already exists
			echo json_encode($results);
			exit;
		}
		$esg->eq_group_id          = $intGroupID;
		$esg->ordering             = $intOrder;
		$esg->name                 = $strName;
		$esg->descr                = $strDescription;
		$esg->flag_is_multi_select = $bitIsMultiSelect;

		$esg->updateDb();

		$output = EqSubgroup::getOneFromDb(['name' => $strName], $DB);

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'add-subgroup';
		$results['html_output']  = '';

		# Omit class="hide" as this is injected into the DOM
		$results['html_output'] .= "<ul id=\"ul-of-subgroup-" . $output->eq_subgroup_id . "\" class=\"unstyled\">\n";
		$results['html_output'] .= "<a id=\"btn-edit-subgroup-id-" . $output->eq_subgroup_id . "\" href=\"#modalSubgroup\" data-toggle=\"modal\" data-for-subgroup-id=\"" . $output->eq_subgroup_id . "\" data-for-ismultiselect=\"" . $output->flag_is_multi_select . "\" data-for-subgroup-name=\"" . $output->name . "\" data-for-subgroup-descr=\"" . $output->descr . "\" class=\"manager-action btn btn-mini btn-primary eq-edit-subgroup\" title=\"Edit\"><i class=\"icon-pencil icon-white\"></i> </a> ";
		$results['html_output'] .= "<a class=\"manager-action btn btn-mini btn-danger eq-delete-subgroup\" data-for-subgroup-id=\"" . $output->eq_subgroup_id . "\" title=\"Delete\"><i class=\"icon-trash icon-white\"></i> </a> ";
		$results['html_output'] .= "<span id=\"subgroupid-" . $output->eq_subgroup_id . "\" data-for-subgroup-order=\"" . $output->ordering . "\"><strong>" . $output->name . ": </strong>" . $output->descr . "</span>\n";
		$results['html_output'] .= "<li class=\"manager-action\">";
		$results['html_output'] .= "<span class=\"noItemsExist\"><em>No items exist.</em><br /></span>";
		$results['html_output'] .= "<a href=\"#modalItem\" data-toggle=\"modal\" data-for-subgroup-id=\"" . $output->eq_subgroup_id . "\" data-for-ismultiselect=\"" . $bitIsMultiSelect . "\" data-for-subgroup-name=\"" . $output->name . "\" class=\"btn btn-success btn-mini eq-add-item\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
		$results['html_output'] .= "</li>";
		$results['html_output'] .= "</ul>";
	}
	//###############################################################
	elseif ($strAction == 'edit-subgroup') {
		$esg = EqSubgroup::getOneFromDb(['eq_subgroup_id' => $intSubgroupID], $DB);

		if (!$esg->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}
		$esg->name                 = $strName;
		$esg->descr                = $strDescription;
		$esg->flag_is_multi_select = $bitIsMultiSelect;

		$esg->updateDb();

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-subgroup';
		$results['html_output']  = '';
	}
	//###############################################################
	elseif ($strAction == 'delete-subgroup') {
		$esg = EqSubgroup::getOneFromDb(['eq_subgroup_id' => $intDeleteID], $DB);

		if (!$esg->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}

		# Get equipment items (for subsequent removal)
		$esg->loadEqItems();

		# Remove subgroup items
		foreach ($esg->eq_items as $ei) {
			$ei->flag_delete = TRUE;
			$ei->updateDb();
		}

		# Remove subgroup
		$esg->flag_delete = TRUE;
		$esg->updateDb();

		# Output
		if ($esg->matchesDb) {
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