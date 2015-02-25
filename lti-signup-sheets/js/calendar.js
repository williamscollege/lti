var GLOBAL_calendar_fetchSignupsforOpening = null;

$(document).ready(function () {

	// ***************************
	// onload actions
	// ***************************


	// ***************************
	// helper functions
	// ***************************

	// TODO - edit openings: btn_save_openings, cleanUpForm(), frmCreateOpening, frmEditOpening


	// ***************************
	// Calendar datepicker
	// ***************************
	$("#new_OpeningUntilDate,#edit_OpeningDateStart").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'mm/dd/yy',
		yearRange: '-4:+4'
	});

	// Create Openings: populate modal form with calendar date of day clicked
	$(document).on('click', '.addOpeningLink', function () {
		var dateClicked = $(this).attr('data-cal-date');
		setupModalForm_CreateOpening(dateClicked);
	});

	// Edit Opening: populate modal form using opening_id
	$(document).on('click', '.sus-edit-opening, .sus-add-someone-to-opening', function () {
		var openingID = $(this).attr('data-opening-id');
		var action = "edit";
		if ($(this).hasClass('sus-add-someone-to-opening')) {
			var action = "add";
		}
		console.log(openingID, action);
		setupModalForm_EditOpening(openingID, action);
	});

	// Sheet Opening: signup or cancel for this opening_id
	$(document).on('click', '.sus-add-me-to-opening, .sus-delete-me-from-opening', function () {
		var openingID = $(this).attr('data-opening-id');
		var doAction = 'sheet-opening-signup-add-me'; //default condition
		if ($(this).hasClass('sus-delete-me-from-opening')) {
			doAction = 'sheet-opening-signup-delete-me';
		}

		dfnUtil_setTransientAlert('progress', 'Saving...');

		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: {
				ajaxVal_Action: doAction,
				ajaxVal_Edit_ID: openingID
			},
			dataType: 'json',
			error: function (req, textStatus, err) {
				dfnUtil_setTransientAlert('error', "error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					dfnUtil_setTransientAlert('success', 'Saved');
					$(".list-opening-id-" + openingID).replaceWith(data['html_render_opening']);
				}
				else {
					dfnUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
				}
			}
		});
	});

	function setupModalForm_EditOpening(openingID, action) {
		// reset non-dynamic form fields to defaults
		$('#frmEditOpening').trigger("reset");
		$('#btnEditOpeningCancelSignup').click();

		// div parent of the link clicked, which contains all of the data attributes for this opening
		var parentOfClickedLink = $(".list-opening-id-" + openingID).last();

		// set initial form values; parent of clicked link contains all attributes for this opening
		$("#edit_OpeningID").val($(parentOfClickedLink).attr('data-opening_id'));
		$("#edit_OpeningName").val($(parentOfClickedLink).attr('data-name'));
		$("#edit_OpeningDescription").val($(parentOfClickedLink).attr('data-description'));
		$("#edit_OpeningAdminNotes").val($(parentOfClickedLink).attr('data-admin_comment'));
		$("#edit_OpeningLocation").val($(parentOfClickedLink).attr('data-location'));
		$("#edit_OpeningNumSignupsPerOpening").val($(parentOfClickedLink).attr('data-max_signups'));

		// split date/time values
		var datetimeBeginAry = $(parentOfClickedLink).attr('data-begin_datetime').split(' ');
		var datetimeEndAry = $(parentOfClickedLink).attr('data-end_datetime').split(' ');

		// unnecessary computations: format dates: mm/dd/yyyy format (for datepicker)
		var forDateBeginAry = datetimeBeginAry[0].split('-'); // current format: 2015-02-24
		var forDateBeginClean = forDateBeginAry[1] + '/' + forDateBeginAry[2] + '/' + forDateBeginAry[0]; // current format: 02/24/2015

		// clean times: 12 hour format with AM/PM
		var forTimeBeginAry = timeConvert24to12(datetimeBeginAry[1]).split(':');
		var forTimeEndAry = timeConvert24to12(datetimeEndAry[1]).split(':');

		// set date/time values
		$("#edit_OpeningDateStart").attr('value', forDateBeginClean);

		$('#edit_OpeningBeginTimeHour option[value="' + forTimeBeginAry[0] + '"]').prop('selected', true);
		$('#edit_OpeningBeginTimeMinute option[value="' + roundMinutesToNearestFiveUsingTwoDigits(forTimeBeginAry[1]) + '"]').attr('selected', 'selected');
		$('#edit_OpeningBeginTime_AMPM option[value="' + forTimeBeginAry[3] + '"]').prop('selected', true);

		$('#edit_OpeningEndTimeHour option[value="' + forTimeEndAry[0] + '"]').prop('selected', true);
		$('#edit_OpeningEndTimeMinute option[value="' + roundMinutesToNearestFiveUsingTwoDigits(forTimeEndAry[1]) + '"]').prop('selected', true);
		$('#edit_OpeningEndTimeMinute_AMPM option[value="' + forTimeEndAry[3] + '"]').prop('selected', true);

		if (action == "add") {
			// display the add signup functionality
			$("#link_show_signup_controls").click();
		}

		$("#signupListing ul").html("<li><em>loading data...</em></li>");

		// call function to populate "#signupListing" with list of current signups
		fetchSignupsforOpening(openingID);
	}

	function fetchSignupsforOpening(openingID) {
		var doAction = 'fetch-signups-for-opening-id';

		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: openingID
		};

		// show status
		// dfnUtil_setTransientAlert('progress', 'Saving...');
		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: params,
			dataType: 'json',
			error: function (req, textStatus, err) {
				dfnUtil_setTransientAlert('error', "error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					//dfnUtil_setTransientAlert('success', 'Saved');
					$("#signupListing UL").html(data.html_output);
					$(".list-opening-id-" + params['ajaxVal_Edit_ID']).replaceWith(data['html_render_opening']);
				}
				else {
					dfnUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
				}
			}
			//, complete: function(req,textStatus) {
			//	$("#"+target_id).prop("disabled", false);
			//}
		});
	}

	GLOBAL_calendar_fetchSignupsforOpening = fetchSignupsforOpening;

	function roundMinutesToNearestFiveUsingTwoDigits(num) {
		// round minutes to nearest 5 minute increment
		var roundMinutes = 5 * Math.round(num / 5);

		// ensure that resultant has two digits
		if (roundMinutes.toString().length == 1) {
			roundMinutes = "0" + roundMinutes;
		}
		return parseInt(roundMinutes);
	}

	function timeConvert24to12(time) {
		// Check correct time format and split into components
		time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

		if (time.length > 1) { // If time format correct
			time = time.slice(1);  // Remove full string match value
			time[5] = +time[0] < 12 ? ':am' : ':pm'; // Set am/pm
			time[0] = +time[0] % 12 || 12; // Adjust hours
		}
		return time.join(''); // return adjusted time or original string
	}

	function resetSignupFields() {
		$("#signupUsername").val('');
		$("#signupAdminNote").val('');
	}

	function setupModalForm_CreateOpening(forDateYYYYMMDD) {

		var forDateAry = forDateYYYYMMDD.split('-');
		var forDateClean = forDateAry[1] + '/' + forDateAry[2] + '/' + forDateAry[0];

		var d = new Date(forDateYYYYMMDD);
		var dow = (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])[d.getDay()];
		var dom = forDateAry[2] * 1;

		// set up the date
		$("#new_OpeningUntilDate").attr('value', forDateClean);

		$("#new_OpeningDateStart").val(forDateYYYYMMDD);
		$(".openingCalDate").html(forDateClean);

		// clear out & reset the day-of-week repeats
		$('.repeat_dow_val').val(0);
		$('.toggler_dow').removeClass('btn-success');
		$('.toggler_dow').removeClass('btn-default');
		$('.toggler_dow').addClass('btn-default');
		$('#btn_' + dow).click();

		// clear out & reset the day-of-month repeats
		$('.repeat_dom_val').val(0);
		$('.toggler_dom').removeClass('btn-success');
		$('.toggler_dom').removeClass('btn-default');
		$('.toggler_dom').addClass('btn-default');
		$('#btn_dom_' + dom).click();

		// set the repeat option to be the default (only on)
		$('#radioOpeningRepeatRate1').click();

		// hide the stuff that should be hidden
		$('#link_hide_duration').click();
		$('#link_hide_optional_opening_fields').click();

		// reset non-dynamic form fields to defaults
		$('#frmCreateOpening').trigger("reset");
	}

	// ***************************
	// listeners
	// ***************************

	// Delete opening
	$(document).on("click", ".sus-delete-opening", function () {
		GLOBAL_confirmHandlerData = $(this).parent(".list-opening").attr('data-opening_id');

		var openingName = '';
		if ($(this).parent(".list-opening").attr('data-name')) {
			var openingName = " (" + $(this).parent(".list-opening").attr('data-name') + ")";
		}

		var params = {
			title: "Delete Opening",
			message: "Really delete this opening?<br /><br /><strong>" + $(this).siblings('.opening-time-range').html() + "</strong>" + openingName,
			label: "Delete Opening",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-opening",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	$("#link_show_optional_opening_fields").click(function () {
		$(".optional_opening_fields").show();
		$("#link_show_optional_opening_fields").hide();
	});
	$("#link_hide_optional_opening_fields").click(function () {
		$(".optional_opening_fields").hide();
		$("#link_show_optional_opening_fields").show();
	});

	// Edit signup (from Edit Opening / Signups)
	$(document).on("click", ".sus-edit-signup", function () {
		// ensure that input boxes are displayed
		$("#link_show_signup_controls").click();

		// populate data of input boxes
		$("#signupUsername").val($(this).attr('data-for-username'));
		$("#signupAdminNote").val($(this).attr('data-for-signup-admin-comment'));
	});


	// Delete signup (from Edit Opening / Signups)
	$(document).on("click", ".sus-delete-signup", function () {
		// reset input fields
		resetSignupFields();

		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		console.log('GLOBAL_confirmHandlerData=' + GLOBAL_confirmHandlerData + ', GLOBAL_confirmHandlerReference=' + GLOBAL_confirmHandlerReference)
		var params = {
			title: "Delete Signup",
			message: "Really delete this signup for <strong>&quot;" + $(this).attr('data-for-signup-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup-from-edit-opening-modal",
			ajax_id: GLOBAL_confirmHandlerData
		};
		GLOBAL_util_showConfirmBox(params);
	});

	// singups: sort by last name
	$("#signup_sort_by_last_name").click(function () {
		$("#signupListing UL LI").sort(function (a, b) {
			return ($(b).data('for-lastname') + ' ' + $(b).data('for-firstname')) < ($(a).data('for-lastname') + ' ' + $(a).data('for-firstname')) ? 1 : -1;
		}).appendTo('#signupListing UL');

	});
	$("#signup_sort_by_signup_order").click(function () {
		$("#signupListing UL LI").sort(function (a, b) {
			return ($(b).data('for-signup-created_at')) < ($(a).data('for-signup-created_at')) ? 1 : -1;
		}).appendTo('#signupListing UL');

	});


	// ***************************
	// default condition
	// ***************************
	$("#link_hide_time_range").click(function () {
		$(this).hide();
		$(".openings_by_time_range").hide();
		$("label[for='openingBeginTimeHour']").html("Starting&nbsp;at");
		$("label[for='openingEndTimeHour']").html("Make&nbsp;each&nbsp;opening");
		$("#link_hide_duration").show();
		$(".openings_by_duration").show();
		$("#new_OpeningTimeMode").val('duration');
	});

	$("#link_hide_duration").click(function () {
		$(this).hide();
		$(".openings_by_duration").hide();
		$("label[for='openingBeginTimeHour']").html("From");
		$("label[for='openingEndTimeHour']").html("To");
		$("#link_hide_time_range").show();
		$(".openings_by_time_range").show();
		$("#new_OpeningTimeMode").val('time_range');
	});

	$(".toggler_dow").click(function (event) {
		var which = event.target.id.substr(4, 3);
		if ($(this).hasClass("btn-success")) {
			//alert("turning off #repeat_dow_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dow_" + which).val(0);
		}
		else {
			//alert("turning on #repeat_dow_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dow_" + which).val(1);
		}
	});

	$(".toggler_dom").click(function (event) {
		var which = event.target.id.substr(8, 3);
		if ($(this).hasClass("btn-success")) {
			//alert("turning off #repeat_dom_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dom_" + which).val(0);
		}
		else {
			//alert("turning on #repeat_dom_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dom_" + which).val(1);
		}
	});

	$("#radioOpeningRepeatRate1").click(function (event) {
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").hide();
	});

	$("#radioOpeningRepeatRate2").click(function (event) {
		$("#repeatWeekdayChooser").show();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").show();
	});

	$("#radioOpeningRepeatRate3").click(function (event) {
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").show();
		$("#repeatUntilDate").show();
	});

	$("#btnEditOpeningSubmit").click(function () {
		// TODO -- DKC form validation: see russiansisters census_add.php
		$("#frmEditOpening").submit(); // efficiency: trigger the native submit event to avoid re-validating the form
	});


	// TODO - Is this still needed, now that an opening can 'wrap' around midnight? If obsolete, remove from codebase
	// TODO - if needed, add class to:  #btnNewOpeningSubmit, #btnEditOpeningSubmit
	$(".check_if_time_errors").click(function (event) {
		if (($("#new_OpeningEndTimeHour").val() == '12')
			&& ($("#new_OpeningEndTimeMinute").val() == '0')
			&& ($("#new_OpeningEndTimeMinute_AMPM").val() == 'am')) {
			customAlert("", "cannot end an opening at 12:00 AM");
			return false;
		}

		// create start time string
		// create end time string
		var btime = valsToTimeString($("#new_OpeningBeginTimeHour").val(), $("#new_OpeningBeginTimeMinute").val(), $("#new_OpeningBeginTime_AMPM").val());
		var etime = valsToTimeString($("#new_OpeningEndTimeHour").val(), $("#new_OpeningEndTimeMinute").val(), $("#new_OpeningEndTimeMinute_AMPM").val());

		// if end <= start, that's a problem
		if (etime <= btime) {
			customAlert("", "end time must be later than start time");
			return false;
		}
		return true;
	});


	// ***************************
	// Edit Opening: Signup someone to an opening
	// ***************************
	$("#btnEditOpeningAddSignup").click(function () {
		var doAction = 'edit-opening-add-signup-user';

		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#edit_OpeningID").val(),
			ajaxVal_Name: $("#signupUsername").val(),
			ajaxVal_Description: $("#signupAdminNote").val()
		};

		// show status
		dfnUtil_setTransientAlert('progress', 'Saving...');
		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: params,
			dataType: 'json',
			error: function (req, textStatus, err) {
				dfnUtil_setTransientAlert('error', "error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					dfnUtil_setTransientAlert('success', 'Saved');
					GLOBAL_calendar_fetchSignupsforOpening(params['ajaxVal_Edit_ID']);
					// hide input fields (and reset them)
					$("#btnEditOpeningCancelSignup").click();
				}
				else {
					// error message
					dfnUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
				}
			}
			//, complete: function(req,textStatus) {
			//	$("#"+target_id).prop("disabled", false);
			//}
		});
	});

	$("#link_show_signup_controls").click(function () {
		$("#signupControls").show();
		$(this).hide();
		// reset input fields
		resetSignupFields();
	});

	// Cancel cleanup
	$('#btnEditOpeningCancelSignup').click(function () {
		$("#signupControls").hide();
		$("#link_show_signup_controls").show();
		// reset input fields
		resetSignupFields();
	});


	// ***************************
	// Cancel and cleanup
	// ***************************
	// TODO - update other modal cleanUpForm fxns with solutions from this one

	function cleanUpForm(formName) {
		// reset form to initial values (does not effect hidden inputs)
		$('#' + formName).trigger("reset");

		// TODO - temporary... need to deal with hidden values, and button conditions after click, dismiss, and re-open of modal
		// TODO - also need to bind  button[data-dismiss="modal"]  action (AND ESCAPE KEY TOO) to the cleanUpForm()
		//$(":input", form).each(function () {
		//	var type = this.type;
		//	var tag = this.tagName.toLowerCase();
		//	if (type == 'text') {
		//		this.value = "";
		//	}
		//});
		//validateOpening.resetForm();
		// manually remove input highlights
		// $(".form-group").removeClass('success').removeClass('error');
	}

	$('#btnNewOpeningCancel, #btnEditOpeningCancel').click(function () {
		cleanUpForm("frmCreateOpening");
		cleanUpForm("frmEditOpening");

		// manually clear modal values
		//$("#new_OpeningID").val(0);
		//$("#new_OpeningLabel").text('');
		//$("#new_OpeningAction").val('');
		//$("#frmCreateOpening textarea").val('');
		//$("#frmCreateOpening input[type=text]").val('');
		//$("#frmCreateOpening input[type=radio]").attr("checked", false);
		//$("#frmCreateOpening select").val(0);

		// reset submit button (avoid disabled state)
		$("#btnNewOpeningSubmit, #btnEditOpeningSubmit").button('reset');
	});
	// END: Cancel and cleanup

});