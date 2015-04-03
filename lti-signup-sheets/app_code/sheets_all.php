<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('sheets_all'));
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		// Auto-Click "Edit Sheetgroup Modal" if user arrived at this page by clicking "Edit current group" link from sheets_edit_one.php?sheetgroup=502 page
		if ((isset($_REQUEST['sheetgroup'])) && (is_numeric($_REQUEST['sheetgroup'])) && ($_REQUEST['sheetgroup'] > 0)) { ?>
			<script>
				$(document).ready(function () {
					// this fxn is nearly identical to sheets_all.js listener for ".sus-edit-sheetgroup" clicks
					function openEditSheetgroupModal() {
						// fetch values from DOM
						var sheetgroup_id = $(sheetgrouplink).attr("data-for-sheetgroup-id");
						var sheetgroup_name = $(sheetgrouplink).attr("data-for-sheetgroup-name");
						var sheetgroup_description = $(sheetgrouplink).attr("data-for-sheetgroup-description");
						var sheetgroup_max_total = $(sheetgrouplink).attr("data-for-sheetgroup-max-total");
						var sheetgroup_max_pending = $(sheetgrouplink).attr("data-for-sheetgroup-max-pending");

						// update values in modal
						$("#ajaxSheetgroupLabel").text("Edit Group");
						$("#ajaxSheetgroupAction").val("edit-sheetgroup");
						$("#ajaxSheetgroupID").val(sheetgroup_id);
						$("#ajaxSheetgroupName").val(sheetgroup_name);
						$("#ajaxSheetgroupDescription").val(sheetgroup_description);
						$("#ajaxSheetgroupMaxTotal").val(sheetgroup_max_total);
						$("#ajaxSheetgroupMaxPending").val(sheetgroup_max_pending);
					}

					var sheetgrouplink = $('#btn-edit-sheetgroup-id-<?php echo htmlentities($_REQUEST['sheetgroup'], ENT_QUOTES, 'UTF-8'); ?>');
					openEditSheetgroupModal();
					$(sheetgrouplink).click();
				});
			</script>
		<?php
		}

		echo "<div id=\"content_container\">"; // begin: div#content_container

		// ***************************
		// fetch managed sheets
		// ***************************
		$USER->cacheManagedSheets();
		// util_prePrintR($USER->managed_sheets);

		// display managed sheets
		if (count($USER->managed_sheets) > 0) {
			echo "<table class=\"table table-condensed table-bordered table-hover\">";
			echo "<tr class=\"success\"><th class=\"col-sm-11\">Sheets I manage that are owned by others...</th>";
			// show placeholder icon (disabled)
			echo "<th class=\"col-sm-1 text-right\"><a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete\"><i class=\"glyphicon glyphicon-minus-sign\"></i></a>&nbsp;</th></tr>";
			foreach ($USER->managed_sheets as $mgr_sheet) {
				echo "<tr><td class=\"col-sm-11\">";
				echo "<a href=\"" . APP_ROOT_PATH . "/app_code/sheets_edit_one.php?sheet=" . htmlentities($mgr_sheet->sheet_id, ENT_QUOTES, 'UTF-8') . "\" title=\"Edit sheet\">" . htmlentities($mgr_sheet->name, ENT_QUOTES, 'UTF-8') . "</a>";
				$owner = User::getOneFromDb(['user_id' => $mgr_sheet->owner_user_id], $DB);
				echo " <small>(owned by " . htmlentities($owner->first_name, ENT_QUOTES, 'UTF-8') . " " . htmlentities($owner->last_name, ENT_QUOTES, 'UTF-8') . ")</small>";
				echo "</td><td class=\"col-sm-1 text-right\">";
				// show placeholder icon (disabled)
				echo "<a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete\"><i class=\"glyphicon glyphicon-minus-sign\"></i></a>&nbsp;";
				echo "</td></tr>";
			}
			echo "</table>\n";
		}

		// ***************************
		// fetch sheetgroups
		// ***************************
		$USER->cacheSheetgroups();
		// util_prePrintR($USER->sheetgroups);

		// if the user lacks a default sheet group, create one
		if (!$USER->sheetgroups) // create group since none exists
		{
			// create an object for the insert_record function
			$name        = htmlentities($USER->first_name, ENT_QUOTES, 'UTF-8') . ' ' . htmlentities($USER->last_name, ENT_QUOTES, 'UTF-8') . ' signup-sheets';
			$description = 'Main collection of signup-sheets created by ' . htmlentities($USER->first_name, ENT_QUOTES, 'UTF-8') . ' ' . htmlentities($USER->last_name, ENT_QUOTES, 'UTF-8');
			$sg          = SUS_Sheetgroup::createNewSheetgroupForUser($USER->user_id, $name, $description, $DB);
			$sg->updateDb();

			if (!$sg->matchesDb) {
				// error: default sheet group failed to auto-create properly
				util_displayMessage('error', 'Default sheet group failed to auto-create properly.');
				require_once(dirname(__FILE__) . '/../foot.php');
				exit;
			}

			// fetch sheetgroups (use efficient cache function)
			$USER->cacheSheetgroups();
		}

		// display sheetgroups
		# TODO - add course contextid as param?
		foreach ($USER->sheetgroups as $sheetgroup) {

			// sheetgroup header
			echo "<table class=\"table table-condensed table-bordered table-hover\">";
			echo "<tr class=\"info\"><th class=\"col-sm-11\">";
			echo "<a href=\"#modalSheetgroup\" id=\"btn-edit-sheetgroup-id-" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" class=\"sus-edit-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" data-for-sheetgroup-id=\"" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-name=\"" . htmlentities($sheetgroup->name, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-description=\"" . htmlentities($sheetgroup->description, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-max-total=\"" . htmlentities($sheetgroup->max_g_total_user_signups, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-max-pending=\"" . htmlentities($sheetgroup->max_g_pending_user_signups, ENT_QUOTES, 'UTF-8') . "\" title=\"Edit group\">" . htmlentities($sheetgroup->name, ENT_QUOTES, 'UTF-8') . "</a>";
			echo "</th><th class=\"col-sm-1 text-right\">";
			if (!$sheetgroup->flag_is_default) {
				echo "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-bb=\"alert_callback\" data-for-sheetgroup-id=\"" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" title=\"Delete group and all sheets in it\"><i class=\"glyphicon glyphicon-remove\"></i> Group</a>&nbsp;";
			}
			else {
				// show placeholder icon (disabled)
				echo "<a class=\"btn btn-xs btn-default disabled\" disabled=\"disabled\" title=\"Cannot delete default group\"><i class=\"glyphicon glyphicon-minus-sign\"></i> <span class='small'>Default</span></a>&nbsp;";
			}
			echo "</th></tr>";

			// list sheets
			$sheetgroup->cacheSheets();
			foreach ($sheetgroup->sheets as $sheet) {
				echo "<tr><td class=\"col-sm-11\">";
				echo "<a href=\"" . APP_ROOT_PATH . "/app_code/sheets_edit_one.php?sheet=" . htmlentities($sheet->sheet_id, ENT_QUOTES, 'UTF-8') . "\" id=\"btn-edit-sheet-id-" . htmlentities($sheet->sheet_id, ENT_QUOTES, 'UTF-8') . "\" class=\"sus-edit-sheet\" data-for-sheet-name=\"" . htmlentities($sheet->name, ENT_QUOTES, 'UTF-8') . "\"  title=\"Edit sheet\">" . htmlentities($sheet->name, ENT_QUOTES, 'UTF-8') . "</a>";
				echo "</td><td class=\"col-sm-1 text-right\">";
				echo "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-sheet\" data-bb=\"alert_callback\" data-for-sheetgroup-id=\"" . htmlentities($sheet->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" data-for-sheet-id=\"" . htmlentities($sheet->sheet_id, ENT_QUOTES, 'UTF-8') . "\" title=\"Delete sheet\"><i class=\"glyphicon glyphicon-remove\"></i></a>&nbsp;";
				echo "</td></tr>";
			}

			// add new sheet
			echo "<tr><td class=\"col-sm-12\" colspan=\"2\">";
			echo "<a href=\"" . APP_ROOT_PATH . "/app_code/sheets_edit_one.php?sheetgroup=" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "&sheet=new\" class=\"btn btn-xs btn-success sus-add-sheet\"  title=\"Add new sheet\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new sheet to this group</a>";
			echo "</td></tr>\n";

			// complete sheetgroup
			echo "</table>\n";
		}

		// add new sheetgroup
		echo "<p id=\"container-add-new-group\">\n";
		echo "<a href=\"#modalSheetgroup\" class=\"btn btn-primary sus-add-sheetgroup\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" title=\"Add group\"><i class=\"glyphicon glyphicon-plus\"></i> Add a new group</a>";
		echo "</p>";

		echo "</div>"; // end: div#content_container
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once(dirname(__FILE__) . '/../foot.php');
?>


<!-- Bootstrap Modal: Add/Edit Sheetgroup -->
<form action="<?php echo APP_ROOT_PATH; ?>/ajax_actions/ajax_actions.php" id="frmAjaxSheetgroup" name="frmAjaxSheetgroup" class="form-horizontal" role="form" method="post">
	<input type="hidden" id="ajaxSheetgroupAction" name="ajaxSheetgroupAction" value="" />
	<input type="hidden" id="ajaxOwnerUserID" name="ajaxOwnerUserID" value="<?php echo htmlentities($USER->user_id, ENT_QUOTES, 'UTF-8') ?>" />
	<input type="hidden" id="ajaxSheetgroupID" name="ajaxSheetgroupID" value="" />

	<div id="modalSheetgroup" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajaxSheetgroupLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-info">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 id="ajaxSheetgroupLabel" class="modal-title">Group</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="ajaxSheetgroupName" class="col-sm-2 control-label">Name</label>

						<div class="col-sm-10">
							<input type="text" id="ajaxSheetgroupName" name="ajaxSheetgroupName" class="form-control" maxlength="255" placeholder="Group name" value="" />
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
							<div class="well well-sm"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i> Group settings affect all
								sheets in this group. Sheet settings affect only that sheet.
							</div>
							Users can have at most
							<select id="ajaxSheetgroupMaxTotal" name="ajaxSheetgroupMaxTotal" class="">
								<option value="-1" selected="selected">unlimited</option>
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
								<option value="-1" selected="selected">any</option>
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
					<button type="submit" id="btnAjaxSheetgroupSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save</button>
					<button type="reset" id="btnAjaxSheetgroupCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</form>
<!-- /Modal -->

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/sheets_all.js"></script>
