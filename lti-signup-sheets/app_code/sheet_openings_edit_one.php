<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('sheet_openings_edit_one'));
	require_once('../app_head.php');

	//###############################################################
	// begin security: check if access allowed to this page
	//###############################################################
	if ((!isset($_REQUEST["sheet"])) || (!is_numeric($_REQUEST["sheet"])) || ($_REQUEST["sheet"] <= 0)) {
		// error: querystring 'sheet' must exist and be an integer
		util_displayMessage('error', 'Invalid or missing sheet request');
		require_once('../foot.php');
		exit;
	}
	// TODO -- NEED TO IMPLEMENT THIS !!!!!
	//	elseif (!$USER->isUserAllowedToSignupForOpening($_REQUEST["sheet"])) {
	//		// error: must have access to manage this sheet
	//		util_displayMessage('error', 'You do not have permission to edit that sheet');
	//		require_once('../foot.php');
	//		exit;
	//	}


	// load calendar setup functions
	require_once('calendar_setup.php');


	$s = '';
	if ((isset($_REQUEST["sheet"])) && (is_numeric($_REQUEST["sheet"])) && ($_REQUEST["sheet"] > 0)) {
		// ***************************
		// fetch sheet
		// ***************************
		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
	}

	if (!$s->matchesDb) {
		util_displayMessage('error', 'No matching record found in database');
		require_once('../foot.php');
		exit;
	}

	// helper function: usage details
	function sus_grammatical_max_signups($num) {
		if (!$num || $num < 0) {
			return 'an unlimited number of signups';
		}
		else {
			if ($num == 1) {
				return "1 signup";
			}
			else {
				return "$num signups";
			}
		}
	}

	if ($IS_AUTHENTICATED) {
		echo "<div id=\"parent_container\">"; // start: div#parent_container
		?>
		<div class="container">
			<div class="row">
				<!-- Basic Sheet Info / Sheet Access -->
				<div class="col-sm-5">
					<div id="sus_signup_on_sheet_info" class="small">
						<?php
							// determine separate counts of signups within: sheetgroup; this sheet
							$fetch_signups_in_openings_all    = [];
							$fetch_signups_in_openings_future = [];
							$fetch_signups_in_sheet_all       = [];
							$fetch_signups_in_sheet_future    = [];

							$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $s->sheetgroup_id], $DB);

							// 1) determine count of signups on sheets in this sheetgroup
							$sheets_in_sg         = SUS_Sheet::getAllFromDb(['sheetgroup_id' => $sg->sheetgroup_id], $DB);
							$list_sheet_ids_in_sg = Db_Linked::arrayOfAttrValues($sheets_in_sg, 'sheet_id');

							$openings_in_sg_all         = SUS_Opening::getAllFromDb(['sheet_id' => $list_sheet_ids_in_sg], $DB);
							$list_opening_ids_in_sg_all = Db_Linked::arrayOfAttrValues($openings_in_sg_all, 'opening_id');

							$openings_in_sg_future         = SUS_Opening::getAllFromDb(['sheet_id' => $list_sheet_ids_in_sg, 'begin_datetime >=' => util_currentDateTimeString_asMySQL()], $DB);
							$list_opening_ids_in_sg_future = Db_Linked::arrayOfAttrValues($openings_in_sg_future, 'opening_id');

							if ($list_opening_ids_in_sg_all) {
								$fetch_signups_in_openings_all = SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_sg_all, 'signup_user_id' => $USER->user_id], $DB);
							}
							if ($list_opening_ids_in_sg_future) {
								$fetch_signups_in_openings_future = SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_sg_future, 'signup_user_id' => $USER->user_id], $DB);
							}

							// 2) determine count of signups on this sheet
							$openings_in_one_sheet_all         = SUS_Opening::getAllFromDb(['sheet_id' => $s->sheet_id], $DB);
							$list_opening_ids_in_one_sheet_all = Db_Linked::arrayOfAttrValues($openings_in_one_sheet_all, 'opening_id');

							$openings_in_one_sheet_future         = SUS_Opening::getAllFromDb(['sheet_id' => $s->sheet_id, 'begin_datetime >=' => util_currentDateTimeString_asMySQL()], $DB);
							$list_opening_ids_in_one_sheet_future = Db_Linked::arrayOfAttrValues($openings_in_one_sheet_future, 'opening_id');

							if ($list_opening_ids_in_one_sheet_all) {
								$fetch_signups_in_sheet_all = SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_one_sheet_all, 'signup_user_id' => $USER->user_id], $DB);
							}
							if ($list_opening_ids_in_one_sheet_future) {
								$fetch_signups_in_sheet_future = SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_one_sheet_future, 'signup_user_id' => $USER->user_id], $DB);
							}
						?>

						<h3><?php echo $s->name; ?></h3>
						<?php echo $s->description; ?><br />
						Group: <?php echo $sg->name; ?><br />

						<p><a id="link_for_usage_quotas" href="#" title="Usage details">Show usage details</a></p>

						<div id="toggle_usage_quotas" class="hidden">
							You may use
							<span class="badge"><?php echo(sus_grammatical_max_signups($sg->max_g_total_user_signups)); ?></span> across all sheets in this
							group;
							<span class="badge"><?php echo(sus_grammatical_max_signups($sg->max_g_pending_user_signups)); ?></span> may be for future times.
							Currently you have used
							<span class="badge"> <?php echo count($fetch_signups_in_openings_all) + 0; ?></span> in this group,
							<span class="badge"><?php echo count($fetch_signups_in_openings_future) + 0; ?></span> of which are in the future.
							You may have
							<span class="badge"><?php echo(sus_grammatical_max_signups($s->max_total_user_signups)); ?></span> on this sheet;
							<span class="badge"><?php echo(sus_grammatical_max_signups($s->max_pending_user_signups)); ?></span> may be for future times.
							Currently you have
							<span class="badge"><?php echo count($fetch_signups_in_sheet_all) + 0; ?></span> on this sheet,
							<span class="badge"><?php echo count($fetch_signups_in_sheet_future) + 0; ?></span> of which are in the future.
						</div>
						<p><a id="link_for_openings_instructions" class="hidden" href="#" title="Instructions">Show instructions</a></p>
					</div>


					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<div id="toggle_openings_instructions">
								<p>To the right is a calendar showing all openings for this sheet. Hover over an openings icon
									<span class="glyphicon glyphicon-list-alt" aria-hidden="true" style="font-size: 24px;"></span>
									to see a summary of the openings on that day, and click on that icon to
									get a more detailed list (which replaces this help text).
									Click <a href="#" title="Sign up"><i class="glyphicon glyphicon-plus"></i>&nbsp;Signup</a>
									to add yourself or
									<a href="#" class="wms-custom-delete" title="Cancel signup"><i class="glyphicon glyphicon-remove"></i>&nbsp;Cancel&nbsp;signup</a>
									to remove yourself from a given opening.
								</p>

								<p>
									To see all the openings for this sheet in a text-based list, click the "List Openings" tab above.
								</p>
							</div>
							<div id="display_opening_signup_details"></div>
						</div>
					</div>
				</div>
				<div class="col-sm-1">&nbsp;</div>
				<!-- Calendar Openings / List Openings -->
				<div class="col-sm-6">
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

									<?php
										renderCalendarWidget_DOSIGNUP();
										//e.g. renderCalendarWidget(c1,c2,c3,axv);
									?>

								</div>
								<!--End: Calendar Openings -->

								<!--Start: List Openings -->
								<div role="tabpanel" class="tab-pane fade" id="tabOpeningsList" aria-labelledby="tabOpeningsList">
									<a href="#" id="scroll-to-todayish-openings" type="button" class="btn btn-success btn-small">scroll to current date</a>

									<div id="openings-list-container">

										<?php
											$s->cacheOpenings();
											$lastOpeningDate = '';
											$daysOpenings    = [];
											$todayYmd        = explode(' ', util_currentDateTimeString())[0];
											foreach ($s->openings as $opening) {
												$curOpeningDate = explode(' ', $opening->begin_datetime)[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														// show 'self' controls only on current and future dates (not past dates)

														//date_format(new DateTime($this->begin_datetime), "h:i A") . ' - ' . date_format(new DateTime($this->end_datetime), "h:i A")
														//echo '$op->begin_datetime : util_currentDateTimeString_asMySQL = ' . $op->begin_datetime . ':' . util_currentDateTimeString_asMySQL();
														if ($op->begin_datetime >= util_currentDateTimeString_asMySQL()) {
															echo $op->renderAsHtmlShortWithLimitedControls($USER->user_id) . "\n";
														}
														else {
															echo $op->renderAsHtmlShortWithNoControls($USER->user_id) . "\n";
														}
													}

													if ($lastOpeningDate) {
														echo '</div>' . "\n";
													}

													// determine: past/present/future
													$relative_time_class = 'in-the-past';
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
												// show 'self' controls only on current and future dates (not past dates)
												if ($op->begin_datetime >= util_currentDateTimeString_asMySQL()) {
													echo $op->renderAsHtmlShortWithLimitedControls($USER->user_id) . "\n";
												}
												else {
													echo $op->renderAsHtmlShortWithNoControls($USER->user_id) . "\n";
												}
											}
											echo '</div>' . "\n";
										?>

									</div>
								</div>
								<!--End: List Openings -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- end: div.container -->

		<?php
		echo "</div>"; // end: div#parent_container
	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/sheets_edit_one.js"></script>
