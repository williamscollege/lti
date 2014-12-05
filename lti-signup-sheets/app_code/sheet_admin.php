<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_signups'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		/* --------------------------- */
		/* START OLD CODE */
		/* --------------------------- */

		?>
		<div class="sus_subcontent">
		<h3>Sheet Groups</h3>

		<p>Sheets are collected into groups. Group settings affect all sheets in the group. Sheet settings affect only that sheet.
		</p>
		<?php
		/*
						if ($action == 'deletegroup') {
							deleteSheetGroup(clean_param($_REQUEST['sheetgroup'], PARAM_CLEAN));
						}
						elseif ($action == 'deletesheet') {
							deleteSheet(clean_param($_REQUEST['sheet'], PARAM_CLEAN));

							// if we came from the sheet group page, head back their after deleting
							if (clean_param($_REQUEST['sheetgroup'], PARAM_CLEAN)) {
								include $action_table['editgroup'];
								exit;
							}
						}
		*/

		// fetch sheetgroups (using the more efficient cache function)
		$USER->cacheSheetgroups();

		# if the user lacks a default sheet group, create one
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

			// fetch sheetgroups (using the more efficient cache function)
			$USER->cacheSheetgroups();
		}

		# display the list
		#   at the top of each group, show action to create a new sheet in that group
		#   at the end of the list (or top of the list?) show action to create new group
		#  !NOTE: clicking on a group takes user to editing page for that group
		#  !NOTE: clicking on a sheet takes user to editing page for that sheet
		#  !NOTE: for each group and sheet, show some (not yet sure what) info besides the name

		$last_sg_id         = 0;
		$last_sg_is_default = 0;
		echo "<ul class=\"sus_sheetgroup_list\">\n";

		// fetch sheetgroups (using the more efficient cache function)
		//$USER->sheetgroups->cacheSheets();
		//util_prePrintR($USER);

		/*TMP NEW STUFF*/
		# TODO - these declarations are adhoc -- needs improvement
		$ss_href  = APP_ROOT_PATH . '/?contextid=123465'; //$contextid = optional_param('contextid', 0, PARAM_INT); // determines what course
		$edit_group_url = "$ss_href&action=editgroup&sheetgroup=";
		$add_group_url  = "$ss_href&action=editgroup&sheetgroup=new";
		$edit_sheet_url = "$ss_href&action=editsheet&sheet=";
		$add_sheet_url  = "$ss_href&action=editsheet&sheet=new&sheetgroup=";

//		util_prePrintR($USER->sheetgroups);

		foreach ($USER->sheetgroups as $sheetgroup) {
			if ($sheetgroup->sheetgroup_id != $last_sg_id) {
				$last_sg_is_default = $sheetgroup->flag_is_default;
				if ($last_sg_id != 0)  // close prior item, if not first pass
				{
					$sheetgroup->cacheSheets();
					foreach ($sheetgroup->sheets as $sheet) {
						// print_r($sheetgroup->sheets);
						echo $sheet->name . "<br />";
					}

					echo "<li class=\"sus_sheet_item sus_new_sheet\" id=\"new_sheet_for_$last_sg_id\"><a href=\"$add_sheet_url$last_sg_id\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new sheet to this group</a></li>\n";
					echo "</ul>\n";
					echo "</li>\n";
				}
				$last_sg_id = $sheetgroup->sheetgroup_id;
				echo "<li class=\"sus_sheetgroup_item\" id=\"sus_sheetgroup_$last_sg_id\">";
				echo "<p class=\"bg-info\"><a href=\"$edit_group_url$last_sg_id\">$sheetgroup->name</a>";
				if (!$last_sg_is_default) {
					echo '  <a href="?sheetgroup=' . $last_sg_id . '&action=deletegroup&contextid=' . $context->id . '" onclick="return confirm(\'Any sheets in this group will be deleted.\n\nReally delete this group?\');" class="nukeit_link"><img src="image/pix/t/delete.png"/ title="delete sheet group" alt="delete sheet group" class="nukeit"></a>';
				}
				echo "</p>\n";

				echo "<ul class=\"sus_sheet_list\">(FIRST:) \n";

				$sheetgroup->cacheSheets();
				foreach ($sheetgroup->sheets as $sheet) {
					// print_r($sheetgroup->sheets);
					echo $sheet->name . "<br />";
				}

			}
			echo "<li class=\"sus_sheet_item sus_new_sheet\" id=\"new_sheet_for_$last_sg_id\"><a href=\"$add_sheet_url$last_sg_id\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new sheet to this group</a></li>\n";
			echo "</ul>\n";
			echo "</li>\n";
		}
			echo "<li class=\"sus_sheetgroup_item sus_new_sheetgroup\" id=\"new_sheetgroup\"><a href=\"$add_group_url\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new group</a></li>";
			echo "</ul><!-- end sus_sheetgroup_list-->\n";

			/*ORIGINAL BELOW*/
			/*
							foreach ($USER->sheetgroups as $sheetgroup) {
								if ($sheetgroup->sheetgroup_id != $last_sg_id) {
									$last_sg_is_default = $sheetgroup->flag_is_default;
									if ($last_sg_id != 0)  // close prior item, if not first pass
									{
										echo "      <li class=\"sus_sheet_item sus_new_sheet\" id=\"new_sheet_for_$last_sg_id\"><a href=\"$add_sheet_url$last_sg_id\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new sheet to this group</a></li>\n";
										echo "    </ul>\n";
										echo "  </li>\n";
									}
									$last_sg_id = $sheetgroup->sheetgroup_id;
									echo "  <li class=\"sus_sheetgroup_item\" id=\"sus_sheetgroup_$last_sg_id\">";
									echo "<h3><a href=\"$edit_group_url$last_sg_id\">$sheetgroup->name</a>";
									if (!$last_sg_is_default) {
										echo '  <a href="?sheetgroup=' . $last_sg_id . '&action=deletegroup&contextid=' . $context->id . '" onclick="return confirm(\'Any sheets in this group will be deleted.\n\nReally delete this group?\');" class="nukeit_link"><img src="image/pix/t/delete.png"/ title="delete sheet group" alt="delete sheet group" class="nukeit"></a>';
									}
									echo "</h3>\n";

									echo "    <ul class=\"sus_sheet_list\">\n";
								}
								if ($sheetgroup->sheetgroup_id) {
									echo "      <li class=\"sus_sheet_item\" id=\"sus_sheet_$sheet->sheetgroup_id\">";
									echo '  <a href="?sheet=' . $sheetgroup->sheetgroup_id . '&action=deletesheet&contextid=' . $context->id . '" onclick="return confirm(\'Really delete this sheet?\');" class="nukeit_link"><img src="image/pix/t/delete.png"/ title="delete sheet" alt="delete sheet" class="nukeit"></a>';
									echo "<a href=\"" . $edit_sheet_url . $sheet->sheetgroup_id . "&sheetgroup=" . $sheetgroup->sheetgroup_id ."\">" . $sheetgroup->name . "</a>";
									echo "</li>\n";
								}
							}
							echo "      <li class=\"sus_sheet_item sus_new_sheet\" id=\"new_sheet_for_$last_sg_id\"><a href=\"$add_sheet_url$last_sg_id\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new sheet to this group</a></li>\n";
							echo "    </ul>\n";
							echo "  </li>\n";

							echo "  <li class=\"sus_sheetgroup_item sus_new_sheetgroup\" id=\"new_sheetgroup\"><a href=\"$add_group_url\"><img src=\"image/pix/t/add.png\" class=\"addit\">Add a new group</a></li>";
							echo "</ul><!-- end sus_sheetgroup_list-->\n";

							// get admin-ed sheets
							// if there are any
							//  list them in a basic, black-bordered white box
							$admin_sheets = getAdminSheetsAndAssociatedInfo();
							if ($admin_sheets) {
								echo "<div class=\"sus_sheetgroup_item sus_admin_sheets\">Sheets I manage that are owned by others:\n";
								echo "  <ul class=\"sus_sheet_list\">\n";
								foreach ($admin_sheets as $as) {
									echo "    <li class=\"sus_sheet_item\" id=\"sus_sheet_$as->sheetgroup_id\">";
									echo "<a href=\"$edit_sheet_url$as->sheetgroup_id&sheetgroup=$as->sheetgroup_id\">$as->name</a>";
									echo " (owned by {$as->usr_firstname} {$as->usr_lastname})";
									echo "</li>\n";
								}
								echo "  </ul>\n</div>\n";
							}
			*/
			?>
			</div>
			<?php
			/* --------------------------- */
			/* END OLD CODE */
			/* --------------------------- */
	}

		require_once('../foot.php');