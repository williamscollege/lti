var GLOBAL_calendar_fetchSignupsforOpening = null;

$(document).ready(function () {

	// ***************************
	// onload actions
	// ***************************

	hasUserReachedSignupLimit(); // remove display of signup links (if condition is met)


	// ***************************
	// Calendar datepicker
	// ***************************

	$("#new_OpeningUntilDate,#edit_OpeningDateBegin").datepicker({
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
		//console.log(openingID, action);
		setupModalForm_EditOpening(openingID, action);
	});

	// Send email to participants for opening
	$(document).on('click', '#notifyParticipantsButton', function () {


        GLOBAL_confirmHandlerData = $("#edit_OpeningID").val();
		GLOBAL_confirmHandlerReference = {
			notifyParticipantsSubject: $("#notifyParticipantsSubject").val(),
			notifyParticipantsMessage: $("#notifyParticipantsMessage").val()
		};

        // console.log(JSON.stringify(GLOBAL_confirmHandlerReference));

		var params = {
			title: "Send email to participants",
			message: "<p>Really email participants?</p>" +
			'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;An alert with your message will be sent individually to each participant.</p>',
			label: "Send",
			class: "btn btn-primary",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "send-email-to-participants-for-opening-id",
			ajax_id: GLOBAL_confirmHandlerData,
			subject_message_json: GLOBAL_confirmHandlerReference
		};
		showConfirmBox(params);
	});

	// Sheet Opening: signup or cancel for this opening_id
	$(document).on('click', '.sus-add-me-to-opening, .sus-delete-me-from-opening', function () {
		var openingID = $(this).attr('data-opening-id');
		var doAction = 'sheet-opening-signup-add-me'; //default condition
		if ($(this).hasClass('sus-delete-me-from-opening')) {
			doAction = 'sheet-opening-signup-delete-me';
		}

		susUtil_setTransientAlert('progress', 'Working...');

		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: {
				ajax_Action: doAction,
				ajax_Primary_ID: openingID
			},
			dataType: 'json',
			error: function (req, textStatus, err) {
				susUtil_setTransientAlert('error', "Error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					susUtil_setTransientAlert('success', 'Saved');
					//$("#alert_usage_quotas").replaceWith(data['html_render_usage_alert']);
					//$("#contents_usage_quotas").replaceWith(data['html_render_usage_details']);
					//$(".list-opening-id-" + openingID).replaceWith(data['html_render_opening']);
					updateUserSignupUsageDetailsInDOM(data['html_render_usage_alert'], data['html_render_usage_details'], data['html_render_opening'], openingID);
				}
				else {
					susUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
				}
			}
		});
	});

	// helper function to enable updating of DOM for ajax success
	function updateUserSignupUsageDetailsInDOM(render_usage_alert, render_usage_details, render_opening, openingID) {
		$("#alert_usage_quotas").replaceWith(render_usage_alert);
		$("#contents_usage_quotas").replaceWith(render_usage_details);
		$(".list-opening-id-" + openingID).replaceWith(render_opening);

		// remove display of signup links (if condition is met)
		hasUserReachedSignupLimit();
	}

	// remove display of signup links (if condition is met)
	function hasUserReachedSignupLimit() {
		if ($(".wms-reached-signup-limit").length) {
			// console.log('wms-reached-signup-limit found');
			$(".sus-add-me-to-opening").hide();
		}
		else {
			$(".sus-add-me-to-opening").show();
		}
	}

	function setupModalForm_EditOpening(openingID, action) {
		// reset form values in modal (solution for: the ESC key will dismiss a modal but fails to clear values per next usage)
		$('#btnEditOpeningCancelSignup').click();

		// div parent of the link clicked, which contains all of the data attributes for this opening
		var parentOfClickedLink = $(".list-opening-id-" + openingID).last();

		// set initial form values; parent of clicked link contains all attributes for this opening
		$("#edit_OpeningID").val($(parentOfClickedLink).attr('data-opening_id'));
		$("#edit_OpeningName").val($(parentOfClickedLink).attr('data-name'));
		$("#edit_OpeningDescription").val($(parentOfClickedLink).attr('data-description'));
		$("#edit_OpeningNumSignupsPerOpening").val($(parentOfClickedLink).attr('data-max_signups'));
		$("#edit_OpeningLocation").val($(parentOfClickedLink).attr('data-location'));
		$("#edit_OpeningAdminNotes").val($(parentOfClickedLink).attr('data-admin_comment'));

		// set initial form values for: Send email to participants for opening (for subject: fetch opening name, else sheet name)
		var nameOpeningOrSheet = ($(parentOfClickedLink).attr('data-name') ? $(parentOfClickedLink).attr('data-name') : $("#inputSheetName").val());
		$("#notifyParticipantsSubject").val("[Glow Signup Sheets] " + nameOpeningOrSheet); // set subject value everytime
		$("#notifyParticipantsMessage").val(""); // remove any prior text
		$("#notifyParticipantsButton").show(); // ensure button is not hidden from prior actions
		$("#notifyParticipantsButton").button('reset'); // ensure removal of any prior 'loading' text
		$("#btnConfirmationText").html(""); // remove any prior text

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
		$("#edit_OpeningDateBegin").attr('value', forDateBeginClean);

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
			ajax_Action: doAction,
			ajax_Primary_ID: openingID
		};

		// show status
		// susUtil_setTransientAlert('progress', 'Working...');
		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: params,
			dataType: 'json',
			error: function (req, textStatus, err) {
				susUtil_setTransientAlert('error', "Error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					//susUtil_setTransientAlert('success', 'Saved');
					$("#signupListing UL").html(data.html_output);
					$(".list-opening-id-" + params['ajax_Primary_ID']).replaceWith(data['html_render_opening']);
				}
				else {
					susUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
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

	function setupModalForm_CreateOpening(forDateYYYYMMDD) {
		// reset form values in modal (solution for: the ESC key will dismiss a modal but fails to clear values per next usage)
		cleanUpForm("frmCreateOpening");

		var forDateAry = forDateYYYYMMDD.split('-');
		var forDateClean = forDateAry[1] + '/' + forDateAry[2] + '/' + forDateAry[0];

		var d = new Date(forDateYYYYMMDD);
		var dow = (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])[d.getDay()];
		var dom = forDateAry[2] * 1;

		// set up the date
		$("#new_OpeningUntilDate").attr('value', forDateClean);

		$("#new_OpeningDateBegin").val(forDateYYYYMMDD);
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
	}

	function resetSignupFields() {
		$("#signupUsername").val('').css('background-color', 'transparent');
		$("#signupAdminNote").val('');
		$("#btnEditOpeningAddSignup").button('reset');
	}


	// ***************************
	// listeners
	// ***************************

	/*
	 SAVE: Workflow of codebase for "delete opening(s)":
	 1. sus_opening.php - this class has a render fxn that creates the '.sus-delete-opening' links
	 2. calendar.js - listens for click, creates params to be passed to jQuery BootBox showConfirmBox
	 3. util.js - the showConfirmBox fxn calls ajax, sends data to ajax_actions.php
	 4. ajax_actions.php - user's radio button choice (if exists) informs the switch case for server-side work. creates extra $results[] records for use in DOM removal.
	 ...completes ajax action, returning json_encode'd results to the showConfirmBox fxn that had initiated the ajax call (util.js)
	 5. util.js - the showConfirmBox fxn's success callback in turn calls updateDOM() which again uses user's radio button choice to determine how to correctly remove DOM elements
	 */

	// Delete opening
	$(document).on("click", ".sus-delete-opening", function () {
		GLOBAL_confirmHandlerData = $(this).parent(".list-opening").attr('data-opening_id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-count-openings-in-group-id');

		var openingName = '';
		if ($(this).parent(".list-opening").attr('data-name')) {
			var openingName = " (" + $(this).parent(".list-opening").attr('data-name') + ")";
		}

		if (GLOBAL_confirmHandlerReference > 1) {
			// delete repeating opening choices (IS part of a group/series of repeating openings)
			var params = {
				title: "Delete Repeating Openings?",
				message: '<form>' +
				'<p><h4>' + $(this).parent().siblings('h4').html() + '</h4><strong>' + $(this).siblings('.opening-time-range').html() + '</strong>' + openingName + '</p>' +
				'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;Deleting an opening will cancel any associated signups.<br />An alert will be sent to the owner of each cancelled signup.</p>' +
				'<div class="radio"><label for="delete-choice-0">' +
				'<input type="radio" name="custom_user_value" id="delete-choice-0" value="0" checked="checked">' +
				'<strong>Only this instance</strong> - <span class="small">Delete only this opening</span></label>' +
				'</div>' +
				'<div class="radio"><label for="delete-choice-1">' +
				'<input type="radio" name="custom_user_value" id="delete-choice-1" value="1">' +
				'<strong>Only this single day</strong> - <span class="small">Delete all openings for this single day</span></label>' +
				'</div>' +
				'<div class="radio"><label for="delete-choice-2">' +
				'<input type="radio" name="custom_user_value" id="delete-choice-2" value="2">' +
				'<strong>All following</strong> - <span class="small">Delete this and all future openings in this series</span></label>' +
				'</div>' +
				'<div class="radio"><label for="delete-choice-3">' +
				'<input type="radio" name="custom_user_value" id="delete-choice-3" value="3">' +
				'<strong>All openings in the series</strong> - <span class="small">Delete this and all past and future openings in this series</span></label>' +
				'</div>' +
				'</form>',
				label: "Delete",
				class: "btn btn-danger",
				url: "../ajax_actions/ajax_actions.php",
				ajax_action: "delete-opening",
				ajax_id: GLOBAL_confirmHandlerData
			};
		}
		else {
			// delete single opening (NOT part of a group/series)
			var params = {
				title: "Delete opening",
				message: "<p>Really delete this opening?<br /><br /><strong>" + $(this).siblings('.opening-time-range').html() + "</strong>" + openingName + "</p>" +
				'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;Deleting this opening will cancel any associated signups. An alert will be sent to the owner of each cancelled signup.</p>',
				label: "Delete opening",
				class: "btn btn-danger",
				url: "../ajax_actions/ajax_actions.php",
				ajax_action: "delete-opening",
				ajax_id: GLOBAL_confirmHandlerData
			};
		}

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
		//console.log('GLOBAL_confirmHandlerData=' + GLOBAL_confirmHandlerData + ', GLOBAL_confirmHandlerReference=' + GLOBAL_confirmHandlerReference)
		var params = {
			title: "Delete Signup",
			message: "<p>Really delete this signup for <strong>&quot;" + $(this).attr('data-for-signup-name') + "&quot;</strong>?" + '</p>' +
			'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;An alert will be sent to this user.</p>',
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
		// add visual highlight to label as a "reminder"
		$("#repeatUntilDate LABEL").addClass('bg-danger');
	});

	$("#radioOpeningRepeatRate3").click(function (event) {
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").show();
		$("#repeatUntilDate").show();
		// add visual highlight to label as a "reminder"
		$("#repeatUntilDate LABEL").addClass('bg-danger');
	});

	// remove visual highlight from label
	$("#new_OpeningUntilDate").click(function () {
		$("#repeatUntilDate LABEL").removeClass('bg-danger');
	});

	$("#btnEditOpeningSubmit").click(function () {
		// show button loading text (bootstrap)
		$("#btnEditOpeningSubmit").button('loading');

		// submit form
		$("#frmEditOpening").submit();
	});

	$("#btnNewOpeningSubmit").click(function () {
		// show button loading text (bootstrap)
		$("#btnNewOpeningSubmit").button('loading');
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

		// create time strings
		var btime = valsToTimeString($("#new_OpeningBeginTimeHour").val(), $("#new_OpeningBeginTimeMinute").val(), $("#new_OpeningBeginTime_AMPM").val());
		var etime = valsToTimeString($("#new_OpeningEndTimeHour").val(), $("#new_OpeningEndTimeMinute").val(), $("#new_OpeningEndTimeMinute_AMPM").val());

		// if end <= begin, that's a problem
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
		// show button loading text (bootstrap)
		$("#btnEditOpeningAddSignup").button('loading');

		var doAction = 'edit-opening-add-signup-user';

		var params = {
			ajax_Action: doAction,
			ajax_Primary_ID: $("#edit_OpeningID").val(),
			ajax_Name: $("#signupUsername").val(),
			ajax_Description: $("#signupAdminNote").val()
		};

		// show status
		susUtil_setTransientAlert('progress', 'Working...');
		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: params,
			dataType: 'json',
			error: function (req, textStatus, err) {
				susUtil_setTransientAlert('error', "Error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					susUtil_setTransientAlert('success', 'Saved');
					GLOBAL_calendar_fetchSignupsforOpening(params['ajax_Primary_ID']);
					// hide input fields (and reset them)
					$("#btnEditOpeningCancelSignup").click();
				}
				else {
					// error message
					susUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
					// reset button
					$("#btnEditOpeningAddSignup").button('reset');
					// focus on bad username; select input field contents
					$("#signupUsername").focus().css('background-color', 'yellow');
				}
			}
			//, complete: function(req,textStatus) {
			//	$("#"+target_id).prop("disabled", false);
			//}
		});
	});

	$("#link_show_signup_controls").click(function () {
		$("#signupControls").show();
		$("#signupUsername").focus();
		$(this).hide();
		// reset input fields
		resetSignupFields();
	});

	// Reset hidden fields
	$('#btnEditOpeningCancelSignup').click(function () {
		$("#signupControls").hide();
		$("#link_show_signup_controls").show();
		// reset input fields
		resetSignupFields();
	});

	// Cancel button: dismiss modal
	$('#btnNewOpeningCancel, #btnEditOpeningCancel').click(function () {
		// reset form
		cleanUpForm("frmCreateOpening");
		cleanUpForm("frmEditOpening");
	});


	// ***************************
	// Cancel and cleanup (helper function)
	// ***************************

	function cleanUpForm(formName) {
		// reset form (using jquery validate plugin)
		// $('#' + formName).validate().resetForm();

		// reset form (using standard jquery)
		$('#' + formName).trigger("reset");

		// manually remove input highlights
		// $(".form-group").removeClass('success').removeClass('error');

		// reset submit button (avoid disabled state)
		$("#btnNewOpeningSubmit, #btnEditOpeningSubmit").button('reset');
	}

});