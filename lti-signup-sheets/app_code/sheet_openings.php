<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('sheet_openings'));
	require_once('../app_head.php');

	// load calendar setup functions
	require_once('calendar_setup.php');


	$s = '';
	if (isset($_REQUEST["sheet"]) && $_REQUEST["sheet"] > 0) {
		// ***************************
		// fetch sheet
		// ***************************
		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
	}

	if (!$s->matchesDb) {
		echo 'no matching record found in database';
		exit;
	}


	if ($IS_AUTHENTICATED) {
		echo "<div id=\"parent_container\">"; // start: div#parent_container
		?>
		<div class="container">
			<div class="row">
				<!-- Basic Sheet Info / Sheet Access -->
				<div class="col-sm-5">
					<?php
						echo "<h3>" . $s->name . "</h3>";
						echo "<p>" . $s->description . "</p>";
					?>
					<a id="signup_help_link" href="#" title="Show instructions">Show instructions</a>
					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<div id="signup_help_text">
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
							<div id="signup_opening_details"></div>
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
										//renderCalendarWidget(c1,c2,c3,axv);
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

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/edit_sheet.js"></script>
