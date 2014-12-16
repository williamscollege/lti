<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_signups'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div>";
		echo "<h3>Sheet Groups</h3>";
		echo "<p>Sheets are collected into groups. Group settings affect all sheets in the group. Sheet settings affect only that sheet.</p>";

		// fetch sheetgroups (use efficient cache function)
		$USER->cacheSheetgroups();

		// if the user lacks a default sheet group, create one
		if (!$USER->sheetgroups) // create group since none exists
		{
			// create an object for the insert_record function
			$name        = $USER->first_name . ' ' . $USER->last_name . ' signup-sheets';
			$description = 'Main collection of signup-sheets created by ' . $USER->first_name . ' ' . $USER->last_name;
			$sg          = SUS_Sheetgroup::createNewSheetgroupForUser($USER->user_id, $name, $description, $DB);
			$sg->updateDb();

			if (!$sg->matchesDb) {
				error('Initial sheet group creation/insert failed.');
			}

			// fetch sheetgroups (use efficient cache function)
			$USER->cacheSheetgroups();
		}

		# TODO - add course contextid as param
		foreach ($USER->sheetgroups as $sheetgroup) {

			// sheetgroup header
			echo "<table class=\"table table-condensed table-bordered table-hover\">";
			echo "<tr class=\"bg-info\"><th>";
			echo "<a href=\"edit_sheetgroup.php?sheetgroup=" . $sheetgroup->sheetgroup_id . "\">" . $sheetgroup->name . "</a>";
			echo "</th><th class=\"text-right\">";
			if (!$sheetgroup->flag_is_default) {
				// TODO - jquery: confirm dialogue and action: confirm('Really delete this sheetgroup?')
				echo "<a class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-sheetgroup-id=\"" . $sheet->sheetgroup_id . "\" title=\"Delete sheetgroup and all sheets in it\"><i class=\"glyphicon glyphicon-trash icon-white\"></i></a>&nbsp;";
			}
			echo "</th></tr>";

			// list sheets
			$sheetgroup->cacheSheets();
			foreach ($sheetgroup->sheets as $sheet) {
				echo "<tr><td>";
				echo "<a href=\"edit_sheet.php?sheetgroup=" . $sheet->sheetgroup_id . "&sheet=" . $sheet->sheet_id . "\">" . $sheet->name . "</a>";
				echo "</td><td class=\"text-right\">";
				// TODO - jquery: confirm dialogue and action: confirm('Really delete this sheet?')
				echo "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-sheet\" data-sheetgroup-id=\"" . $sheet->sheetgroup_id . "\" data-sheet-id=\"" . $sheet->sheet_id . "\" title=\"Delete sheet\"><i class=\"glyphicon glyphicon-trash icon-white\"></i></a>&nbsp;";
				echo "</td></tr>";
			}

			// add new sheet
			echo "<tr><td colspan=\"2\">";
			echo "<a href=\"add_sheet.php?sheetgroup=" . $sheetgroup->sheetgroup_id . "\" class=\"btn btn-xs btn-success sus-add-sheet\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new sheet to this group</a>";
			echo "</td></tr>\n";

			// complete sheetgroup
			echo "</table>\n";
		}

		// fetch managed sheets
		$USER->cacheManagedSheets();

		// display managed sheets
		if ($USER->managed_sheets) {
			echo "<table class=\"table table-condensed table-bordered table-hover\">";
			echo "<tr><th class=\"bg-danger\">Sheets I manage that are owned by others:</th></tr>";
			foreach ($USER->managed_sheets as $mgr_sheet) {
				echo "<tr><td>";
				echo "<a href=\"edit_sheet.php?sheetgroup=" . $mgr_sheet->sheetgroup_id . "&sheet=" . $mgr_sheet->sheet_id . "\">" . $mgr_sheet->name . "</a>";
				$owner = User::getOneFromDb(['user_id'=>$mgr_sheet->owner_user_id], $DB);
				echo " <small>(owned by " . $owner->first_name . " " . $owner->last_name . ")</small>";
				echo "</td></tr>";
			}
			echo "</table>\n";
		}


		// add new sheetgroup
		echo "<p>\n";
		echo "<a href=\"add_sheetgroup.php\"  class=\"btn btn-primary sus-add-sheetgroup\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new group</a>";
		echo "</p>";

		// end parent div
		echo "</div>";
	}

	require_once('../foot.php');