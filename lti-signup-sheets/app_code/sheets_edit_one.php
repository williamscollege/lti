<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');

	$sheetIsNew          = FALSE;
	$sheetIsDataIncoming = TRUE;
	if ((isset($_REQUEST["sheet"])) && ($_REQUEST["sheet"] == "new")) {
		$pageTitle           = ucfirst(util_lang('add_sheet'));
		$sheetIsNew          = TRUE;
		$sheetIsDataIncoming = FALSE;
	}
	else {
		$pageTitle = ucfirst(util_lang('sheets_edit_one'));
		if ((isset($_REQUEST["hiddenAction"])) && ($_REQUEST["hiddenAction"] == "savesheet")) {
			$sheetIsDataIncoming = TRUE;
		}
		else {
			$sheetIsDataIncoming = FALSE;
		}
	}
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		#------------------------------------------------#
		# begin security: check if access allowed to this page
		#------------------------------------------------#
		if (!$sheetIsNew) {
			// this is not a 'new' sheet
			if (!$sheetIsDataIncoming) {
				// this is not a 'postback'
				if ((!isset($_REQUEST["sheet"])) || (!is_numeric($_REQUEST["sheet"]))) {
					// error: querystring 'sheet' must exist and be an integer
					util_displayMessage('error', 'Invalid or missing sheet request');
					require_once(dirname(__FILE__) . '/../foot.php');
					exit;
				}
				elseif (!$USER->isUserAllowedToManageSheet($_REQUEST["sheet"])) {
					// error: must have access to manage this sheet
					util_displayMessage('error', 'You do not have permission to edit this sheet');
					require_once(dirname(__FILE__) . '/../foot.php');
					exit;
				}
			}
		}

		// load calendar setup functions
		require_once(dirname(__FILE__) . '/calendar_setup.php');


		$s = FALSE;

		if ($sheetIsDataIncoming) {
			// purpose: POSTBACK
			// 1) postback for brand new sheet (record not yet in db)
			// 2) postback for edited sheet (record exists in db)

			if (isset($_REQUEST["sheet"]) && is_numeric($_REQUEST["sheet"])) {
				// populate fields based on DB record
				$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);

				if (!$s->matchesDb) {
					// error
					util_displayMessage('error', 'Failed to create or retrieve sheet.');
					require_once(dirname(__FILE__) . '/../foot.php');
					exit;
				}

				// save for subsequent event log
				$evt_action = "sheets_edit_one";
				$evt_note   = "successfully edited sheet";
			}
			else {
				// create new sheet
				$s                = SUS_Sheet::createNewSheet($USER->user_id, $DB);
				$s->owner_user_id = $USER->user_id; // only set owner for brand new sheet (avoid overwritting the owner_user_id upon edit by another admin)

				// save for subsequent event log
				$evt_action = "createNewSheet";
				$evt_note   = "successfully created sheet";
			}

			// util_prePrintR($s); // debugging

			/* TODO - Low priority issue: Not a present concern about submitting invalid date format (i.e. 99/08/9999).
			 * While it's possible, there are enough client side HTML and JS measures in place to provide reasonable help to users,
			 * plus an invalid format will only create an ugly error message w/o updating the DB. So, until it becomes necessary, this issue is tabled. */

			$s->updated_at               = date("Y-m-d H:i:s");
			$s->sheetgroup_id            = $_REQUEST["selectSheetgroupID"];
			$s->name                     = $_REQUEST["inputSheetName"];
			$s->description              = $_REQUEST["textSheetDescription"];
			$s->type                     = "timeblocks"; // hardcode this data as possible hook for future use/modification
			$s->begin_date               = date_format(new DateTime($_REQUEST["inputSheetDateBegin"] . " 00:00:00"), "Y-m-d H:i:s");
			$s->end_date                 = date_format(new DateTime($_REQUEST["inputSheetDateEnd"] . " 23:59:59"), "Y-m-d H:i:s");
			$s->max_total_user_signups   = $_REQUEST["selectMaxTotalSignups"];
			$s->max_pending_user_signups = $_REQUEST["selectMaxPendingSignups"];
			//$s->flag_alert_owner_change   = $_REQUEST[""];
			$s->flag_alert_owner_signup   = util_getValueForCheckboxRequestData('checkAlertOwnerSignup');
			$s->flag_alert_owner_imminent = util_getValueForCheckboxRequestData('checkAlertOwnerImminent');
			//$s->flag_alert_admin_change   = $_REQUEST[""];
			$s->flag_alert_admin_signup   = util_getValueForCheckboxRequestData('checkAlertAdminSignup');
			$s->flag_alert_admin_imminent = util_getValueForCheckboxRequestData('checkAlertAdminImminent');

			// validation
			if ($s->begin_date > $s->end_date) {
				// error
				util_displayMessage('error', 'The "Date Span" expects a "from" date less than or equal to the "to" date. Please correct and re-submit.');
			}
			else {
				$s->updateDb();

				if (!$s->matchesDb) {
					// error
					util_displayMessage('error', 'Failed to update sheet.');
					require_once(dirname(__FILE__) . '/../foot.php');

					// create event log. [requires: user_id(int), flag_success(bool), event_action(varchar), event_action_id(int), event_action_target_type(varchar), event_note(varchar), event_dataset(varchar)]
					$evt_action = "sheets_edit_one";
					$evt_note   = "failed to edit sheet";
					util_createEventLog($USER->user_id, FALSE, $evt_action, $s->sheet_id, "sheet_id", $evt_note, print_r(json_encode($_REQUEST), TRUE), $DB);
					exit;
				}

				// create event log. [requires: user_id(int), flag_success(bool), event_action(varchar), event_action_id(int), event_action_target_type(varchar), event_note(varchar), event_dataset(varchar)]
				util_createEventLog($USER->user_id, TRUE, $evt_action, $s->sheet_id, "sheet_id", $evt_note, print_r(json_encode($_REQUEST), TRUE), $DB);
				?>
				<script>
					$(document).ready(function () {
						// display screen message
						susUtil_setTransientAlert('success', 'Saved.');
					});
				</script>
				<?php
			}
		}
		elseif (isset($_REQUEST["sheet"]) && (is_numeric($_REQUEST["sheet"]))) {
			// purpose: requested to edit existing sheet from link on another page (record exists in db)
			$sheetIsDataIncoming = TRUE;
			$s                   = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
		}
		else {
			// purpose: create a new sheet from scratch; create a new sheet object simply so errors do not occur
			$s = SUS_Sheet::createNewSheet($USER->user_id, $DB);
		}

		echo "<div id=\"content_container\">"; // begin: div#content_container


		// ***************************
		// fetch all available sheets
		// ***************************
		// fetch managed sheets
		$USER->cacheManagedSheets();
		// util_prePrintR($USER->managed_sheets);

		// fetch sheetgroups
		$USER->cacheSheetgroups();


		// ***************************
		// breadcrumbs: begin
		// ***************************
		$available_sheets = "<select id=\"breadcrumbs_select_list\" class=\"input-sm\">";

		// iterate: managed sheets
		if (isset($USER->managed_sheets) AND count($USER->managed_sheets) >= 1) {
			// optgroup: display group of managed sheets
			$available_sheets .= "<optgroup label=\"Sheets I manage that are owned by others...\">";
			// now, iterate the managed sheets
			foreach ($USER->managed_sheets as $managed_sheet) {
				// option: list each sheet
				$str_selected = ""; // is user editing this sheet?
				if ($managed_sheet->sheet_id == $s->sheet_id) {
					$str_selected = " selected=\"selected\" ";
				}
				// option: list each sheet
				$available_sheets .= "<option value=\"" . $managed_sheet->sheet_id . "\"" . $str_selected . ">" . htmlentities($managed_sheet->name, ENT_QUOTES, 'UTF-8') . "</option>";
			}
			$available_sheets .= "</optgroup>";
		}

		// iterate: sheetgroups
		foreach ($USER->sheetgroups as $sheetgroup) {
			// optgroup: display sheetgroup
			$available_sheets .= "<optgroup label=\"" . htmlentities($sheetgroup->name, ENT_QUOTES, 'UTF-8') . "\">";

			// option: list each sheet
			$sheetgroup->cacheSheets();
			$flag_new_sheet = FALSE;
			foreach ($sheetgroup->sheets as $sheet) {
				// option: is new sheet?
				if (!$flag_new_sheet) {
					if (isset($_REQUEST["sheetgroup"]) AND $sheet->sheetgroup_id == $_REQUEST["sheetgroup"]) {
						if ($s->name == '') {
							$available_sheets .= "<option value=\"\" selected=\"selected\">New Sheet</option>";
							$flag_new_sheet = TRUE;
						}
					}
				}
				$str_selected = ""; // is user editing this sheet?
				if ($sheet->sheet_id == $s->sheet_id) {
					$str_selected = " selected=\"selected\" ";
				}
				// option: list each sheet
				$available_sheets .= "<option value=\"" . $sheet->sheet_id . "\"" . $str_selected . ">" . htmlentities($sheet->name, ENT_QUOTES, 'UTF-8') . "</option>";
			}
			$available_sheets .= "</optgroup>";
		}
		$available_sheets .= "</select>";
		echo "<h5 class=\"small\"><a href=\"" . APP_ROOT_PATH . "/app_code/sheets_all.php\" title=\"" . ucfirst(util_lang('sheets_all')) . "\">" . ucfirst(util_lang('sheets_all')) . "</a>&nbsp;&gt;&nbsp;" . $available_sheets . "</h5>";
		// breadcrumbs: end
		?>

		<div class="container">
			<?php
				// display error message, if exists
				if (isset($_REQUEST['conflicts'])) {
					echo '<p>&nbsp;</p>';
					echo '<div class="alert alert-danger alert-dismissible" role="alert">';
					echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
					echo $_REQUEST['conflicts'];
					echo '</div>';
				}
			?>
			<div class="row">
				<!-- Begin: Basic Sheet Info / Sheet Access -->
				<div class="col-sm-5">
					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<ul id="boxSheetHeader" class="nav nav-tabs" role="tablist">
								<!--DKC IMPORTANT (normal): set class to: 'active'-->
								<!--DKC IMPORTANT (testing): set class to: ''-->
								<li role="presentation" class="active">
									<a href="#tabSheetInfo" role="tab" data-toggle="tab" aria-controls="tabSheetInfo" aria-expanded="false">Basic Sheet Info</a>
								</li>
								<?php
									// for a new sheet: hide advanced settings
									if (!$sheetIsNew) {
										?>
										<!--DKC IMPORTANT (normal): set class to: ''-->
										<!--DKC IMPORTANT (testing): set class to: 'active'-->
										<li role="presentation" class="">
											<!-- show spinner icon (visual placeholder) until DOM content (hidden) has fully loaded -->
											<span id="spinner_tabSheetAccess"><img height="39" width="36" src="../img/spinner.gif" />&nbsp;Sheet Access</span>
											<a href="#tabSheetAccess" id="anchor_tabSheetAccess" class="hidden" role="tab" data-toggle="tab" aria-controls="tabSheetAccess" aria-expanded="false">Sheet
												Access</a>
										</li>
										<?php
									}
								?>
							</ul>
							<div id="boxSheetContent" class="tab-content">
								<!-- Begin: Basic Sheet Info -->
								<!--DKC IMPORTANT (normal): set class to: 'tab-pane fade active in'-->
								<!--DKC IMPORTANT (testing): set class to: 'tab-pane fade'-->
								<div role="tabpanel" id="tabSheetInfo" class="tab-pane fade active in" aria-labelledby="tabSheetInfo">
									<form action="<?php echo APP_ROOT_PATH; ?>/app_code/sheets_edit_one.php" id="frmEditSheet" name="frmEditSheet" class="form-group" role="form" method="post">
										<input type="hidden" id="hiddenSheetID" name="sheet" value="<?php echo $s ? htmlentities($s->sheet_id, ENT_QUOTES, 'UTF-8') : ''; ?>">
										<input type="hidden" id="hiddenAction" name="hiddenAction" value="savesheet">

										<div class="form-group">
											<label for="inputSheetName" class="control-label">Sheet Name</label>

											<div class="">
												<input type="text" id="inputSheetName" name="inputSheetName" class="form-control input-sm" maxlength="255" placeholder="Sheet name" value="<?php echo $s ? htmlentities($s->name, ENT_QUOTES, 'UTF-8') : ''; ?>" />
											</div>
										</div>

										<div class="form-group">
											<label for="selectSheetgroupID" class="control-label">In Group</label>

											<div class="">
												<select id="selectSheetgroupID" name="selectSheetgroupID" class="form-control input-sm">
													<?php
														foreach ($USER->sheetgroups as $sg) {
															$optionSelected = "";
															// comparison using whichever value exists (hyperlink querystring reference or sheet object)
															if (($_REQUEST["sheetgroup"] == $sg->sheetgroup_id) || ($s->sheetgroup_id == $sg->sheetgroup_id)) {
																$optionSelected        = " selected=\"selected\" ";
																$currentSheetgroupID   = $sg->sheetgroup_id;
																$currentSheetgroupName = $sg->name;
																$currentSheetgroupDesc = $sg->description;
															}
															echo "<option" . $optionSelected . " value=\"" . htmlentities($sg->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\">" . htmlentities($sg->name, ENT_QUOTES, 'UTF-8') . "</option>";
														}
													?>
												</select>

												<span class="small"><a href="<?php echo APP_ROOT_PATH; ?>/app_code/sheets_all.php?sheetgroup=<?php echo htmlentities($currentSheetgroupID, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlentities($currentSheetgroupName, ENT_QUOTES, 'UTF-8') . " (&quot;" . htmlentities($currentSheetgroupDesc, ENT_QUOTES, 'UTF-8') . "&quot;)"; ?>">Go
														to current group</a></span>
											</div>
										</div>

										<div class="form-group">
											<label for="textSheetDescription" class="control-label">Description</label>

											<div class="">
												<textarea id="textSheetDescription" name="textSheetDescription" class="form-control input-sm" placeholder="Instructions for this signup sheet" rows="1"><?php echo $s ? htmlentities($s->description, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
											</div>
										</div>

										<div class="form-group">
											<label for="inputSheetDateBegin" class="control-label">Date Span: Active from</label>

											<div class="form-inline">
												<input type="text" id="inputSheetDateBegin" name="inputSheetDateBegin" class="form-control input-sm wms-custom-datepicker" readonly maxlength="10" placeholder="mm/dd/yyyy" value="<?php echo $s ? date_format(new DateTime($s->begin_date), "m/d/Y") : ''; ?>" />
												<strong>to</strong>
												<input type="text" id="inputSheetDateEnd" name="inputSheetDateEnd" class="form-control input-sm wms-custom-datepicker" readonly maxlength="10" placeholder="mm/dd/yyyy" value="<?php echo $s ? date_format(new DateTime($s->end_date), "m/d/Y") : ''; ?>" />
											</div>
										</div>

										<div class="form-group">
											<label for="selectMaxTotalSignups" class="control-label">Maximum Signups</label>

											<div class="form-inline small">
												Users can have
												<select id="selectMaxTotalSignups" name="selectMaxTotalSignups" class="form-control input-sm">
													<?php

														function getPreselectedStateHtml($srcObj, $fieldName, $curVal) {
															return ($srcObj && ($srcObj->{$fieldName} == $curVal) ? ' selected="selected"' : '');
														}

														foreach ([-1, 1, 2, 3, 4, 5, 6, 7, 8] as $optval) {
															$optdisplay = $optval;
															if ($optval < 0) {
																$optdisplay = 'unlimited';
															}
															echo "<option value=\"$optval\"" . getPreselectedStateHtml($s, 'max_total_user_signups', $optval) . ">$optdisplay</option>\n";
														}
													?>
												</select>
												signups on this sheet, and
												<select id="selectMaxPendingSignups" name="selectMaxPendingSignups" class="form-control input-sm"><?php
														foreach ([-1, 1, 2, 3, 4, 5, 6, 7, 8] as $optval) {
															$optdisplay = $optval;
															if ($optval < 0) {
																$optdisplay = 'unlimited';
															}
															echo "<option value=\"$optval\"" . getPreselectedStateHtml($s, 'max_pending_user_signups', $optval) . ">$optdisplay</option>\n";
														}
													?>
												</select>
												may be for future openings.
											</div>
										</div>

										<div class="form-group">
											<strong>Event Actions</strong><br />

											<div class="checkbox small col-sm-12">
												<label>
													<input type="checkbox" id="checkAlertOwnerSignup" name="checkAlertOwnerSignup"<?php echo ($s && $s->flag_alert_owner_signup) ? ' checked="checked"' : ''; ?>>
													Alert <strong>owner</strong> on each signup or cancel
												</label><br />
												<label>
													<input type="checkbox" id="checkAlertAdminSignup" name="checkAlertAdminSignup"<?php echo ($s && $s->flag_alert_admin_signup) ? ' checked="checked"' : ''; ?>>
													Alert <strong>admins</strong> on each signup or cancel
												</label>
											</div>
										</div>

										<div class="form-group">
											<strong>Daily Reminders</strong><br />

											<div class="checkbox small col-sm-12">
												<label>
													<input type="checkbox" id="checkAlertOwnerImminent" name="checkAlertOwnerImminent"<?php echo ($s && $s->flag_alert_owner_imminent) ? ' checked="checked"' : ''; ?>>
													Alert <strong>owner</strong> if signups exist in next 2 days
												</label><br />
												<label>
													<input type="checkbox" id="checkAlertAdminImminent" name="checkAlertAdminImminent"<?php echo ($s && $s->flag_alert_admin_imminent) ? ' checked="checked"' : ''; ?>>
													Alert <strong>admins</strong> if signups exist in next 2 days
												</label>
											</div>
										</div>

										<div class="form-group">
											<div class="text-right">
												<button type="submit" id="btnSheetInfoSubmit" class="btn btn-primary" data-loading-text="Saving...">Save
												</button>
												<a href="<?php echo APP_ROOT_PATH; ?>/app_code/sheets_all.php" id="btnSheetInfoCancel" class="btn btn-default btn-link btn-cancel">Cancel</a>
											</div>
											<div class="error"></div>
										</div>
									</form>
								</div>
								<!-- End: Basic Sheet Info -->

								<?php
									// for a new sheet: hide advanced settings
									if (!$sheetIsNew) {
										?>
										<!-- Begin: Sheet Access-->
										<!--DKC IMPORTANT (normal): set class to: 'tab-pane fade'-->
										<!--DKC IMPORTANT (testing): set class to: 'tab-pane fade active in'-->
										<div role="tabpanel" id="tabSheetAccess" class="tab-pane fade" aria-labelledby="tabSheetAccess">
											<div class="form-group">
												<strong>Who can see signups?</strong><br />

												<div class="radio small col-sm-12">
													<label>
														<input type="radio" id="radioSignupPrivacy1" name="radioSignupPrivacy" <?php echo ($s && $s->flag_private_signups == 0) ? " checked=\"checked\" " : ''; ?> value="0">
														Users can see all signups
													</label>
												</div>
												<div class="radio small col-sm-12">
													<label>
														<input type="radio" id="radioSignupPrivacy2" name="radioSignupPrivacy" <?php echo ($s && $s->flag_private_signups == 1) ? " checked=\"checked\" " : ''; ?> value="1">
														Users can only see their own signups
													</label>
												</div>
											</div>

											<div class="form-group">
												<?php
													/*
													// debugging only
													$USER->cacheEnrollments();
													$s->cacheAccess();
													util_prePrintR($USER->enrollments);
													util_prePrintR($s->access);
													*/
												?>
												<strong>Who can sign up?</strong><br />

												<div class="wms_indent_tiny">
													<!-- List: My (current) Courses and/or (all) Organizations (within Canvas LMS, these are specified as sub-accounts)-->
													<span class="small"><strong>People in my <em>published</em> courses or organizations</strong><br />
													 (This list updates every 12 hours)<br /></span>

													<div id="access_by_course_enr_list" class="cb_list">
														<div class="checkbox small col-sm-12">
															<?php
																$USER->cacheEnrollments();
																if (count($USER->enrollments) == 0) {
																	echo "You are not enrolled in any courses or organizations.<br />";
																}
																else {
																	// fetch which courses, if any, that this user has already given access
																	$s->cacheAccess();
																	// iterate this user's enrollments
																	foreach ($USER->enrollments as $enr) {
																		$checkboxSelected = "";

																		// fetch any user granted access values for these courses
																		foreach ($s->access as $a) {
																			if ($a->type == "bycourse" && $a->constraint_data == $enr->course_idstr) {
																				$checkboxSelected = " checked=\"checked\" ";
																			}
																		}
																		// display organizations and/or courses
																		if (substr($enr->course_idstr, 0, 3) == "ORG") {
																			// for organizations: show course short_name (instead of variable length course_idstr)
																			// note: the standard format of organization course_idstr is: "ORG-Some-Name-Here-20150715"
																			$course = Course::getOneFromDb(['canvas_course_id' => $enr->canvas_course_id], $DB);
																			echo "<label><input type=\"checkbox\" id=\"access_by_course_enr_" . htmlentities($enr->enrollment_id, ENT_QUOTES, 'UTF-8') . "\" class=\"access_by_course_ckboxes\"  name=\"access_by_course_enr_" . htmlentities($enr->enrollment_id, ENT_QUOTES, 'UTF-8') . "\" data-permtype=\"bycourse\" data-permval=\"" . htmlentities($enr->course_idstr, ENT_QUOTES, 'UTF-8') . "\"" . $checkboxSelected . ">" . htmlentities($course->short_name, ENT_QUOTES, 'UTF-8') . "</label><br />";
																		}
																		else {
																			// for courses: show the standard course_idstr
																			echo "<label><input type=\"checkbox\" id=\"access_by_course_enr_" . htmlentities($enr->enrollment_id, ENT_QUOTES, 'UTF-8') . "\" class=\"access_by_course_ckboxes\"  name=\"access_by_course_enr_" . htmlentities($enr->enrollment_id, ENT_QUOTES, 'UTF-8') . "\" data-permtype=\"bycourse\" data-permval=\"" . htmlentities($enr->course_idstr, ENT_QUOTES, 'UTF-8') . "\"" . $checkboxSelected . ">" . htmlentities($enr->course_idstr, ENT_QUOTES, 'UTF-8') . "</label><br />";
																		}
																	}
																}
															?>
														</div>
													</div>

													<!-- List: All Instructors -->
													<div class="wms_tiny_break"><br /></div>
													<span class="small"><strong>And/or people in <em>published</em> courses taught by</strong><br /></span>

													<div id="access_by_instr_list" class="cb_list">
														<div class="checkbox small col-sm-12">
															<?php
																// BEGIN: measure code execution
																// $startCodeTimer = microtime(TRUE); // debugging

																// Use Custom SQL to relieve bottleneck
																$sql  = "SELECT DISTINCT users.* FROM enrollments INNER JOIN users ON enrollments.canvas_user_id = users.canvas_user_id WHERE enrollments.course_role_name = 'teacher' ORDER BY users.last_name ASC, users.first_name ASC;";
																$stmt = $DB->prepare($sql);
																$stmt->execute();
																$instr_users = $stmt->fetchAll(PDO::FETCH_ASSOC); // TODO - still necessary to convert record set to an array?

																// END: measure code execution
																// echo "Custom SQL inner join: " . (microtime(TRUE) - $startCodeTimer) . "<br />"; // debugging

																if (count($instr_users) == 0) {
																	echo "There are no instructors in any courses.<br />";
																}
																else {
																	// fetch which courses, if any, that this user has already given access
																	$s->cacheAccess();

																	// iterate this user's enrollments
																	foreach ($instr_users as $u) {
																		$checkboxSelected = "";

																		// fetch any user granted access values for these courses
																		foreach ($s->access as $a) {
																			// if ($a->type == "byinstr" && $a->constraint_id == $u->user_id) {
																			if ($a->type == "byinstr" && $a->constraint_id == $u['user_id']) {
																				$checkboxSelected = " checked=\"checked\" ";
																			}
																		}
																		echo "<label><input type=\"checkbox\" id=\"access_by_instr_" . htmlentities($u['user_id'], ENT_QUOTES, 'UTF-8') . "\" class=\"access_by_instructor_ckboxes\" name=\"access_by_instr_" . htmlentities($u['user_id'], ENT_QUOTES, 'UTF-8') . "\" data-permtype=\"byinstr\" data-permval=\"" . htmlentities($u['user_id'], ENT_QUOTES, 'UTF-8') . "\"" . $checkboxSelected . ">" . htmlentities($u['first_name'], ENT_QUOTES, 'UTF-8') . " " . htmlentities($u['last_name'], ENT_QUOTES, 'UTF-8') . "</label><br />";
																	}
																}
															?>
														</div>
													</div>

													<!-- List: These People -->
													<div class="wms_tiny_break"><br /></div>
												<span class="small"><strong>And/or this list of <?php echo LANG_INSTITUTION_NAME_SHORT; ?> usernames</strong>
													<button type="button" class="btn btn-xs btn-link" data-toggle="tooltip" data-placement="top" title="Separate usernames by spaces and/or commas (example: jdoe1, pvalley asmith2)">
														<i class="glyphicon glyphicon-info-sign" style="font-size: 18px;"></i></button><br />
												</span>
													<?php
														// create array of usernames where access type = 'byuser'
														$byuser_ary = [];
														foreach ($s->access as $a) {
															if ($a->type == "byuser") {
																array_push($byuser_ary, $a->constraint_data);
															}
														}
														sort($byuser_ary);
														// util_prePrintR($byuser_ary);
													?>

													<div id="access_by_user">
														<textarea id="textAccessByUserList" name="textAccessByUserList" data-permtype="byuser" class="form-control input-sm" placeholder="Separate usernames by spaces and/or commas (example: jdoe1, pvalley asmith2)" rows="1"><?php echo implode(", ", $byuser_ary); ?></textarea>
													</div>

													<!-- Bootstrap panel -->
													<!-- List: People who are a -->
													<div class="wms_tiny_break"><br /></div>
													<span class="small"><strong>And/or people in any course who are</strong><br /></span>

													<div id="access_by_role_list">
														<div id="wms_panel_list" class="checkbox small col-sm-12">
															<?php
																// util_prePrintR($s->access);
																// fetch any user granted access values for these courses
																$checkboxSelected_byrole_teacher = "";
																$checkboxSelected_byrole_student = "";
																$checkboxSelected_byhasaccount   = "";
																foreach ($s->access as $a) {
																	if ($a->type == "byrole" && $a->constraint_data == "teacher") {
																		$checkboxSelected_byrole_teacher = " checked=\"checked\" ";
																	}
																	elseif ($a->type == "byrole" && $a->constraint_data == "student") {
																		$checkboxSelected_byrole_student = " checked=\"checked\" ";
																	}
																	elseif ($a->type == "byhasaccount" && $a->constraint_data == "all") {
																		$checkboxSelected_byhasaccount = " checked=\"checked\" ";
																	}
																}
															?>
															<p>
																<label>
																	<input type="checkbox" id="access_by_role_teacher" name="access_by_role_teacher" data-permtype="teacher" data-permval="byrole" title="Teachers" <?php echo $checkboxSelected_byrole_teacher; ?>>
																	Teachers
																</label>&nbsp;
																<label>
																	<input type="checkbox" id="access_by_role_student" name="access_by_role_student" data-permtype="student" data-permval="byrole" title="Students" <?php echo $checkboxSelected_byrole_student; ?>>
																	Students
																</label>&nbsp;
																<label>
																	<input type="checkbox" id="access_by_any" name="access_by_any" data-permtype="byhasaccount" data-permval="all" title="Everyone (teachers, students, auditors, etc.)" <?php echo $checkboxSelected_byhasaccount; ?>>
																	Everyone
																</label>
															</p>
														</div>
													</div>
												</div>

												<!-- Admin management-->
												<div class="form-group text-danger">
													<strong>Admins: Who else can <u>manage</u> this sheet?</strong><br />

													<div class="wms_indent_tiny">
														<!-- List: These People -->
														<span class="small"><strong>This list of <?php echo LANG_INSTITUTION_NAME_SHORT; ?> usernames</strong>
															<button type="button" class="btn btn-xs btn-link" data-toggle="tooltip" data-placement="top" title="Separate usernames by spaces and/or commas (example: jdoe1, pvalley asmith2)">
																<i class="glyphicon glyphicon-info-sign" style="font-size: 18px;"></i></button><br />
														</span>

														<?php
															// create array of usernames where access type = 'adminbyuser'
															$adminbyuser_ary = [];
															foreach ($s->access as $a) {
																if ($a->type == "adminbyuser") {
																	array_push($adminbyuser_ary, $a->constraint_data);
																}
															}
															sort($adminbyuser_ary);
															// util_prePrintR($adminbyuser_ary);
														?>

														<div id="access_by_user">
															<textarea style="border-color: red" id="textAdminByUserList" name="textAdminByUserList" data-permtype="adminbyuser" class="form-control input-sm text-danger" placeholder="Separate usernames by spaces and/or commas (example: jdoe1, pvalley asmith2)" rows="1"><?php echo implode(", ", $adminbyuser_ary); ?></textarea>
														</div>
													</div>
												</div>

											</div>
											<!--end div.form-group-->
										</div>
										<script>
											// display DOM content, hide spinner
											$("#spinner_tabSheetAccess").addClass("hidden");
											$("#anchor_tabSheetAccess").removeClass("hidden");
										</script>
										<!-- End: Sheet Access-->
										<?php
									}
								?>
							</div>
						</div>
					</div>
				</div>
				<!-- End: Basic Sheet Info / Sheet Access -->
				<div class="col-sm-1">&nbsp;</div>
				<!-- Begin: Calendar View / List View -->
				<?php
					// for a new sheet: hide advanced settings
					if (!$sheetIsNew) {
						?>
						<div class="col-sm-6">
							<div class="row">
								<!-- show spinner icon (visual placeholder) until DOM content (hidden) has fully loaded -->
								<span id="spinner_calendarTabs"><img height="39" width="36" src="../img/spinner.gif" />&nbsp;Calendar View</span>

								<div id="content_calendarTabs" class="tab-container hidden" role="tabpanel" data-example-id="set2">
									<ul id="boxOpeningsHeader" class="nav nav-tabs" role="tablist">
										<li role="presentation" class="active">
											<a href="#tabOpeningsCalendarView" role="tab" data-toggle="tab" aria-controls="tabOpeningsCalendarView" aria-expanded="false">Calendar
												View</a>
										</li>
										<li role="presentation" class="">
											<a href="#tabOpeningsListView" role="tab" data-toggle="tab" aria-controls="tabOpeningsListView" aria-expanded="false">List
												View</a>
										</li>
									</ul>
									<div id="boxOpeningsContent" class="tab-content">

										<!-- Begin: Calendar View -->
										<div role="tabpanel" id="tabOpeningsCalendarView" class="tab-pane fade active in" aria-labelledby="tabOpeningsCalendarView">

											<?php
												renderCalendarWidget_EDIT($s->sheet_id);
												//renderCalendarWidget(c1,c2,c3,axv);
											?>

										</div>
										<!-- End: Calendar View -->

										<!-- Begin: List View -->
										<div role="tabpanel" id="tabOpeningsListView" class="tab-pane fade PrintArea wms_print_EditOne" aria-labelledby="tabOpeningsListView">
											<div id="buttons_list_openings">
												<!-- PrintArea: Print a specific div -->
												<a href="#" class="wmsPrintArea" data-what-area-to-print="wms_print_EditOne" title="Print only this section"><i class="glyphicon glyphicon-print"></i></a>
												<!-- TOGGLE LINK: Show optional history -->
												<a href="#" id="link_for_history_openings" type="button" class="btn btn-link btn-xs" title="toggle history">show
													history</a>
												<!-- Download as CSV file -->
												<a href="#" id="" class="btn btn-link btn-xs wmsExportCSV" title="Download CSV file (includes all history)"><i class="glyphicon glyphicon-save"></i>csv</a>
											</div>

											<div id="openings-list-container">

												<?php
													$s->cacheOpenings();

													// count how many openings belong to this opening_group_id (an opening is "repeating" if it has > 1 opening per opening_group_id)
													$countOpeningsPerGroup_ary = array_count_values(array_map(function ($item) {
														return $item->opening_group_id;
													}, $s->openings));
													// util_prePrintR($countOpeningsPerGroup_ary);

													$flagFutureOpeningSignup = FALSE;
													$lastOpeningDate         = '';
													$daysOpenings            = [];
													$todayYmd                = explode(' ', util_currentDateTimeString())[0];
													// CSV output: set headers manually
													$csv_string_builder = '"SIS_ID","Full_Name","Username","Opening_Date","Opening_Time","Date_User_Signed_Up"' . "\n";

													foreach ($s->openings as $opening) {
														$curOpeningDate = explode(' ', $opening->begin_datetime)[0];
														if ($curOpeningDate != $lastOpeningDate) {
															// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
															foreach ($daysOpenings as $op) {
																echo $op->renderAsHtmlOpeningWithFullControls($countOpeningsPerGroup_ary) . "\n";
																// CSV output: build string with openings and any user signups
																$csv_string_builder .= $op->renderAsCSV();
															}

															if ($lastOpeningDate) {
																echo '</div>' . "\n";
															}
															$relative_time_class = 'in-the-past toggle_opening_history';
															//util_prePrintR('$curOpeningDate : $todayYmd = '.$curOpeningDate .':'. $todayYmd);
															if ($curOpeningDate == $todayYmd) {
																$relative_time_class     = 'in-the-present';
																$flagFutureOpeningSignup = FALSE;
															}
															elseif ($curOpeningDate > $todayYmd) {
																$relative_time_class     = 'in-the-future';
																$flagFutureOpeningSignup = FALSE;
															}
															echo '<div class="opening-list-for-date ' . $relative_time_class . '" data-for-date="' . $curOpeningDate . '"><h4>' . date_format(new DateTime($opening->begin_datetime), "m/d/Y") . '</h4>';
															$daysOpenings = [];
														}
														array_push($daysOpenings, $opening);

														$lastOpeningDate = $curOpeningDate;
													}
													// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo $op->renderAsHtmlOpeningWithFullControls($countOpeningsPerGroup_ary) . "\n";
														// CSV output: build string with openings and any user signups (this is the last array element)
														$csv_string_builder .= $op->renderAsCSV();
													}
													echo '</div>' . "\n";

													// display placeholder message
													if (!$flagFutureOpeningSignup) {
														echo '<div class="opening-list-for-date in-the-future"><br /><em>There are no openings for future dates.</em></div>';
													}

													// CSV output: store in DOM as hidden content
													echo "<div class=\"hidden wms_export_CSV\">" . $csv_string_builder . "</div>" . "\n";;
												?>
											</div>
										</div>
										<!-- End: List View -->
									</div>
								</div>
							</div>
						</div>
						<script>
							// display DOM content, hide spinner
							$("#spinner_calendarTabs").addClass("hidden");
							$("#content_calendarTabs").removeClass("hidden");
						</script>
						<?php
					}
				?>
				<!-- End: Calendar View / List View -->
			</div>
		</div> <!-- end: div.container -->

		<?php
		echo "</div>"; // end: div#content_container

		// Bootstrap Modal: Calendar Create Opening, Calendar Edit Opening
		renderCalendarModalCreateOpening($s->sheet_id);
		renderCalendarModalEditOpening($s->sheet_id);
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once(dirname(__FILE__) . '/../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/sheets_edit_one.js"></script>
