$(document).ready(function () {

	// ***************************
	// onload actions
	// ***************************


	// ***************************
	// helper functions
	// ***************************

	// TODO - edit openings: btn_save_openings, cleanUpForm(), frmCreateOpening, frmEditOpening

	// TODO - Refactoring: Could probably refactor showConfirmBox() and updateDOM() into util.js file and remove a lot of redundant code
	// BootBox jQuery helper function
	function showConfirmBox(ary) {
		//alert(ary['ajax_action'] + ', ' + ary['ajax_id']);
		bootbox.dialog({
			title: ary['title'],
			message: ary['message'],
			buttons: {
				success: {
					label: ary['label'],
					className: ary['class'],
					callback: function () {
						// show status
						dfnUtil_setTransientAlert('progress', 'Saving...');
						$.ajax({
							type: 'GET',
							url: ary['url'],
							cache: false,
							data: {
								'ajaxVal_Action': ary['ajax_action'],
								'ajaxVal_Delete_ID': ary['ajax_id']
							},
							dataType: 'json',
							success: function (data) {
								if (data.status == 'success') {
									// remove element
									updateDOM(ary['ajax_action'], true);
								}
								else {
									// error message
									updateDOM(ary['ajax_action'], false);
								}
							}
						});
					}
				},
				cancel: {
					label: "Cancel",
					className: "btn btn-link btn-cancel",
					callback: function () {
						this.dismiss = "modal";
					}
				}
			},
			// modal options
			animate: false,
			backdrop: "static",
			onEscape: true
		});
	}

	function updateDOM(action, ret) {
		if (action == 'delete-opening') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');

				// check to see if this the last opening on this date
				var countRemainingOpenings = $('#list-opening-id-' + GLOBAL_confirmHandlerData).siblings(".list-opening").length;

				if (countRemainingOpenings == 0) {
					// this is the last opening on this date!
					// remove the list container from DOM for both: "Calendar Openings" overlay AND calendar "List Openings"
					$('#list-opening-id-' + GLOBAL_confirmHandlerData).parent().parent(".calendar-cell-openings").remove();
					$('#tabOpeningsList #list-opening-id-' + GLOBAL_confirmHandlerData).parent(".opening-list-for-date").remove();
				}
				else {
					// additional openings still exist on this date...
					// remove single opening from DOM for both: "Calendar Openings" overlay AND calendar "List Openings"
					$('#list-opening-id-' + GLOBAL_confirmHandlerData).remove();
					$('#tabOpeningsList #list-opening-id-' + GLOBAL_confirmHandlerData).remove();
				}
			}
			else {
				// error message
				$("#list-opening-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		//else if (action == 'delete-sheet') {
		//	if (ret) {
		//		// show status
		//		dfnUtil_setTransientAlert('success', 'Saved');
		//		// remove element
		//		$('#btn-edit-sheet-id-' + GLOBAL_confirmHandlerData).closest('TR').remove();
		//	}
		//	else {
		//		// error message
		//		$("#btn-edit-sheet-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
		//	}
		//}
	}


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
		setupModalForm_EditOpening(openingID, action);
	});

	function setupModalForm_EditOpening(openingID, action) {
		// reset non-dynamic form fields to defaults
		$('#frmEditOpening').trigger("reset");
		$('#btnEditOpeningCancelSignup').click();


		// div parent of the link clicked, which contains all of the data attributes for this opening
		var parentOfClickedLink = $("#list-opening-id-" + openingID);

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
			// display the Add Someone functionality
			$("#link_show_signup_controls").click();
		}

		// signupListing: set data attribute
		//$("#signupListing").data("for-opening-id", $(parentOfClickedLink).attr('data-opening_id'));
		// console.log($("#signupListing").data("for-opening-id"));

		// call function to populate "#signupListing" with list of current signups
		fetchSignupsforOpening(openingID);
	}

	function fetchSignupsforOpening(openingID){
		var doAction = 'fetch-signups-for-opening-id';
		//console.log(doAction + ' = ' + openingID);

		//var params = [username,note];
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: openingID
		};

		//alert(ary['url'] + '\n ' + ary['ajax_action'] + '\n ' + ary['ajax_id'] + '\n' + ary['ajax_val']);
		//console.log('to remote url: '+remoteUrl);
		console.dir(params);
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
				console.dir(req);
				console.dir(textStatus);
				console.dir(err);
			},
			success: function (data) {
				if (data.status == 'success') {
					// remove element
					//dfnUtil_setTransientAlert('success', 'Saved');

					if(data.which_action == 'fetch-signups-for-opening-id'){
						$("#signupListing UL").html(data.html_output);
					}
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
	}

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
		//alert("on 1");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").hide();
	});

	$("#radioOpeningRepeatRate2").click(function (event) {
		//alert("on 2");
		$("#repeatWeekdayChooser").show();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").show();
	});

	$("#radioOpeningRepeatRate3").click(function (event) {
		//alert("on 3");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").show();
		$("#repeatUntilDate").show();
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
		//alert("time strings are "+btime+" and "+etime);

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
	$("#btnEditOpeningAddSignup").click(function(){
		var doAction = 'edit-opening-add-signup-user';

		//var params = [username,note];
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#edit_OpeningID").val(),
			ajaxVal_Name: $("#signupUsername").val(),
			ajaxVal_Description: $("#signupAdminNote").val()
		};

		//alert(ary['url'] + '\n ' + ary['ajax_action'] + '\n ' + ary['ajax_id'] + '\n' + ary['ajax_val']);
		//console.log('to remote url: '+remoteUrl);
		console.dir(params);
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
				console.dir(req);
				console.dir(textStatus);
				console.dir(err);
			},
			success: function (data) {
				if (data.status == 'success') {
					// remove element
					dfnUtil_setTransientAlert('success', 'Saved');

					if(data.which_action == 'edit-opening-add-signup-user'){
						$("#signupListing UL").append(data.html_output);
					}
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
	});

	// Cancel cleanup
	$('#btnEditOpeningCancelSignup').click(function () {
		$("#signupControls").hide();
		$("#link_show_signup_controls").show();
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

	// TODO - implement in ajax success callback
	//success: function (data) {
	//	// hide and reset form
	//	$("#btnOpeningCancel").click();

	// END: Cancel and cleanup

});