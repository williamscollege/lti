$(document).ready(function () {

	// initial default conditions
	$("#input_sheet_name").focus().select();


	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})


	// ***************************
	// Calendar datepicker
	// ***************************
	$("#inputSheetDateBegin, #inputSheetDateEnd").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'mm/dd/yy',
		yearRange: '-4:+4'
	});


	// New Sheet: set default date (today)
	if ($("#inputSheetDateBegin").val() == "") {
		var today = new Date();
		$("#inputSheetDateBegin").datepicker('setDate', today);

		var futureDate = new Date(today.getTime());
		futureDate.setMonth(futureDate.getMonth() + 1);
		$("#inputSheetDateEnd").datepicker('setDate', futureDate);
	}


	// TODO - Replace this with pre-bundled jquery validation.js
	// form validation
	$("#btnSheetInfoSubmit").click(function (event) {
		if (!($("#inputSheetName").val().match(/\S/))) {
			alert("Missing Information - You must enter a name for the sheet");
			$("#inputSheetName").focus();
			return false;
		}
		return true;
	});


	// ***************************
	// Listeners
	// ***************************

	// Edit Sheet Access: Who can see signups
	$("input[name=radioSignupPrivacy]").on("change", function () {
		// GLOBAL_confirmHandlerData = $(this).attr('id');
		GLOBAL_confirmHandlerReference = $(this).val();
		var params = {
			ajaxVal_Action: "editSheetAccess-flag-private-signups",
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: GLOBAL_confirmHandlerReference
		};
		updateSheetAccess(params);
	});

	// Edit Sheet Access: Who can see signups
	$(".access_by_course_ckboxes").on("change", function () {
		var doAction = 'editSheetAccess-access-by-course-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-course-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: $(this).attr('data-permval')
		};
		updateSheetAccess(params);
	});


	$(".access_by_instructor_ckboxes").on("change", function () {
		var doAction = 'editSheetAccess-access-by-instructor-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-instructor-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: $(this).attr('data-permval')
		};
		updateSheetAccess(params);
	});


	$("#access_by_role_teacher").on("change", function () {
		var doAction = 'editSheetAccess-access-by-role-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-role-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: 'teacher'
		};
		updateSheetAccess(params);
	});

	$("#access_by_role_student").on("change", function () {
		var doAction = 'editSheetAccess-access-by-role-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-role-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: 'student'
		};
		updateSheetAccess(params);
	});

	$("#access_by_any").on("change", function () {
		var doAction = 'editSheetAccess-access-by-any-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-any-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: 'all'
		};
		updateSheetAccess(params);
	});


	$("#textAccessByUserList,#textAdminByUserList").on("change", function () {
		var eleClickedId = $(this).attr("id");
		var doAction = 'editSheetAccess-access-by-user';
		if (eleClickedId == 'textAdminByUserList') {
			doAction = 'editSheetAccess-admin-by-user';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: $(this).val()
		};
		updateSheetAccess(params);
	});

	// display usage details for this group and sheet
	$("#link_for_usage_quotas").click(function () {
		if ($("#toggle_usage_quotas").hasClass('hidden')) {
			$("#toggle_usage_quotas").removeClass('hidden');//.show();
			$("#link_for_usage_quotas").text('Hide usage details');
		}
		else {
			$("#toggle_usage_quotas").addClass('hidden');//.show();
			$("#link_for_usage_quotas").text('Show usage details');
		}
	});

	// display opening signup details (Copy DOM from calendar overlay)
	$(".calendar-cell-openings").click(function () {
		var dataset = $(this).html();
		$("#toggle_openings_instructions").hide();
		$("#display_opening_signup_details").show().html(dataset);
		// prepend a 'close' button to DOM overlay
		$("#display_opening_signup_details").prepend('<a href="#" title="close" id="wms_custom_close_x" class="close"><span aria-hidden="true">&times;</span></a>');
	});

	// close opening signup details (DOM overlay previously copied from calendar)
	$(document).on('click', '#wms_custom_close_x', function () {
		$("#toggle_openings_instructions").show();
		$("#display_opening_signup_details").hide();
	});


	// ***************************
	// helper functions
	// ***************************

	function updateSheetAccess(params) {
		//alert(ary['url'] + '\n ' + ary['ajax_action'] + '\n ' + ary['ajax_id'] + '\n' + ary['ajax_val']);
		//console.log('to remote url: '+remoteUrl);
		//console.dir(params);
		// show status
		susUtil_setTransientAlert('progress', 'Saving...');
		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: params,
			dataType: 'json',
			error: function (req, textStatus, err) {
				susUtil_setTransientAlert('error', "error making ajax request: " + err.toString());
			},
			success: function (data) {
				if (data.status == 'success') {
					// remove element
					susUtil_setTransientAlert('success', 'Saved');
				}
				else {
					// error message
					susUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
					// console.dir(data);
					// console.dir(textStatus);
					// console.dir(err);
				}
			}
			//, complete: function(req,textStatus) {
			//	$("#"+target_id).prop("disabled", false);
			//}
		});
	}

	$('#scroll-to-todayish-openings').click(function () {
		scrollOpeningsListToTodayish();
	});

	function scrollOpeningsListToTodayish() {
		var closestFutureOpeningsList = $('#openings-list-container .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#openings-list-container .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#openings-list-container .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#openings-list-container').scrollTop($('#openings-list-container').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
		$(closestFutureOpeningsList).first().effect("highlight", {color: '#C9E5C9'}, 300);
	}

});