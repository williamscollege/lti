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
					<button class="btn btn-primary" data-calendar-nav="prev">&lt;&lt; Prev</button>
					<button class="btn" data-calendar-nav="today">Today</button>
					<button class="btn btn-primary" data-calendar-nav="next">Next &gt;&gt;</button>
				</div>
				<div class="btn-group">
					<button class="btn btn-warning" data-calendar-view="year">Year</button>
					<button class="btn btn-warning active" data-calendar-view="month">Month</button>
					<button class="btn btn-warning" data-calendar-view="week">Week</button>
					<button class="btn btn-warning" data-calendar-view="day">Day</button>
				</div>
			</div>

			<h3>March 2013</h3>
			<small>To see example with events navigate to march 2013</small>
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

				// dkc hacks
				// customize event icons
				$("a[data-event-class='event-important']").removeClass("event").removeClass("event-important").html("<i class=\"glyphicon glyphicon-plus\"></i> text");


				function processCurrentCalendarCells() {
					$(".cal-cell").each(function (idx) {
//					console.log("processing cell " + idx);
						if (cellElementNeedsBlockInsertLink(this)) {
							insertNewBlockLinkIntoCell(this);
						}
					});
				}

				function cellElementNeedsBlockInsertLink(cellElement) {
//					var calendarDateStart = new Date(Date.parse($("#calendar span").first().attr("data-cal-date") + 'T00:00:00Z'));
//					var calendarDateEnd = new Date(Date.parse($("#calendar span").last().attr("data-cal-date") + 'T00:00:00Z'));
					var currentCellDate = new Date(Date.parse($(cellElement).find('span').attr("data-cal-date") + 'T00:00:00Z'));
					var sheetDateStart = new Date($("#inputSheetDateStart").val());
					var sheetDateEnd = new Date($("#inputSheetDateEnd").val());
					return currentCellDate <= sheetDateEnd && currentCellDate >= sheetDateStart;
				}

				function insertNewBlockLinkIntoCell(cellElement) {
//					$(cellElement).css("background-color", "green");
					$(cellElement).find('div').prepend("<a href='#' class='addOpeningLink' data-toggle='modal' data-target='#modal-create-opening'><i class=\"glyphicon glyphicon-plus\"></i></a>");
				}

				// previous button: limit to show only relevant months
				$("BUTTON[data-calendar-nav='prev']").click(function () {
					updateCalendarNavButtons();
					processCurrentCalendarCells();
				});

				// next button: limit to show only relevant months
				$("BUTTON[data-calendar-nav='next']").click(function () {
					updateCalendarNavButtons();
					processCurrentCalendarCells();
				});


				// TODO: date-time comparison of: 19:00:00 GMT-0500 vs 00:00:00 GMT-0500
				// TODO: end >  or  >=... what if both conditions are valid

				function updateCalendarNavButtons() {
					// TODO- someday fix this, too:
					// The "Day" button contains no 'cells' amd does not trigger the 'prev' or 'next' btn to disable appropriately

					// TODO- someday fix this:
					// currently gives (e.g)
					// calendarDateStart=Sat Dec 27 2014 19:00:00 GMT-0500 (Eastern Standard Time)
					// sheetDateStart = Tue Dec 02 2014 00:00:00 GMT-0500 (Eastern Standard Time)
//					console.log($("#calendar span").first().attr("data-cal-date"));
//					console.log($("#calendar span").last().attr("data-cal-date"));
					var calendarDateStart = new Date(Date.parse($("#calendar span").first().attr("data-cal-date") + 'T00:00:00Z'));
					var calendarDateEnd = new Date(Date.parse($("#calendar span").last().attr("data-cal-date") + 'T00:00:00Z'));
					var sheetDateStart = new Date($("#inputSheetDateStart").val());
					var sheetDateEnd = new Date($("#inputSheetDateEnd").val());

//					alert('calendarDateStart=' + calendarDateStart +  '\n' + 'sheetDateStart = ' + sheetDateStart);

					$("BUTTON[data-calendar-nav='prev']").prop("disabled", calendarDateStart <= sheetDateStart);
					$("BUTTON[data-calendar-nav='next']").prop("disabled", sheetDateEnd <= calendarDateEnd);
				}

				updateCalendarNavButtons();
				processCurrentCalendarCells();
			});


		</script>

		<style type="text/css">
			/* wms bootstrap-calendar-master overrides */
			span[data-cal-date] {
				margin-top: 5px;
				margin-right: 5px;
				font-size: inherit;
			}

			.cal-month-box .cal-day-today span[data-cal-date] {
				font-size: 1.2em;
				font-weight: bold;
			}
		</style>

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

