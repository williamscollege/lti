<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('calendar'));
	require_once('../app_head.php');


	// calendar frame
	if ($IS_AUTHENTICATED) {
		// TODO - underscore is CDN. download for more direct service?
		?>
		<link rel="stylesheet" href="../js/bootstrap-calendar-master/css/calendar.css">

		<div class="page-header">

			<div class="pull-right form-inline">
				<div class="btn-group">
					<button class="btn btn-primary btn-sm" data-calendar-nav="prev">&lt;&lt; Prev</button>
					<button class="btn btn-sm" data-calendar-nav="today">Today</button>
					<button class="btn btn-primary btn-sm" data-calendar-nav="next">Next &gt;&gt;</button>
				</div>
				<div class="btn-group">
					<!--					<button class="btn btn-warning btn-sm" data-calendar-view="year">Year</button>-->
					<!--					<button class="btn btn-default btn-link btn-sm" data-calendar-view="month">View Month</button>-->
					<!--					<button class="btn btn-warning btn-sm" data-calendar-view="week">Week</button>-->
					<!--					<button class="btn btn-warning btn-sm" data-calendar-view="day">Day</button>-->
				</div>
			</div>

			<h3></h3>
		</div>

		<div id="calendar"></div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.7.0/underscore-min.js"></script>
		<script type="text/javascript" src="../js/bootstrap-calendar-master/js/calendar.js"></script>
		<script type="text/javascript">

			$(document).ready(function () {
				"use strict";

				var options = {
					modal: '#events-modal',
//					modal_type: 'ajax'
//					, modal_title: function (e) {
//						return e.title
//					}
					events_source: 'calendar-events.php',
					//events_source: 'events.json.php',
					//events_source: function () { return []; }
					view: 'month',
					tmpl_path: '../js/bootstrap-calendar-master/tmpls/',
					tmpl_cache: false,
					day:
					<?php
						// todo - grab requested start date ... date_format(new DateTime($_REQUEST["inputSheetDateStart"] . " 00:00:00"), "Y-m-d H:i:s");
						echo "'" . date_format(new DateTime(date('Y-m-d')), "Y-m-d") . "'";
					?>,//'2013-03-12',
					onAfterEventsLoad: function (events) {
						if (!events) {
							return;
						}
						var list = $('#eventlist');
						list.html('');

						$.each(events, function (key, val) {
							$(document.createElement('li'))
								.html('<a href="' + val.url + '">' + val.title + '</a>')
								.appendTo(list);
						});
					},
					onAfterViewLoad: function (view) {
						$('.page-header h3').text(this.getTitle());
						$('.btn-group button').removeClass('active');
						$('button[data-calendar-view="' + view + '"]').addClass('active');
					},
					classes: {
						months: {
							general: 'label'
						}
					}
				};

				// ***************************
				// init calendar
				// ***************************
				var calendar = $('#calendar').calendar(options);


				// ***************************
				// onload actions
				// ***************************
				updateCalendarNavButtons();
				processCurrentCalendarCells();
				unbindDailyMode();


				// ***************************
				// calendar listeners
				// ***************************
				$('.btn-group button[data-calendar-nav]').each(function () {
					var $this = $(this);
					$this.click(function () {
						calendar.navigate($this.data('calendar-nav'));
					});
				});

				$('.btn-group button[data-calendar-view]').each(function () {
					var $this = $(this);
					$this.click(function () {
						calendar.view($this.data('calendar-view'));
					});
				});

				$('#first_day').change(function () {
					var value = $(this).val();
					value = value.length ? parseInt(value) : null;
					calendar.setOptions({first_day: value});
					calendar.view();
				});

				$('#language').change(function () {
					calendar.setLanguage($(this).val());
					calendar.view();
				});

				$('#events-modal .modal-header, #events-modal .modal-footer').click(function (e) {
					//e.preventDefault();
					//e.stopPropagation();
				});

				// button listeners: enable handlers to populate cells correctly
				$("BUTTON[data-calendar-nav]").click(function () {
					updateCalendarNavButtons();
					processCurrentCalendarCells();
					unbindDailyMode();
				});


				// ***************************
				// calendar functions
				// ***************************

				// prevent 'prev' and 'next' buttons from displaying months outside of sheet date span
				function updateCalendarNavButtons() {
					var calendarDateStart_ary = ($("#calendar span").first().attr("data-cal-date")).split('-');
					var calendarDateStart = new Date(calendarDateStart_ary[1] + '/' + calendarDateStart_ary[2] + '/' + calendarDateStart_ary[0]);

					var calendarDateEnd_ary = ($("#calendar span").last().attr("data-cal-date")).split('-');
					var calendarDateEnd = new Date(calendarDateEnd_ary[1] + '/' + calendarDateEnd_ary[2] + '/' + calendarDateEnd_ary[0]);

					var sheetDateStart = new Date($("#inputSheetDateStart").val());
					var sheetDateEnd = new Date($("#inputSheetDateEnd").val());

					//alert('calendarDateStart=' + calendarDateStart +  '\n' + 'sheetDateStart = ' + sheetDateStart);
					$("BUTTON[data-calendar-nav='prev']").prop("disabled", calendarDateStart <= sheetDateStart);
					$("BUTTON[data-calendar-nav='next']").prop("disabled", sheetDateEnd <= calendarDateEnd);
				}

				// iterate through each visible calendar cell
				function processCurrentCalendarCells() {
					$(".cal-cell").each(function (idx) {
						// console.log("processing cell " + idx);
						if (cellElementNeedsBlockInsertLink(this)) {
							insertNewBlockLinkIntoCell(this);
						}
						addExistingOpeingingToCell(this);
					});
				}

				// boolean check to determine if current calendar cell needs link to 'create openings'
				function cellElementNeedsBlockInsertLink(cellElement) {
					var currentCellDate_ary = ($(cellElement).find('span').attr("data-cal-date")).split('-');
					var currentCellDate = new Date(currentCellDate_ary[1] + '/' + currentCellDate_ary[2] + '/' + currentCellDate_ary[0]);

					var sheetDateStart = new Date($("#inputSheetDateStart").val());
					var sheetDateEnd = new Date($("#inputSheetDateEnd").val());
					return currentCellDate <= sheetDateEnd && currentCellDate >= sheetDateStart;
				}

				// insert link to 'create openings' in this calendar cell
				function insertNewBlockLinkIntoCell(cellElement) {
					$(cellElement).find('div').prepend('<a href="#" class="addOpeningLink" data-toggle="modal" data-target="#modal-create-opening" title="Create openings"><i class="glyphicon glyphicon-plus"></i></a>');
				}

				// display any existing openings within this calendar cell
				function addExistingOpeingingToCell(cellElement) {
					var cell_date_str = $(cellElement).find('span').attr("data-cal-date");
					//console.log(cell_date_str);
					// get from the list data all events for this date
					var openings = $(".opening-list-for-date[data-for-date=\"" + cell_date_str + "\"]").html();
					//console.dir(openings);

					// if there are any, copy them into this cell
					if (openings) {
						// TODO - Consider changing custom div overlay to instead use bootstrap popover (hover)
						$(cellElement).find('div').first().append('<div class="calendar-cell-openings"><span class="glyphicon glyphicon-list-alt" style="font-size: 24px;" aria-hidden="true"></span><div class="calendar-cell-openings-container">' + openings + '</div></div>');
					}
				}

				// keep calendar strictly in 'Month View' mode: unbind click & dblclick fxns that would open calendar into 'Daily View' (single day) mode
				function unbindDailyMode() {
					$('*[data-cal-date]').unbind("click");
					$('.cal-cell').unbind("dblclick");
				}

			});
		</script>



		<!-- Bootstrap Modal: Calendar Event Info -->
		<form action="../ajax_actions/ajax_actions.php" id="frmAjaxCalEventInfo" name="frmAjaxCalEventInfo" class="form-horizontal" role="form" method="post">
			<div id="events-modal" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajaxCalEventInfoLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-info">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
							</button>
							<h4 id="ajaxCalEventInfoLabel" class="modal-title">ABC</h4>
						</div>
						<div class="modal-body">
							<!--CONTENTS HERE-->
						</div>
						<div class="modal-footer">
							<button type="submit" id="btnAjaxCalEventInfoSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save</button>
							<button type="reset" id="btnAjaxCalEventInfoCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<!-- /Bootstrap Modal: Calendar Event Info -->

		<!-- Bootstrap Modal: Calendar Create Opening -->
		<form action="../ajax_actions/ajax_actions.php" id="frmAjaxOpening" name="frmAjaxOpening" class="form-horizontal" role="form" method="post">
			<div id="modal-create-opening" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajaxOpeningLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-info">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
							</button>
							<h4 id="ajaxOpeningLabel" class="modal-title">Creating openings on 12/23/2014</h4>
						</div>
						<div class="modal-body">
							<!-- TOGGLE LINK: Show Optional Fields -->
							<a href="#" id="link_show_optional_opening_fields" class="small" title="Show optional fields">Show optional fields</a>

							<div class="optional_opening_fields">
								<!-- TOGGLE LINK: Hide Optional Fields -->
								<a href="#" id="link_hide_optional_opening_fields" class="small" title="Hide optional fields">Hide optional fields</a>

								<div class="form-group form-group-sm">
									<label for="ajaxOpeningName" class="col-sm-3 control-label">Name</label>

									<div class="col-sm-9">
										<input type="text" id="ajaxOpeningName" name="ajaxOpeningName" class="form-control" placeholder="Opening name" value="" />
									</div>
								</div>
								<div class="form-group form-group-sm">
									<label for="ajaxOpeningDescription" class="col-sm-3 control-label">Description</label>

									<div class="col-sm-9">
										<textarea id="ajaxOpeningDescription" name="ajaxOpeningDescription" class="form-control" placeholder="Opening description" rows="1"></textarea>
									</div>
								</div>
								<div class="form-group form-group-sm">
									<label for="ajaxOpeningAdminNotes" class="col-sm-3 control-label">Admin&nbsp;Notes</label>

									<div class="col-sm-9">
										<textarea id="ajaxOpeningAdminNotes" name="ajaxOpeningAdminNotes" class="form-control" placeholder="Only the sheet admin can see these notes" rows="1"></textarea>
									</div>
								</div>
								<div class="form-group form-group-sm">
									<label for="ajaxOpeningLocation" class="col-sm-3 control-label">Location</label>

									<div class="col-sm-9">
										<input type="text" id="ajaxOpeningLocation" name="ajaxOpeningLocation" class="form-control" placeholder="Opening location" value="" />
									</div>
								</div>
							</div>
							<!-- end optional_opening_fields -->
							<div class="form-group form-group-sm">
								<label for="ajaxOpeningBeginTimeHour" class="col-sm-3 control-label">From</label>

								<div class="col-sm-9">
									<!-- START 'HOURS' -->
									<select id="ajaxOpeningBeginTimeHour" name="ajaxOpeningBeginTimeHour">
										<option value="1" selected="selected">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
									</select>:
									<!-- START 'MINUTES' -->
									<select id="ajaxOpeningBeginTimeMinute" name="ajaxOpeningBeginTimeMinute">
										<option value="0" selected="selected">00</option>
										<option value="5">05</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
										<option value="35">35</option>
										<option value="40">40</option>
										<option value="45">45</option>
										<option value="50">50</option>
										<option value="55">55</option>
									</select>
									<!-- START 'AM/PM' -->
									<select id="ajaxOpeningBeginTime_AMPM" name="ajaxOpeningBeginTime_AMPM">
										<option value="am">am</option>
										<option value="pm" selected="selected">pm</option>
									</select>

									<!-- TOGGLE LINKS: Openings by duration / time-range -->
									<a href="#" id="link_show_by_time_range" class="openings_by_time_range small" title="Switch to openings by duration">Switch to
										duration</a>
									<a href="#" id="link_show_by_duration" class="openings_by_duration small" title="Switch to openings by time range">Switch to time
										range</a>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label for="ajaxOpeningEndTimeHour" class="col-sm-3 control-label">To</label>

								<div class="col-sm-9">
									<!-- TOGGLED RESULT: openings by time range -->
									<div class="openings_by_time_range">
										<!-- START 'HOURS' -->
										<select id="ajaxOpeningEndTimeHour" name="ajaxOpeningEndTimeHour">
											<option value="1">1</option>
											<option value="2" selected="selected">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
											<option value="6">6</option>
											<option value="7">7</option>
											<option value="8">8</option>
											<option value="9">9</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
										</select>:
										<!-- START 'MINUTES' -->
										<select id="ajaxOpeningEndTimeMinute" name="ajaxOpeningEndTimeMinute">
											<option value="0" selected="selected">00</option>
											<option value="5">05</option>
											<option value="10">10</option>
											<option value="15">15</option>
											<option value="20">20</option>
											<option value="25">25</option>
											<option value="30">30</option>
											<option value="35">35</option>
											<option value="40">40</option>
											<option value="45">45</option>
											<option value="50">50</option>
											<option value="55">55</option>
										</select>
										<!-- START 'AM/PM' -->
										<select id="ajaxOpeningEndTimeMinute_AMPM" name="ajaxOpeningEndTimeMinute_AMPM">
											<option value="am">am</option>
											<option value="pm" selected="selected">pm</option>
										</select>
									</div>
									<!-- TOGGLED RESULT: openings by duration -->
									<div class="openings_by_duration">
										<select id="ajaxOpeningDurationEachOpening" name="ajaxOpeningDurationEachOpening">
											<option value="5" selected="selected">5</option>
											<option value="10">10</option>
											<option value="15">15</option>
											<option value="20">20</option>
											<option value="25">25</option>
											<option value="30">30</option>
											<option value="35">35</option>
											<option value="40">40</option>
											<option value="45">45</option>
											<option value="50">50</option>
											<option value="55">55</option>
											<option value="60">60</option>
											<option value="65">65</option>
											<option value="70">70</option>
											<option value="75">75</option>
											<option value="80">80</option>
											<option value="85">85</option>
											<option value="90">90</option>
										</select> minutes
									</div>
								</div>
							</div>

							<div class="form-group form-group-sm">
								<label for="ajaxOpeningNumOpeningsInTimeRange" class="col-sm-3 control-label">#&nbsp;Openings</label>

								<div class="col-sm-9">
									<select id="ajaxOpeningNumOpeningsInTimeRange" name="ajaxOpeningNumOpeningsInTimeRange">
										<option value="1" selected="selected">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
										<option value="24">24</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label for="ajaxOpeningNumSignupsPerOpening" class="col-sm-3 control-label">&nbsp;Signups/Opening</label>

								<div class="col-sm-9">
									<select id="ajaxOpeningNumSignupsPerOpening" name="ajaxOpeningNumSignupsPerOpening">
										<option value="-1">unlimited</option>
										<option value="1" selected="selected">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
										<option value="24">24</option>
										<option value="25">25</option>
										<option value="26">26</option>
										<option value="27">27</option>
										<option value="28">28</option>
										<option value="29">29</option>
										<option value="30">30</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label for="ajaxOpeningRepeaterControls" class="col-sm-3 control-label">Repeating?</label>

								<div class="col-sm-9">
									<div id="ajaxOpeningRepeaterControls">

										<div id="chooseRepeatType">
											<div class="radio">
												<label for="radioOpeningRepeatRate1">
													<input id="radioOpeningRepeatRate1" name="openingRepeatRate" value="1" checked="checked" type="radio" /> Only on
													2014-12-23
												</label>
											</div>
											<div class="radio">
												<label for="radioOpeningRepeatRate2">
													<input id="radioOpeningRepeatRate2" name="openingRepeatRate" value="2" type="radio" /> Repeat on days of the week
												</label>
											</div>
											<div class="radio">
												<label for="radioOpeningRepeatRate3">
													<input id="radioOpeningRepeatRate3" name="openingRepeatRate" value="3" type="radio" /> Repeat on days of the month
												</label>
											</div>
										</div>

										<div id="repeatWeekdayChooser">
											<input name="repeat_dow_sun" id="repeat_dow_sun" value="0" type="hidden" />
											<input name="repeat_dow_mon" id="repeat_dow_mon" value="0" type="hidden" />
											<input name="repeat_dow_tue" id="repeat_dow_tue" value="0" type="hidden" />
											<input name="repeat_dow_wed" id="repeat_dow_wed" value="0" type="hidden" />
											<input name="repeat_dow_thu" id="repeat_dow_thu" value="0" type="hidden" />
											<input name="repeat_dow_fri" id="repeat_dow_fri" value="0" type="hidden" />
											<input name="repeat_dow_sat" id="repeat_dow_sat" value="0" type="hidden" />
											<input id="btn_mon" value="MON" class="toggler_dow btn btn-default btn-xs" type="button" />
											<input id="btn_tue" value="TUE" class="toggler_dow btn btn-default btn-xs" type="button" />
											<input id="btn_wed" value="WED" class="toggler_dow btn btn-default btn-xs" type="button" />
											<input id="btn_thu" value="THU" class="toggler_dow btn btn-success btn-xs" type="button" />
											<input id="btn_fri" value="FRI" class="toggler_dow btn btn-default btn-xs" type="button" /><br />
											<input id="btn_sat" value="SAT" class="toggler_dow btn btn-default btn-xs" type="button" />
											<input id="btn_sun" value="SUN" class="toggler_dow btn btn-default btn-xs" type="button" />
										</div>

										<div id="repeatMonthdayChooser">
											<input name="repeat_dom_1" id="repeat_dom_1" value="0" type="hidden" />
											<input id="btn_dom_1" value="1" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_2" id="repeat_dom_2" value="0" type="hidden" />
											<input id="btn_dom_2" value="2" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_3" id="repeat_dom_3" value="0" type="hidden" />
											<input id="btn_dom_3" value="3" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_4" id="repeat_dom_4" value="0" type="hidden" />
											<input id="btn_dom_4" value="4" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_5" id="repeat_dom_5" value="0" type="hidden" />
											<input id="btn_dom_5" value="5" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_6" id="repeat_dom_6" value="0" type="hidden" />
											<input id="btn_dom_6" value="6" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_7" id="repeat_dom_7" value="0" type="hidden" />
											<input id="btn_dom_7" value="7" class="toggler_dom btn btn-default btn-xs" type="button" />
											<br />
											<input name="repeat_dom_8" id="repeat_dom_8" value="0" type="hidden" />
											<input id="btn_dom_8" value="8" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_9" id="repeat_dom_9" value="0" type="hidden" />
											<input id="btn_dom_9" value="9" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_10" id="repeat_dom_10" value="1" type="hidden" />
											<input id="btn_dom_10" value="10" class="toggler_dom btn btn-success btn-xs" type="button" />
											<input name="repeat_dom_11" id="repeat_dom_11" value="1" type="hidden" />
											<input id="btn_dom_11" value="11" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_12" id="repeat_dom_12" value="0" type="hidden" />
											<input id="btn_dom_12" value="12" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_13" id="repeat_dom_13" value="0" type="hidden" />
											<input id="btn_dom_13" value="13" class="toggler_dom btn btn-success btn-xs" type="button" />
											<input name="repeat_dom_14" id="repeat_dom_14" value="0" type="hidden" />
											<input id="btn_dom_14" value="14" class="toggler_dom btn btn-default btn-xs" type="button" />
											<br />
											<input name="repeat_dom_15" id="repeat_dom_15" value="0" type="hidden" />
											<input id="btn_dom_15" value="15" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_16" id="repeat_dom_16" value="0" type="hidden" />
											<input id="btn_dom_16" value="16" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_17" id="repeat_dom_17" value="0" type="hidden" />
											<input id="btn_dom_17" value="17" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_18" id="repeat_dom_18" value="1" type="hidden" />
											<input id="btn_dom_18" value="18" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_19" id="repeat_dom_19" value="0" type="hidden" />
											<input id="btn_dom_19" value="19" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_20" id="repeat_dom_20" value="0" type="hidden" />
											<input id="btn_dom_20" value="20" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_21" id="repeat_dom_21" value="0" type="hidden" />
											<input id="btn_dom_21" value="21" class="toggler_dom btn btn-default btn-xs" type="button" />
											<br />
											<input name="repeat_dom_22" id="repeat_dom_22" value="0" type="hidden" />
											<input id="btn_dom_22" value="22" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_23" id="repeat_dom_23" value="0" type="hidden" />
											<input id="btn_dom_23" value="23" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_24" id="repeat_dom_24" value="0" type="hidden" />
											<input id="btn_dom_24" value="24" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_25" id="repeat_dom_25" value="0" type="hidden" />
											<input id="btn_dom_25" value="25" class="toggler_dom btn btn-success btn-xs" type="button" />
											<input name="repeat_dom_26" id="repeat_dom_26" value="0" type="hidden" />
											<input id="btn_dom_26" value="26" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_27" id="repeat_dom_27" value="0" type="hidden" />
											<input id="btn_dom_27" value="27" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_28" id="repeat_dom_28" value="0" type="hidden" />
											<input id="btn_dom_28" value="28" class="toggler_dom btn btn-default btn-xs" type="button" />
											<br />
											<input name="repeat_dom_29" id="repeat_dom_29" value="0" type="hidden" />
											<input id="btn_dom_29" value="29" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_30" id="repeat_dom_30" value="0" type="hidden" />
											<input id="btn_dom_30" value="30" class="toggler_dom btn btn-default btn-xs" type="button" />
											<input name="repeat_dom_31" id="repeat_dom_31" value="0" type="hidden" />
											<input id="btn_dom_31" value="31" class="toggler_dom btn btn-default btn-xs" type="button" />
										</div>

										<div id="repeatUntilDate">
											<label for="ajaxOpeningUntilDate" style="font-weight: normal; border-radius: 0;">
												<strong>until</strong> <input type="text" id="ajaxOpeningUntilDate" name="ajaxOpeningUntilDate" class="form-inline" placeholder="mm/dd/yyyy" maxlength="10" value="02/03/2015" />
												<!--value="<?php /*echo $s ? date_format(new DateTime($s->date_closes), "m/d/Y") : ''; */?>" />-->
											</label>
										</div>
									</div>
									<!-- end ajaxOpeningRepeaterControls -->
								</div>
							</div>


						</div>
						<div class="modal-footer">
							<button type="submit" id="btnAjaxOpeningSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save</button>
							<button type="reset" id="btnAjaxOpeningCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<!-- /Bootstrap Modal: Calendar Create Opening -->

		<!-- Bootstrap Modal: Calendar Manage Opening -->
		<form action="../ajax_actions/ajax_actions.php" id="frmAjaxCalManageOpening" name="frmAjaxCalManageOpening" class="form-horizontal" role="form" method="post">
			<div id="modal-edit-opening" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajaxCalManageOpeningLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-info">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
							</button>
							<h4 id="ajaxCalManageOpeningLabel" class="modal-title">456</h4>
						</div>
						<div class="modal-body">
							<!--CONTENTS HERE-->
						</div>
						<div class="modal-footer">
							<button type="submit" id="btnAjaxCalManageOpeningSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save</button>
							<button type="reset" id="btnAjaxCalManageOpeningCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<!-- /Bootstrap Modal: Calendar Manage Opening -->

		<script type="text/javascript" src="../js/create_opening.js"></script>

	<?php
	}
?>

