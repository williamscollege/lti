<?php
	require_once('../app_setup.php');
	$sheetDataIncoming = TRUE;

	if (isset($_REQUEST["sheet"]) && $_REQUEST["sheet"] == "new") {
		$pageTitle = ucfirst(util_lang('add_sheet'));
		//		$isNewSheet = TRUE;
		$sheetDataIncoming = FALSE;
	}
	else {
		$pageTitle = ucfirst(util_lang('edit_sheet'));
		//		$isNewSheet = FALSE;
		if (isset($_REQUEST["hiddenAction"]) && $_REQUEST["hiddenAction"] == "savesheet") {
			$sheetDataIncoming = TRUE;
		} else {
			$sheetDataIncoming = FALSE;
		}
	}
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {

		$s = FALSE;

		// Postback
		//		if (!$isNewSheet) {
		if ($sheetDataIncoming) {

			if (isset($_REQUEST["sheet"])) {
				$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
			}
			else {
				$s = SUS_Sheet::createNewSheet($USER->user_id, $DB);
			}

			//if (isset($_REQUEST["hiddenAction"]) && $_REQUEST["hiddenAction"] == "addsheet") {
			util_prePrintR($_REQUEST);

			// create record (if there is no match in DB)
			//$s->sheet_id                  = $_REQUEST[""];
			//					$s->created_at                = date("Y-m-d H:i:s");
			//					$s->updated_at                = date("Y-m-d H:i:s");
			//$s->flag_delete               = $_REQUEST[""];
			//$s->owner_user_id             = $USER->user_id;
			$s->sheetgroup_id = $_REQUEST["selectSheetgroupID"];
			$s->name          = $_REQUEST["inputSheetName"];
			$s->description   = $_REQUEST["textSheetDescription"];
			$s->type          = "timeblocks";
			$s->date_opens    = date_format(new DateTime($_REQUEST["inputSheetDateStart"] . " 00:00:00"), "Y-m-d H:i:s");
			$s->date_closes   = date_format(new DateTime($_REQUEST["inputSheetDateEnd"] . " 23:59:59"), "Y-m-d H:i:s");
			//$s->date_closes               = date_format(strtotime($_REQUEST["inputSheetDateEnd"]," 23:59:59"),"Y-m-d H:i:s");
			$s->max_total_user_signups   = $_REQUEST["selectMaxTotalSignups"];
			$s->max_pending_user_signups = $_REQUEST["selectMaxPendingSignups"];
			//$s->flag_alert_owner_change   = $_REQUEST[""];
			$s->flag_alert_owner_signup   = util_getValueForCheckboxRequestData('checkAlertOwnerSignup');// _REQUEST["checkAlertOwnerSignup"];
			$s->flag_alert_owner_imminent = util_getValueForCheckboxRequestData('checkAlertOwnerImminent');// $_REQUEST["checkAlertOwnerImminent"];
			//$s->flag_alert_admin_change   = $_REQUEST[""];
			// TODO special handling for checkboxes
			$s->flag_alert_admin_signup   = util_getValueForCheckboxRequestData('checkAlertAdminSignup');
			$s->flag_alert_admin_imminent = util_getValueForCheckboxRequestData('checkAlertAdminImminent');
			//$s->flag_private_signups      = "";

			if (!$s->matchesDb) {
				$s->updateDb();
			}
			//				else {
			//					echo "error: matching record already exists";
			//					exit;
			//				}
			///	}

			// Populate fields based on DB record
			// util_prePrintR($s);

		}
		else {
			if (isset($_REQUEST["sheet"])) {
				$sheetDataIncoming = TRUE;
				$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
			}
		}


		echo "<div id=\"parent_container\">"; // start: div#parent_container
		echo "<h3>" . $pageTitle . "</h3>";
		// echo "<p>Customize your signup sheet.</p>";


		// ***************************
		// fetch sheetgroups
		// ***************************
		$USER->cacheSheetgroups();
		// util_prePrintR($USER->managed_sheets);

		?>
		<div class="container">
			<div class="row">
				<!-- Basic Sheet Info / Sheet Access -->
				<div class="col-sm-5">
					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<ul id="boxSheet" class="nav nav-tabs" role="tablist">
								<!--IMPORTANT: set class to: 'active'-->
								<li role="presentation" class="">
									<a href="#tabSheetInfo" role="tab" data-toggle="tab" aria-controls="tabSheetInfo" aria-expanded="false">Basic Sheet Info</a>
								</li>
								<?php
									if ($sheetDataIncoming) {
										// for a new sheet: hide advanced settings
										?>
										<!--IMPORTANT: set class to: ''-->
										<li role="presentation" class="active">
											<a href="#tabSheetAccess" role="tab" data-toggle="tab" aria-controls="tabSheetAccess" aria-expanded="false">Sheet
												Access</a>
										</li>
									<?php
									}
								?>
							</ul>
							<div id="boxSheetContent" class="tab-content">

								<!-- Start: Basic Sheet Info -->
								<!--IMPORTANT: set class to: 'tab-pane fade active in'-->
								<div role="tabpanel" id="tabSheetInfo" class="tab-pane fade" aria-labelledby="tabSheetInfo">
									<form action="edit_sheet.php" id="frmEditSheet" name="frmEditSheet" class="form-group" role="form" method="post">
										<!--<input type="hidden" id="hiddenSheetgroupID" name="hiddenSheetgroupID" value="<?php /*echo $USER->sheetgroups->sheetgroup_id; */ ?>">-->
										<input type="hidden" id="hiddenSheetID" name="sheet" value="<?php echo $s ? $s->sheet_id : 0; ?>">
										<input type="hidden" id="hiddenAction" name="hiddenAction" value="savesheet">
										<!--<input type="hidden" id="subaction" name="subaction" value="addsheet">-->

										<div class="form-group">
											<label for="inputSheetName" class="control-label">Sheet Name</label>

											<div class="">
												<input type="text" id="inputSheetName" name="inputSheetName" class="form-control input-sm" placeholder="Signup sheet name" maxlength="255" value="<?php echo $s ? $s->name : ''; ?>" />
											</div>
										</div>

										<div class="form-group">
											<label for="selectSheetgroupID" class="control-label">In Group</label>

											<div class="">
												<select id="selectSheetgroupID" name="selectSheetgroupID" class="form-control input-sm">
													<?php
														foreach ($USER->sheetgroups as $sg) {
															$optionSelected = "";
															if ($_REQUEST["sheetgroup"] == $sg->sheetgroup_id) {
																$optionSelected        = " selected=\"selected\" ";
																$currentSheetgroupID   = $sg->sheetgroup_id;
																$currentSheetgroupName = $sg->sheetgroup_name;
															}
															echo "<option" . $optionSelected . " value=\"" . $sg->sheetgroup_id . "\">" . $sg->name . "</option>";
														}
													?>
												</select>

												<!--TODO add popover to show contents of above group...-->
												<span class="small"><a href="my_sheets.php?sheetgroup=<?php echo $currentSheetgroupID; ?>">Go to current
														group</a></span>
											</div>
										</div>

										<div class="form-group">
											<label for="textSheetDescription" class="control-label">Description</label>

											<div class="">
												<textarea id="textSheetDescription" name="textSheetDescription" class="form-control input-sm" placeholder="Instructions for this signup sheet" rows="2"><?php echo $s ? $s->description : ''; ?></textarea>
											</div>
										</div>

										<div class="form-group">
											<label for="inputSheetDateStart" class="control-label">Date Span: Active from</label>

											<div class="form-inline">
												<input type="date" id="inputSheetDateStart" name="inputSheetDateStart" class="form-control input-sm" placeholder="mm/dd/yyyy" maxlength="10" value="<?php echo $s ? date_format(new DateTime($s->date_opens), "m/d/Y") : ''; ?>" />

												<strong>to</strong>
												<input type="date" id="inputSheetDateEnd" name="inputSheetDateEnd" class="form-control input-sm" placeholder="mm/dd/yyyy" maxlength="10" value="<?php echo $s ? date_format(new DateTime($s->date_closes), "m/d/Y") : ''; ?>" />
											</div>
										</div>

										<div class="form-group">
											<label for="selectMaxTotalSignups" class="control-label">Maximum Signups</label>

											<div class="form-inline small">
												Users can have

												<!--												$s->max_total_user_signups    = $_REQUEST["selectMaxTotalSignups"];-->
												<!--												$s->max_pending_user_signups  = $_REQUEST["selectMaxPendingSignups"];-->

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
													?><!--
													<option selected="selected" value="-1">unlimited</option>
													<option value="1">1</option>
													<option value="2">2</option>
													<option value="3">3</option>
													<option value="4">4</option>
													<option value="5">5</option>
													<option value="6">6</option>
													<option value="7">7</option>
													<option value="8">8</option>
													 -->
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
													<!--													<option selected="selected" value="-1">any</option>-->
													<!--													<option value="1">1</option>-->
													<!--													<option value="2">2</option>-->
													<!--													<option value="3">3</option>-->
													<!--													<option value="4">4</option>-->
													<!--													<option value="5">5</option>-->
													<!--													<option value="6">6</option>-->
													<!--													<option value="7">7</option>-->
													<!--													<option value="8">8</option>-->
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
												<!-- TODO - global fix: correct all local/relative paths to APP_ROOT structure -->
												<a href="my_sheets.php" id="btnSheetInfoCancel" class="btn btn-default btn-link btn-cancel">Cancel</a>
											</div>
										</div>
									</form>
								</div>
								<!-- End: Basic Sheet Info -->

								<!--Start: Sheet Access-->
								<!--IMPORTANT: set class to: 'tab-pane fade'-->
								<div role="tabpanel" id="tabSheetAccess" class="tab-pane fade active in" aria-labelledby="tabSheetAccess">
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

										<div id="access_by_course_list" class="cb_list">
											<div class="checkbox small col-sm-12">
												<!-- TODO - iterator, DB saved values, and create unique 'id' values for elements -->
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-ABC
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-DEFG
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-PDQ
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-XYZ
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-ABC
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-DEFG
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-PDQ
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_50" name="access_by_course_50" data-permtype="bycourse" data-permval="50">
													Course 15S-XYZ
												</label><br />
											</div>
										</div>

										<!-- List: Instructors -->
										<div class="wms_tiny_break"><br /></div>
										<span class="small"><strong>People in courses taught by</strong><br /></span>

										<div id="access_by_instr_list" class="cb_list">
											<div class="checkbox small col-sm-12">
												<!-- TODO - iterator, DB saved values, and create unique 'id' values for elements -->
												<label>
													<input type="checkbox" id="access_by_instr_1554" name="access_by_instr_1554" data-permtype="byinstr" data-permval="1554">
													Daniel Aalberts (daalbert)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_5080" name="access_by_course_5080" data-permtype="bycourse" data-permval="5080">
													Sayaka Abe (sa9)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_9587" name="access_by_course_9587" data-permtype="bycourse" data-permval="9587">
													Hanane Aboulahmam (ha3)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_1234" name="access_by_course_1234" data-permtype="bycourse" data-permval="1234">
													Beverly Acha (09bda)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_instr_1554" name="access_by_instr_1554" data-permtype="byinstr" data-permval="1554">
													rDaniel Aalberts (daalbert)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_5080" name="access_by_course_5080" data-permtype="bycourse" data-permval="5080">
													rSayaka Abe (sa9)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_9587" name="access_by_course_9587" data-permtype="bycourse" data-permval="9587">
													rHanane Aboulahmam (ha3)
												</label><br />
												<label>
													<input type="checkbox" id="access_by_course_1234" name="access_by_course_1234" data-permtype="bycourse" data-permval="1234">
													rBeverly Acha (09bda)
												</label><br />
											</div>
										</div>

										<!-- List: These People -->
										<div class="wms_tiny_break"><br /></div>
										<span class="small"><strong>These people: UNIX username(s)</strong><br /></span>

										<div id="access_by_user">
											<textarea id="textAccessByUserList" name="textAccessByUserList" class="form-control input-sm" placeholder="Separate usernames by white space and/or commas" rows="1"></textarea>
										</div>

										<!-- List: People who are a -->
										<!-- Bootstrap panel -->

										<div class="wms_tiny_break"><br /></div>
										<span class="small"><strong>People who are a...</strong><br /></span>

										<div class="panel panel-default">
											<div id="access_by_role_list" class="panel-body nopadding">
												<div id="wms_panel_list" class="checkbox small col-sm-12">
													<!-- TODO - iterator, DB saved values, and create unique 'id' values for elements -->
													<label>
														<input type="checkbox" id="access_by_role_teacher" name="access_by_role_teacher" data-permtype="teacher" data-permval="byrole">
														Teacher of a course
													</label><br />
													<label>
														<input type="checkbox" id="access_by_role_student" name="access_by_role_student" data-permtype="student" data-permval="byrole">
														Student in a course
													</label><br />
													<label>
														<input type="checkbox" id="access_by_any" name="access_by_any" data-permtype="byhasaccount" data-permval="all">
														Glow user
													</label>
												</div>
											</div>
										</div>

										<!-- Admin management-->
										<div class="form-group">
											<p><strong>Who can manage the sheet</strong></p>

											<div class="wms_tiny_break"><br /></div>
											<span class="small"><strong>These people: UNIX username(s)</strong><br /></span>

											<div id="access_by_user">
												<textarea id="textAdminByUserList" name="textAdminByUserList" data-permtype="adminbyuser" class="form-control input-sm" placeholder="Separate usernames by white space and/or commas" rows="1"></textarea>
											</div>
										</div>

									</div>
									<!--end div.form-group-->
								</div>
								<!--End: Sheet Access-->
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-1">&nbsp;</div>
				<?php
					if ($sheetDataIncoming) {
						// for a new sheet: hide advanced settings
						?>
						<!-- Calendar Openings / List Openings -->
						<div class="col-sm-5">
							<div class="row">
								<div class="tab-container" role="tabpanel" data-example-id="set2">
									<ul id="boxOpenings" class="nav nav-tabs" role="tablist">
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

										<!--Start: Calendar Openings -->
										<div role="tabpanel" class="tab-pane fade active in" id="tabOpeningsCalendar" aria-labelledby="tabOpeningsCalendar">

											<p>Calendar stuff</p>

										</div>
										<!--End: Calendar Openings -->

										<!--Start: List Openings -->
										<div role="tabpanel" class="tab-pane fade" id="tabOpeningsList" aria-labelledby="tabOpeningsList">

											<p>List stuff</p>

										</div>
										<!--End: List Openings -->
									</div>
								</div>
							</div>
						</div>
					<?php
					}
				?>
				<div class="col-sm-1">&nbsp;</div>
			</div>
		</div> <!-- end: div.container -->

		<?php
		echo "</div>"; // end: div#parent_container
	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="../js/edit_sheet.js"></script>
