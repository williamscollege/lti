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
			// postback
			// 1) postback for brand new sheet (record not yet in db)
			// 2) postback for edited sheet (record exists in db)

			if (isset($_REQUEST["sheet"])) {
				// populate fields based on DB record
				$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
			}
			else {
				// create new sheet
				$s = SUS_Sheet::createNewSheet($USER->user_id, $DB);
			}

			// util_prePrintR($_REQUEST); // debugging

			$s->updated_at               = date("Y-m-d H:i:s");
			$s->owner_user_id            = $USER->user_id;
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

			if (!$s->matchesDb) {
				$s->updateDb();
			}
		}
		else {
			if (isset($_REQUEST["sheet"])) {
				// use cases:
				// 1) requested to edit existing sheet from link on another page (record exists in db)
				$sheetIsDataIncoming = TRUE;
				$s                   = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
			}
		}


		echo "<div id=\"content_container\">"; // begin: div#content_container
		echo "<h5 class=\"small\"><a href=\"" . APP_ROOT_PATH . "/app_code/sheets_all.php\" title=\"" . ucfirst(util_lang('sheets_all')) . "\">" . ucfirst(util_lang('sheets_all')) . "</a>&nbsp;&gt;&nbsp;" . $s->name . "</h5>";


		// ***************************
		// fetch sheetgroups
		// ***************************
		$USER->cacheSheetgroups();
		// util_prePrintR($USER->managed_sheets);

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
											<a href="#tabSheetAccess" role="tab" data-toggle="tab" aria-controls="tabSheetAccess" aria-expanded="false">Sheet
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
										<input type="hidden" id="hiddenSheetID" name="sheet" value="<?php echo $s ? $s->sheet_id : 0; ?>">
										<input type="hidden" id="hiddenAction" name="hiddenAction" value="savesheet">

										<div class="form-group">
											<label for="inputSheetName" class="control-label">Sheet Name</label>

											<div class="">
												<input type="text" id="inputSheetName" name="inputSheetName" class="form-control input-sm" maxlength="255" placeholder="Signup sheet name" value="<?php echo $s ? htmlspecialchars($s->name, ENT_QUOTES) : ''; ?>" />
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
															echo "<option" . $optionSelected . " value=\"" . $sg->sheetgroup_id . "\">" . $sg->name . "</option>";
														}
													?>
												</select>

												<span class="small"><a href="<?php echo APP_ROOT_PATH; ?>/app_code/sheets_all.php?sheetgroup=<?php echo $currentSheetgroupID; ?>" title="<?php echo $currentSheetgroupName . " (&quot;" . $currentSheetgroupDesc . "&quot;)"; ?>">Go
														to current group</a></span>
											</div>
										</div>

										<div class="form-group">
											<label for="textSheetDescription" class="control-label">Description</label>

											<div class="">
												<textarea id="textSheetDescription" name="textSheetDescription" class="form-control input-sm" placeholder="Instructions for this signup sheet" rows="2"><?php echo $s ? $s->description : ''; ?></textarea>
											</div>
										</div>

										<div class="form-group">
											<label for="inputSheetDateBegin" class="control-label">Date Span: Active from</label>

											<div class="form-inline">
												<input type="text" id="inputSheetDateBegin" name="inputSheetDateBegin" class="form-control input-sm" maxlength="10" placeholder="mm/dd/yyyy" value="<?php echo $s ? date_format(new DateTime($s->begin_date), "m/d/Y") : ''; ?>" />
												<strong>to</strong>
												<input type="text" id="inputSheetDateEnd" name="inputSheetDateEnd" class="form-control input-sm" maxlength="10" placeholder="mm/dd/yyyy" value="<?php echo $s ? date_format(new DateTime($s->end_date), "m/d/Y") : ''; ?>" />
											</div>
										</div>

										<div class="form-group">
											<label for="selectMaxTotalSignups" class="control-label">Maximum Signups</label>

											<div class="form-inline small">
												<div class="well well-sm"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i> Group
													settings affect all sheets in this group. Sheet settings affect only that sheet.
												</div>
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
											<strong>Notifications</strong><br />

											<div class="checkbox small col-sm-12">
												<label>
													<input type="checkbox" id="checkAlertOwnerSignup" name="checkAlertOwnerSignup"<?php echo ($s && $s->flag_alert_owner_signup) ? ' checked="checked"' : ''; ?>>
													Email <strong>owner</strong> on signup or cancel
												</label><br />
												<label>
													<input type="checkbox" id="checkAlertOwnerImminent" name="checkAlertOwnerImminent"<?php echo ($s && $s->flag_alert_owner_imminent) ? ' checked="checked"' : ''; ?>>
													Email <strong>owner</strong> on upcoming signup
												</label><br />
												<label>
													<input type="checkbox" id="checkAlertAdminSignup" name="checkAlertAdminSignup"<?php echo ($s && $s->flag_alert_admin_signup) ? ' checked="checked"' : ''; ?>>
													Email <strong>admin</strong> on signup or cancel
												</label><br />
												<label>
													<input type="checkbox" id="checkAlertAdminImminent" name="checkAlertAdminImminent"<?php echo ($s && $s->flag_alert_admin_imminent) ? ' checked="checked"' : ''; ?>>
													Email <strong>admin</strong> on upcoming signup
												</label>
											</div>
										</div>

										<div class="form-group">
											<div class="text-right">
												<button type="submit" id="btnSheetInfoSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save
												</button>
												<a href="<?php echo APP_ROOT_PATH; ?>/app_code/sheets_all.php" id="btnSheetInfoCancel" class="btn btn-default btn-link btn-cancel">Cancel</a>
											</div>
										</div>
									</form>
								</div>
								<!-- End: Basic Sheet Info -->

								<!-- Begin: Sheet Access-->
								<!--DKC IMPORTANT (normal): set class to: 'tab-pane fade'-->
								<!--DKC IMPORTANT (testing): set class to: 'tab-pane fade active in'-->
								<div role="tabpanel" id="tabSheetAccess" class="tab-pane fade" aria-labelledby="tabSheetAccess">
									<div class="form-group">
										<strong>Who can see signups</strong><br />

										<div class="radio small col-sm-12">
											<label>
												<input type="radio" id="radioSignupPrivacy1" name="radioSignupPrivacy" <?php echo ($s && $s->flag_private_signups == 0) ? " checked=\"checked\" " : ''; ?> value="0">
												Users can see who signed up when
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
										<p><strong>Who can sign up</strong></p>

										<!-- List: Courses -->
										<span class="small"><strong>People in these courses</strong><br /></span>

										<div id="access_by_course_enr_list" class="cb_list">
											<div class="checkbox small col-sm-12">
												<?php
													$USER->cacheEnrollments();
													if (count($USER->enrollments) == 0) {
														echo "You are not enrolled in any courses.<br />";
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
															echo "<label><input type=\"checkbox\" id=\"access_by_course_enr_" . $enr->enrollment_id . "\" class=\"access_by_course_ckboxes\"  name=\"access_by_course_enr_" . $enr->enrollment_id . "\" data-permtype=\"bycourse\" data-permval=\"" . $enr->course_idstr . "\"" . $checkboxSelected . ">" . $enr->course_idstr . "</label><br />";
														}
													}
												?>
											</div>
										</div>

										<!-- List: Instructors -->
										<div class="wms_tiny_break"><br /></div>
										<span class="small"><strong>People in courses taught by</strong><br /></span>

										<div id="access_by_instr_list" class="cb_list">
											<div class="checkbox small col-sm-12">
												<?php
													$instr_enrollments = Enrollment::getAllFromDb(['course_role_name' => 'teacher'], $DB);
													$instr_uid_hash    = [];
													foreach ($instr_enrollments as $i) {
														array_push($instr_uid_hash, $i->user_id);
													}
													$instr_users = User::getAllFromDb(['user_id' => $instr_uid_hash], $DB);
													usort($instr_users, 'User::cmp');

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
																if ($a->type == "byinstr" && $a->constraint_id == $u->user_id) {
																	$checkboxSelected = " checked=\"checked\" ";
																}
															}
															echo "<label><input type=\"checkbox\" id=\"access_by_instr_" . $u->user_id . "\" class=\"access_by_instructor_ckboxes\" name=\"access_by_instr_" . $u->user_id . "\" data-permtype=\"byinstr\" data-permval=\"" . $u->user_id . "\"" . $checkboxSelected . ">" . $u->first_name . " " . $u->last_name . "</label><br />";
														}
													}
												?>
											</div>
										</div>

										<!-- List: These People -->
										<div class="wms_tiny_break"><br /></div>
										<span class="small"><strong>These people: Williams username(s)</strong>
											<button type="button" class="btn btn-link" data-toggle="tooltip" data-placement="top" title="Separate usernames by white space and/or commas">
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
											<textarea id="textAccessByUserList" name="textAccessByUserList" data-permtype="byuser" class="form-control input-sm" placeholder="Separate usernames by white space and/or commas" rows="1"><?php echo implode(", ", $byuser_ary); ?></textarea>
										</div>

										<!-- Bootstrap panel -->
										<!-- List: People who are a -->
										<div class="wms_tiny_break"><br /></div>
										<span class="small"><strong>People who are a...</strong><br /></span>

										<div class="panel panel-default">
											<div id="access_by_role_list" class="panel-body nopadding">
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

													<label>
														<input type="checkbox" id="access_by_role_teacher" name="access_by_role_teacher" data-permtype="teacher" data-permval="byrole" <?php echo $checkboxSelected_byrole_teacher; ?>>
														Teacher of a course
													</label><br />
													<label>
														<input type="checkbox" id="access_by_role_student" name="access_by_role_student" data-permtype="student" data-permval="byrole" <?php echo $checkboxSelected_byrole_student; ?>>
														Student in a course
													</label><br />
													<label>
														<input type="checkbox" id="access_by_any" name="access_by_any" data-permtype="byhasaccount" data-permval="all" <?php echo $checkboxSelected_byhasaccount; ?>>
														Glow user
													</label>
												</div>
											</div>
										</div>

										<!-- Admin management-->
										<div class="form-group">
											<p><strong>Who can manage the sheet</strong></p>

											<!-- List: These People -->
											<span class="small"><strong>These people: Williams username(s)</strong>
												<button type="button" class="btn btn-link" data-toggle="tooltip" data-placement="top" title="Separate usernames by white space and/or commas">
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
												<textarea id="textAdminByUserList" name="textAdminByUserList" data-permtype="adminbyuser" class="form-control input-sm" placeholder="Separate usernames by white space and/or commas" rows="1"><?php echo implode(", ", $adminbyuser_ary); ?></textarea>
											</div>
										</div>

									</div>
									<!--end div.form-group-->
								</div>
								<!-- End: Sheet Access-->
							</div>
						</div>
					</div>
				</div>
				<!-- End: Basic Sheet Info / Sheet Access -->
				<div class="col-sm-1">&nbsp;</div>
				<!-- Begin: Calendar Openings / List Openings -->
				<?php
					// for a new sheet: hide advanced settings
					if (!$sheetIsNew) {
						?>
						<div class="col-sm-6">
							<div class="row">
								<div class="tab-container" role="tabpanel" data-example-id="set2">
									<ul id="boxOpeningsHeader" class="nav nav-tabs" role="tablist">
										<li role="presentation" class="active">
											<a href="#tabOpeningsCalendar" role="tab" data-toggle="tab" aria-controls="tabOpeningsCalendar" aria-expanded="false">Calendar
												Openings</a>
										</li>
										<li role="presentation" class="">
											<a href="#tabOpeningsList" role="tab" data-toggle="tab" aria-controls="tabOpeningsList" aria-expanded="false">List
												Openings</a>
										</li>
									</ul>
									<div id="boxOpeningsContent" class="tab-content">

										<!-- Begin: Calendar Openings -->
										<div role="tabpanel" id="tabOpeningsCalendar" class="tab-pane fade active in" aria-labelledby="tabOpeningsCalendar">

											<?php
												renderCalendarWidget_EDIT($s->sheet_id);
												//renderCalendarWidget(c1,c2,c3,axv);
											?>

										</div>
										<!-- End: Calendar Openings -->

										<!-- Begin: List Openings -->
										<div role="tabpanel" id="tabOpeningsList" class="tab-pane fade" aria-labelledby="tabOpeningsList">
											<a href="#" id="scroll-to-todayish-openings" type="button" class="btn btn-success btn-xs" title="go to next">go to
												next</a>

											<div id="openings-list-container">

												<?php
													$s->cacheOpenings();

													// count openings per each opening_group_id
													$countOpeningsPerGroup = array_count_values(array_map(function ($item) {
														return $item->opening_group_id;
													}, $s->openings));
													// util_prePrintR($countOpeningsPerGroup);

													$lastOpeningDate = '';
													$daysOpenings    = [];
													$todayYmd        = explode(' ', util_currentDateTimeString())[0];

													foreach ($s->openings as $opening) {
														$curOpeningDate = explode(' ', $opening->begin_datetime)[0];
														if ($curOpeningDate != $lastOpeningDate) {
															// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
															foreach ($daysOpenings as $op) {
																echo $op->renderAsHtmlOpeningWithFullControls($countOpeningsPerGroup) . "\n";
															}

															if ($lastOpeningDate) {
																echo '</div>' . "\n";
															}
															$relative_time_class = 'in-the-past';
															//util_prePrintR('$curOpeningDate : $todayYmd = '.$curOpeningDate .':'. $todayYmd);
															if ($curOpeningDate == $todayYmd) {
																$relative_time_class = 'in-the-present';
															}
															elseif ($curOpeningDate > $todayYmd) {
																$relative_time_class = 'in-the-future';
															}
															echo '<div class="opening-list-for-date ' . $relative_time_class . '" data-for-date="' . $curOpeningDate . '"><h4>' . date_format(new DateTime($opening->begin_datetime), "m/d/Y") . '</h4>';
															$daysOpenings = [];
														}
														array_unshift($daysOpenings, $opening);

														$lastOpeningDate = $curOpeningDate;
													}
													// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo $op->renderAsHtmlOpeningWithFullControls($countOpeningsPerGroup) . "\n";
													}
													echo '</div>' . "\n";
												?>

											</div>
										</div>
										<!-- End: List Openings -->
									</div>
								</div>
							</div>
						</div>
					<?php
					}
				?>
				<!-- End: Calendar Openings / List Openings -->
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
