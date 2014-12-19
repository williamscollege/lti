<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_signups'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div id='DKCTEST'>nothing yet</div>"; // debugging TODO REMOVE
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
			echo "<tr class=\"bg-info\"><th class=\"col-sm-11\">";
			echo "<a href=\"#modalSheetgroup\" class=\"sus-edit-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" data-for-sheetgroup-name=\"" . $sheetgroup->name . "\" data-for-sheetgroup-description=\"" . $sheetgroup->description . "\" data-for-sheetgroup-max-total=\"" . $sheetgroup->max_g_total_user_signups . "\" data-for-sheetgroup-max-pending=\"" . $sheetgroup->max_g_pending_user_signups . "\" title=\"Edit group\">" . $sheetgroup->name . "</a>";
			echo "</th><th class=\"col-sm-1 text-right\">";
			if (!$sheetgroup->flag_is_default) {
				// TODO - jquery: confirm dialogue and action: confirm('Really delete this sheetgroup?')
				echo "<a class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-for-sheetgroup-id=\"" . $sheetgroup->sheetgroup_id . "\" data-for-sheetgroup-name=\"" . $sheetgroup->name . "\" title=\"Delete group and all sheets in it\"><i class=\"glyphicon glyphicon-trash\"></i> Group</a>&nbsp;";
			} else {
				// show placeholder icon (disabled)
				echo "<a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete default group\"><i class=\"glyphicon glyphicon-minus-sign\"></i> Default</a>&nbsp;";
			}
			echo "</th></tr>";

			// list sheets
			$sheetgroup->cacheSheets();
			foreach ($sheetgroup->sheets as $sheet) {
				echo "<tr><td class=\"col-sm-11\">";
				echo "<a href=\"edit_sheet.php?sheetgroup=" . $sheet->sheetgroup_id . "&sheet=" . $sheet->sheet_id . "\" class=\"sus-edit-sheet\" title=\"Edit sheet\">" . $sheet->name . "</a>";
				echo "</td><td class=\"col-sm-1 text-right\">";
					// TODO - jquery: confirm dialogue and action: confirm('Really delete this sheet?')
					echo "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-sheet\" data-for-sheetgroup-id=\"" . $sheet->sheetgroup_id . "\" data-for-sheet-id=\"" . $sheet->sheet_id . "\" title=\"Delete sheet\"><i class=\"glyphicon glyphicon-trash\"></i></a>&nbsp;";
				echo "</td></tr>";
			}

			// add new sheet
			echo "<tr><td class=\"col-sm-12\" colspan=\"2\">";
			echo "<a href=\"add_sheet.php?sheetgroup=" . $sheetgroup->sheetgroup_id . "\" class=\"btn btn-xs btn-success sus-add-sheet\"  title=\"Add new sheet\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new sheet to this group</a>";
			echo "</td></tr>\n";

			// complete sheetgroup
			echo "</table>\n";
		}

		// fetch managed sheets
		$USER->cacheManagedSheets();

		// display managed sheets
		if ($USER->managed_sheets) {
			echo "<table class=\"table table-condensed table-bordered table-hover\">";
			echo "<tr class=\"bg-danger\"><th class=\"col-sm-11\">Sheets I manage that are owned by others:</th>";
			// show placeholder icon (disabled)
			echo "<th class=\"col-sm-1 text-right\"><a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete\"><i class=\"glyphicon glyphicon-minus-sign\"></i></a>&nbsp;</th></tr>";
			foreach ($USER->managed_sheets as $mgr_sheet) {
				echo "<tr><td class=\"col-sm-11\">";
				echo "<a href=\"edit_sheet.php?sheetgroup=" . $mgr_sheet->sheetgroup_id . "&sheet=" . $mgr_sheet->sheet_id . "\"  title=\"Edit sheet\">" . $mgr_sheet->name . "</a>";
				$owner = User::getOneFromDb(['user_id' => $mgr_sheet->owner_user_id], $DB);
				echo " <small>(owned by " . $owner->first_name . " " . $owner->last_name . ")</small>";
				echo "</td><td class=\"col-sm-1 text-right\">";
				// show placeholder icon (disabled)
				echo "<a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete\"><i class=\"glyphicon glyphicon-minus-sign\"></i></a>&nbsp;";
				echo "</td></tr>";
			}
			echo "</table>\n";
		}

		// add new sheetgroup
		echo "<p>\n";
		echo "<a href=\"#modalSheetgroup\" class=\"btn btn-primary sus-add-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" title=\"Add group\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new group</a>";
		echo "</p>";

		// end parent div
		echo "</div>";
	}

	require_once('../foot.php');
?>


<!-- Modal: Add/Edit Sheetgroup -->
<form action="../ajax_actions/ajax_sheetgroup.php" id="frmAjaxSheetgroup" name="frmAjaxSheetgroup" class="form-horizontal" role="form" method="post">
	<input type="hidden" id="ajaxSheetgroupAction" name="ajaxSheetgroupAction" value="" />
	<input type="hidden" id="ajaxSheetgroupID" name="ajaxSheetgroupID" value="" />
	<input type="hidden" id="ajaxSheetgroupFlagIsDefault" name="ajaxSheetgroupFlagIsDefault" value="" />

	<div id="modalSheetgroup" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajaxSheetgroupLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-info">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 id="ajaxSheetgroupLabel" class="modal-title">TBD</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="ajaxSheetgroupName" class="col-sm-2 control-label">Name</label>

						<div class="col-sm-10">
							<input type="text" id="ajaxSheetgroupName" name="ajaxSheetgroupName" class="form-control" placeholder="Group name" value="" />
						</div>
					</div>
					<div class="form-group">
						<label for="ajaxSheetgroupDescription" class="col-sm-2 control-label">Description</label>

						<div class="col-sm-10">
							<textarea id="ajaxSheetgroupDescription" name="ajaxSheetgroupDescription" class="form-control" placeholder="Group description" rows="3"></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							Users can have at most
							<select id="ajaxSheetgroupMaxTotal" name="ajaxSheetgroupMaxTotal" class="">
								<option selected="selected" value="0">unlimited</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
							</select>
							signups across all sheets in this group, and
							<select id="ajaxSheetgroupMaxPending" name="ajaxSheetgroupMaxPending" class="">
								<option selected="selected" value="0">any</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
							</select>
							may be for future openings.
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="btnAjaxSheetgroupSubmit" class="btn btn-success" data-loading-text="Saving...">Save</button>
					<button type="reset" id="btnAjaxSheetgroupCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</form>
<!-- /Modal -->

<script type="text/javascript" src="../js/my_sheets.js"></script>
