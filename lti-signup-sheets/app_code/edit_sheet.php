<?php
	require_once('../app_setup.php');
	if ($_REQUEST["sheet"] == "new") {
		$pageTitle  = ucfirst(util_lang('add_sheet'));
		$isNewSheet = TRUE;
	}
	else {
		$pageTitle  = ucfirst(util_lang('edit_sheet'));
		$isNewSheet = FALSE;
	}
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
	echo "<div id=\"parent_container\">"; // start: div#parent_container
	echo "<h3>" . $pageTitle . "</h3>";
	// echo "<p>Customize your signup sheet.</p>";


	// ***************************
	// fetch sheet data
	// ***************************
	// $USER->cacheManagedSheets();
	// util_prePrintR($USER->managed_sheets);

?>
<div class="container">
	<div class="row">
		<!-- Basic Sheet Info / Sheet Access -->
		<div class="col-sm-5">
			<div class="row">
				<div class="tab-container" role="tabpanel" data-example-id="set1">
					<ul id="boxSheet" class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active">
							<a href="#tabSheetInfo" role="tab" data-toggle="tab" aria-controls="tabSheetInfo" aria-expanded="false">Basic Sheet Info</a>
						</li>
						<?php
							if (!$isNewSheet) {
								// for a new sheet: hide advanced settings
								?>
								<li role="presentation" class="">
									<a href="#tabSheetAccess" role="tab" data-toggle="tab" aria-controls="tabSheetAccess" aria-expanded="false">Sheet
										Access</a>
								</li>
							<?php
							}
						?>
					</ul>
					<div id="boxSheetContent" class="tab-content">

						<!-- Start: Basic Sheet Info -->
						<div role="tabpanel" class="tab-pane fade active in" id="tabSheetInfo" aria-labelledby="tabSheetInfo">
							<form action="edit_sheet.php" id="frmEditSheet" name="frmEditSheet" class="form-group" role="form" method="post">
								<input type="hidden" id="sheetgroup" name="sheetgroup" value="2077">
								<input type="hidden" id="sheet" name="sheet" value="0">
								<input type="hidden" id="action" name="action" value="editsheet">
								<input type="hidden" id="subaction" name="subaction" value="createsheet">

								<div class="form-group">
									<label for="inputSheetName" class="control-label">Sheet Name</label>

									<div class="">
										<input type="text" id="inputSheetName" name="inputSheetName" class="form-control input-sm" placeholder="Signup sheet name" maxlength="255" value="" />
									</div>
								</div>

								<div class="form-group">
									<label for="selectSheetgroupID" class="control-label">In Group</label>

									<div class="">
										<select id="selectSheetgroupID" name="selectSheetgroupID" class="form-control input-sm">
											<option selected="selected" value="0">unlimited</option>
											<option selected="selected" value="2077">David Keiser-Clark signup-sheets</option>
											<option value="2823">Big Group of Sheets</option>
										</select>

										<!--TODO add popover to show contents of above group...-->
												<span class="small">View <a href="edit_sheet.php?contextid=2&amp;action=editgroup&amp;sheetgroup=2077">"David
														Keiser-Clark signup-sheets"</a> group)</span>
									</div>
								</div>

								<div class="form-group">
									<label for="textSheetDescription" class="control-label">Description</label>

									<div class="">
										<textarea id="textSheetDescription" name="textSheetDescription" class="form-control input-sm" placeholder="Instructions for this signup sheet" rows="2"></textarea>
									</div>
								</div>

								<div class="form-group">
									<label for="inputSheetDateStart" class="control-label">Date Span: Active from</label>

									<div class="form-inline">
										<input type="date" id="inputSheetDateStart" name="inputSheetDateStart" class="form-control input-sm" placeholder="mm/dd/yyyy" maxlength="10" value="" />
										<strong>to</strong>
										<input type="date" id="inputSheetDateEnd" name="inputSheetDateEnd" class="form-control input-sm" placeholder="mm/dd/yyyy" maxlength="10" value="" />
									</div>
								</div>

								<div class="form-group">
									<label for="selectMaxTotalSignups" class="control-label">Maximum Signups</label>

									<div class="form-inline small">
										Users can have
										<select id="selectMaxTotalSignups" name="selectMaxTotalSignups" class="form-control input-sm">
											<option selected="selected" value="-1">unlimited</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
											<option value="6">6</option>
											<option value="7">7</option>
											<option value="8">8</option>
										</select>
										signups on this sheet, and
										<select id="selectMaxPendingSignups" name="selectMaxPendingSignups" class="form-control input-sm">
											<option selected="selected" value="-1">any</option>
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

								<div class="form-group">
									<strong>Notifications</strong><br />

									<div class="checkbox small col-sm-12">
										<label>
											<input type="checkbox" id="checkAlertOwnerSignup" name="checkAlertOwnerSignup" checked="checked">
											Email <strong>owner</strong> on signup or cancel
										</label><br />
										<label>
											<input type="checkbox" id="checkAlertOwnerImminent" name="checkAlertOwnerImminent" checked="checked">
											Email <strong>owner</strong> on upcoming signup
										</label><br />
										<label>
											<input type="checkbox" id="checkAlertAdminSignup" name="checkAlertAdminSignup" checked="checked">
											Email <strong>admin</strong> on signup or cancel
										</label><br />
										<label>
											<input type="checkbox" id="checkAlertAdminImminent" name="checkAlertAdminImminent" checked="checked">
											Email <strong>admin</strong> on upcoming signup
										</label>
									</div>
								</div>

								<div class="form-group">
									<div class="text-right">
										<button type="submit" id="btnSheetInfoSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save
										</button>
										<button type="reset" id="btnSheetInfoCancel" class="btn btn-default btn-link btn-cancel">Cancel</button>
									</div>
								</div>
							</form>
						</div>
						<!-- End: Basic Sheet Info -->

						<!--Start: Sheet Access-->
						<div role="tabpanel" class="tab-pane fade" id="tabSheetAccess" aria-labelledby="tabSheetAccess">
							<div class="form-group">
								<strong>Who can see signups</strong><br />

								<div class="radio small col-sm-12">
									<label>
										<input type="radio" id="radioSignupPrivacy1" name="radioSignupPrivacy" checked="" value="0">
										Users can see who signed up when
									</label>
								</div>
								<div class="radio small col-sm-12">
									<label>
										<input type="radio" id="radioSignupPrivacy2" name="radioSignupPrivacy" checked="checked" value="1">
										Users can only see their own signups
									</label>
								</div>
							</div>

							<div class="form-group">
								<p><strong>Who can sign up</strong></p>

								<span class="small"><strong>People in these courses</strong><br /></span>

								<div id="access_by_instr_list" class="cb_list">

									<div class="checkbox small col-sm-12">
										<!-- TODO - iterator, DB saved values, and create unique 'id' values for elements -->
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-ABC
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-DEFG
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-PDQ
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-XYZ
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-ABC
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-DEFG
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-PDQ
										</label><br />
										<label>
											<input type="checkbox" id="access_by_course_50" name="access_by_course_50" permtype="bycourse" permval="50">
											Course 15S-XYZ
										</label><br />
									</div>

								</div>
								<!-- Bootstrap panel -->
								<div class="panel panel-default nopadding">
									<!--<div class="panel-heading small nopadding"><strong>People in courses taught by</strong></div>-->
									<div class="panel-body nopadding">
										<span class="small"><strong>People in courses taught by</strong><br /></span>

										<div class="checkbox small col-sm-12">
											<!-- TODO - iterator, DB saved values, and create unique 'id' values for elements -->
											<!--TODO ADD select box?-->
											<label>
												<input type="checkbox" id="access_by_instr_1554" name="access_by_instr_1554" permtype="byinstr" permval="1554">
												Daniel Aalberts (daalbert)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_course_5080" name="access_by_course_5080" permtype="bycourse" permval="5080">
												Sayaka Abe (sa9)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_course_9587" name="access_by_course_9587" permtype="bycourse" permval="9587">
												Hanane Aboulahmam (ha3)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_course_1234" name="access_by_course_1234" permtype="bycourse" permval="1234">
												Beverly Acha (09bda)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_instr_1554" name="access_by_instr_1554" permtype="byinstr" permval="1554">
												rDaniel Aalberts (daalbert)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_course_5080" name="access_by_course_5080" permtype="bycourse" permval="5080">
												rSayaka Abe (sa9)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_course_9587" name="access_by_course_9587" permtype="bycourse" permval="9587">
												rHanane Aboulahmam (ha3)
											</label><br />
											<label>
												<input type="checkbox" id="access_by_course_1234" name="access_by_course_1234" permtype="bycourse" permval="1234">
												rBeverly Acha (09bda)
											</label><br />
										</div>
									</div>
								</div>


							</div>
							<!--End: Sheet Access-->
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
			<?php
				if (!$isNewSheet) {
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
	</div>


	<?php


		echo "</div>"; // end: div#parent_container
		}

		require_once('../foot.php');
	?>

	<script type="text/javascript" src="../js/edit_sheet.js"></script>
