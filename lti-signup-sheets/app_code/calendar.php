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
					<button id="wms_force_btn_to_link" class="btn btn-default btn-link btn-sm" data-calendar-view="month">View Month</button>
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

				var calendar = $('#calendar').calendar(options);

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
				$("BUTTON[data-calendar-nav],#wms_force_btn_to_link").click(function () {
					updateCalendarNavButtons();
					processCurrentCalendarCells();
					customizeUI();
				});

				// force "View Month" to appear as a link, not a button
				function customizeUI(){
					$("#wms_force_btn_to_link").removeClass("active");
				}

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

				function processCurrentCalendarCells() {
					$(".cal-cell").each(function (idx) {
//					console.log("processing cell " + idx);
						if (cellElementNeedsBlockInsertLink(this)) {
							insertNewBlockLinkIntoCell(this);
						}
						addExistingOpeingingToCell(this);
					});
				}

				function cellElementNeedsBlockInsertLink(cellElement) {
					var currentCellDate_ary = ($(cellElement).find('span').attr("data-cal-date")).split('-');
					var currentCellDate = new Date(currentCellDate_ary[1] + '/' + currentCellDate_ary[2] + '/' + currentCellDate_ary[0]);

					var sheetDateStart = new Date($("#inputSheetDateStart").val());
					var sheetDateEnd = new Date($("#inputSheetDateEnd").val());
					return currentCellDate <= sheetDateEnd && currentCellDate >= sheetDateStart;
				}

				function insertNewBlockLinkIntoCell(cellElement) {
					$(cellElement).find('div').prepend('<a href="#" class="addOpeningLink" data-toggle="modal" data-target="#modal-create-opening" title="Create opening"><i class=\"glyphicon glyphicon-plus\"></i></a>');
				}

				function addExistingOpeingingToCell(cellElement) {
					var cell_date_str = $(cellElement).find('span').attr("data-cal-date");
					//console.log(cell_date_str);
					// get from the list data all events for this date
//					var openings = ($(".opening-list-for-date[data-for-date=\""+cell_date_str+"\"]"));
					var openings = $(".opening-list-for-date[data-for-date=\"" + cell_date_str + "\"]").html();
					//console.dir(openings);

					// if there are any, copy them into this cell
					if (openings) {
						$(cellElement).find('div').first().append('<div class="calendar-cell-openings"><div class="calendar-cell-openings-container">' + openings + '</div></div>');
					}
				}


				//////////////// TODO - Complete or abandon: ATTEMPT TO STOP PROPAGATION OF DAY DBLCLICK and SingleClick on Numeral ////////////////
				// todo - viewing empty calendar cells in 'day mode' creates a cascade JS errors (.first(...).attr(...)
				// keep calendar in month view; this hack removes ability for calendar to display a single day
				$('*[data-cal-date]').click(function () {
					console.log('clicked date');
					return false;
//					var view = $(this).data('cal-view');
//					self.options.day = $(this).data('cal-date');
//					self.view(view);
				});
				$('.cal-cell').on("dblclick", function () {
				//$('.cal-cell').dblclick(function () {
					console.log('clicked cal cell2');
					alert('trying to revert...');
					$("button[data-calendar-view='month']").click();
//					$('.btn-group button[data-calendar-view]').each(function () {
//						var $this = $(this);
//						$this.click(function () {
//							calendar.view($this.data('calendar-view')).stop();
//						});
//					});
//					var view = $('[data-cal-date]', this).data('cal-view');
//					self.options.day = $('[data-cal-date]', this).data('cal-date');
//					self.view('month');
				});
//				this['_update_' + this.options.view]();
//				this._update_modal();
//				function stopme() {
//					alert('trying to just stop...');
//					return false;
//				}
				//////////////// ATTEMPT TO STOP PROPAGATION OF DAY DBLCLICK and SingleClick on Numeral ////////////////


				// ***************************
				// onload actions
				// ***************************

				updateCalendarNavButtons();
				processCurrentCalendarCells();
				customizeUI()

				// dkc hacks: customize event icons
//				$("a[data-event-class='event-important']").removeClass("event").removeClass("event-important").html("<i class=\"glyphicon glyphicon-plus\"></i> text");
				// attempt to change icon.. but it's too small. use image instead?
				//$(".calendar-cell-openings").prepend('<a href="#"><i class="glyphicon glyphicon-list"></i></a>');

			});
		</script>



		<!-- Bootstrap Modal: Calendar Event Info -->
		<div class="modal fade" id="events-modal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Default events-modal</h3>
					</div>
					<div class="modal-body" style="height: 400px">
					</div>
					<div class="modal-footer">
						<a href="#" data-dismiss="modal" class="btn">Close</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Bootstrap Modal: Calendar Event Info -->
		<div class="modal fade" id="modal-create-opening">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>modal-create-opening</h3>
					</div>
					<div class="modal-body" style="height: 400px">
					</div>
					<div class="modal-footer">
						<a href="#" data-dismiss="modal" class="btn">Close</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Bootstrap Modal: Calendar Event Info -->
		<div class="modal fade" id="modal-manage-opening">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>modal-manage-opening</h3>
					</div>
					<div class="modal-body" style="height: 400px">
					</div>
					<div class="modal-footer">
						<a href="#" data-dismiss="modal" class="btn">Close</a>
					</div>
				</div>
			</div>
		</div>

	<?php
	}
?>

