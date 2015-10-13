<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('sheet_openings_signup'));
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		#------------------------------------------------#
		# begin security: check if access allowed to this page
		#------------------------------------------------#
		if ((!isset($_REQUEST["sheet"])) || (!is_numeric($_REQUEST["sheet"])) || ($_REQUEST["sheet"] <= 0)) {
			// error: querystring 'sheet' must exist and be an integer
			util_displayMessage('error', 'Invalid or missing sheet request');
			require_once(dirname(__FILE__) . '/../foot.php');
			exit;
		}
		elseif (!$USER->isUserAllowedToAccessSheet($_REQUEST["sheet"])) {
			// error: must have access to signup on this sheet
			util_displayMessage('error', 'You do not have permission to signup on this sheet');
			require_once(dirname(__FILE__) . '/../foot.php');
			exit;
		}


		// load calendar setup functions
		require_once(dirname(__FILE__) . '/calendar_setup.php');


		// ***************************
		// fetch sheet
		// ***************************
		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);

		if (!$s->matchesDb) {
			util_displayMessage('error', 'No matching sheet record found in database');
			require_once(dirname(__FILE__) . '/../foot.php');
			exit;
		}

		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $s->sheetgroup_id], $DB);
		if (!$sg->matchesDb) {
			util_displayMessage('error', 'No matching sheetgroup record found in database');
			require_once(dirname(__FILE__) . '/../foot.php');
			exit;
		}

		echo "<div id=\"content_container\">"; // begin: div#content_container


		// ***************************
		// fetch available openings
		// ***************************
		$USER->cacheMyAvailableSheetOpenings();
		// util_prePrintR($USER->sheet_openings_all); // debugging


		// ***************************
		// breadcrumbs: begin
		// ***************************
		$available_sheets = "<select id=\"breadcrumbs_select_list\" class=\"input-sm\">";
		foreach ($USER->sheet_openings_all as $sheet) {
			// is selected?
			$str_selected = "";
			if ($sheet['s_id'] == $s->sheet_id) {
				$str_selected = " selected=\"selected\" ";
			}
			// option: list each sheet
			$available_sheets .= "<option value=\"" . $sheet['s_id'] . "\"" . $str_selected . ">" . htmlentities($sheet['s_name'], ENT_QUOTES, 'UTF-8') . "</option>";
		}
		$available_sheets .= "</select>";
		echo "<h5 class=\"small\"><a href=\"" . APP_ROOT_PATH . "/app_code/sheet_openings_all.php\" title=\"" . ucfirst(util_lang('sheet_openings_all')) . "\">" . ucfirst(util_lang('sheet_openings_all')) . "</a>&nbsp;&gt;&nbsp;" . $available_sheets . "</h5>";
		// breadcrumbs: end
		?>

		<div class="container">
			<div class="row">
				<!-- Basic Sheet Info / Sheet Access -->
				<div class="col-sm-5">
					<div id="sus_signup_on_sheet_info" class="small">
						<p>&nbsp;</p>

						<p>
						<h5><strong><?php echo htmlentities($s->name, ENT_QUOTES, 'UTF-8'); ?></strong></h5>
						<?php echo htmlentities($s->description, ENT_QUOTES, 'UTF-8'); ?><br />
						Group: <?php echo htmlentities($sg->name, ENT_QUOTES, 'UTF-8'); ?>
						</p>

						<!-- alert will display only if a limit has been reached (meaning: no more signups are available) -->
						<div id="toggle_usage_alert">
							<?php echo $s->renderAsHtmlUsageAlert(); ?>
						</div>

						<p><a id="link_for_usage_quotas" href="#" title="Usage details">Show usage details</a></p>

						<div id="toggle_usage_quotas" class="hidden">
							<?php echo $s->renderAsHtmlUsageDetails(); ?>
						</div>
					</div>

					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<div id="toggle_openings_instructions">
								<p>To the right is a calendar showing all openings for this sheet. Hover over an openings icon
									<span class="glyphicon glyphicon-list-alt" aria-hidden="true" style="font-size: 24px;"></span>
									to see a summary of the openings on that day, and click on that icon to
									get a more detailed list (which replaces this help text).
									Click <a href="#" class="wms-demo-add" title="Sign up"><i class="glyphicon glyphicon-plus"></i>&nbsp;Signup</a>
									to add yourself or
									<a href="#" class="wms-demo-delete" title="Cancel signup"><i class="glyphicon glyphicon-remove"></i>&nbsp;Cancel&nbsp;signup</a>
									to remove yourself from a given opening.
								</p>

								<p>
									To see all the openings for this sheet in a text-based list, click the "List View" tab above.
								</p>
							</div>
							<div id="display_opening_signup_details"></div>
						</div>
					</div>
				</div>
				<div class="col-sm-1">&nbsp;</div>
				<!-- Calendar View / List View -->
				<div class="col-sm-6">
					<div class="row">
						<!-- show spinner icon (visual placeholder) until DOM content (hidden) has fully loaded -->
						<span id="spinner_calendarTabs"><img height="39" width="36" src="../img/spinner.gif" />&nbsp;Calendar View</span>

						<div id="content_calendarTabs" class="tab-container hidden" role="tabpanel" data-example-id="set2">
							<ul id="boxOpenings" class="nav nav-tabs" role="tablist">
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
								<div role="tabpanel" class="tab-pane fade active in" id="tabOpeningsCalendarView" aria-labelledby="tabOpeningsCalendarView">

									<?php
										renderCalendarWidget_DOSIGNUP();
										//e.g. renderCalendarWidget(c1,c2,c3,axv);
									?>

								</div>
								<!-- End: Calendar View -->

								<!-- Begin: List View -->
								<div role="tabpanel" id="tabOpeningsListView" class="tab-pane fade PrintArea wms_print_OpeningSignup" aria-labelledby="tabOpeningsListView">
									<div id="buttons_list_openings">
										<!-- PrintArea: Print a specific div -->
										<a href="#" class="wmsPrintArea" data-what-area-to-print="wms_print_OpeningSignup" title="Print only this section"><i class="glyphicon glyphicon-print"></i></a>
										<!-- TOGGLE LINK: Show optional history -->
										<a href="#" id="link_for_history_openings" type="button" class="btn btn-link btn-xs" title="toggle history">show
											history</a>
									</div>

									<div id="openings-list-container">
										<?php
											$s->cacheOpenings();
											$flagFutureOpeningSignup = FALSE;
											$lastOpeningDate         = '';
											$daysOpenings            = [];
											$todayYmd                = explode(' ', util_currentDateTimeString())[0];

											foreach ($s->openings as $opening) {
												$curOpeningDate = explode(' ', $opening->begin_datetime)[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														// determine if signups are public/private, and which, if any, controls or text should be displayed
														$op->cacheSignups();
														echo $op->renderAsHtmlOpeningWithLimitedControls($USER->user_id) . "\n";
													}

													if ($lastOpeningDate) {
														echo '</div>' . "\n";
													}

													// determine: past/present/future
													$relative_time_class = 'in-the-past toggle_opening_history';
													if ($curOpeningDate == $todayYmd) {
														$relative_time_class     = 'in-the-present';
														$flagFutureOpeningSignup = TRUE; // set boolean flag
													}
													elseif ($curOpeningDate > $todayYmd) {
														$relative_time_class     = 'in-the-future';
														$flagFutureOpeningSignup = TRUE; // set boolean flag
													}
													echo '<div class="opening-list-for-date ' . $relative_time_class . '" data-for-date="' . $curOpeningDate . '"><h4>' . date_format(new DateTime($opening->begin_datetime), "m/d/Y") . '</h4>';
													$daysOpenings = [];
												}
												array_push($daysOpenings, $opening);

												$lastOpeningDate = $curOpeningDate;
											}

											// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
											foreach ($daysOpenings as $op) {
												// determine if signups are public/private, and which, if any, controls or text should be displayed
												$op->cacheSignups();
												echo $op->renderAsHtmlOpeningWithLimitedControls($USER->user_id) . "\n";
											}
											echo '</div>' . "\n";

											// display placeholder message
											if (!$flagFutureOpeningSignup) {
												echo '<div class="opening-list-for-date in-the-future"><br /><em>There are no openings for future dates.</em></div>';
											}
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
			</div>
		</div> <!-- end: div.container -->

		<?php
		echo "</div>"; // end: div#content_container
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once(dirname(__FILE__) . '/../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/sheets_edit_one.js"></script>
