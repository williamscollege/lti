$(document).ready(function () {

	// initial default conditions
	$("#input_sheet_name").focus().select();


	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$(function () {
		$('[data-toggle="tooltip"]').tooltip();
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


	// form validation
	$('#frmEditSheet').validate({
		rules: {
			inputSheetName: {
				required: true,
				minlength: 2
			},
			inputSheetDateBegin: {
				required: true,
				date: true
				//, dateLessThanOrEqual: true
				//, dateLessThanOrEqual : '#inputSheetDateEnd'
			},
			inputSheetDateEnd: {
				required: true,
				date: true
			}
		},
		messages: {
			inputSheetName: "Please enter a sheet name"
		},
		highlight: function (element) {
			$(element).closest('.form-group').removeClass('success').addClass('error'); //.removeClass('success')
		},
		//invalidHandler: function (e, validator) {
		//	var errors = validator.numberOfInvalids();
		//	if (errors) {
		//		var message = errors == 1
		//			? 'You missed 1 field. It has been highlighted below'
		//			: 'You missed ' + errors + ' fields.  They have been highlighted below';
		//		$("div.error span").html(message);
		//		$("div.error").show();
		//	}
		//	else {
		//		$("div.error").hide();
		//	}
		//},
		success: function (element) {
			element
			//.text('OK!').addClass('valid')
			//.closest('.form-group').addClass('success').removeClass('error');//.addClass('success');
		},
		submitHandler: function (form) {
			// show button loading text (bootstrap)
			$("#btnSheetInfoSubmit").button('loading');

			// submit form (submit form using Javascript instead of jQuery to avoid "Too much recursion" error)
			form.submit();
		}
	});

	// BEGIN TEST
	// TODO - add custom addMethod validation for datebegin <= dateend (sheets_edit_one.php)
	// jQuery.validator.addMethod("dateLessThanOrEqual",
	//	function (value, element) {
	//		if ($("#inputSheetDateEnd").val() === "")
	//			return true;
	//
	//		console.log(value, element, $("#inputSheetDateEnd").val());
	//		//if (new Date(value) <= new Date($(params).val())) {
	//		//	//alert('temp stop');
	//		//	return new Date(value) <= new Date($(params).val());
	//		//}
	//		if (!/Invalid|NaN/.test(new Date(value))) {
	//			return new Date(value) <= new Date($("#inputSheetDateEnd").val());
	//		}
	//
	//		return isNaN(value) && isNaN($("#inputSheetDateEnd").val())
	//			|| (Number(value) <= Number($("#inputSheetDateEnd").val()));
	//	}, 'Must be less than or equal to end date.');

	//// add custom validator method
	//jQuery.validator.addMethod("dateLessThanOrEqual",
	//	function (value, element, params) {
	//		if ($(params).val() === "")
	//			return true;
	//
	//		console.log(value, element, params, $(params).val());
	//		//if (new Date(value) <= new Date($(params).val())) {
	//		//	//alert('temp stop');
	//		//	return new Date(value) <= new Date($(params).val());
	//		//}
	//		if (!/Invalid|NaN/.test(new Date(value))) {
	//			return new Date(value) <= new Date($(params).val());
	//		}
	//
	//		return isNaN(value) && isNaN($(params).val())
	//			|| (Number(value) <= Number($(params).val()));
	//	}, 'Must be less than or equal to end date.');

	// END TEST


	// ***************************
	// Listeners
	// ***************************

	// Edit Sheet "Access": Various categories of who can see signups
	$("input[name=radioSignupPrivacy]").on("change", function () {
		// GLOBAL_confirmHandlerData = $(this).attr('id');
		GLOBAL_confirmHandlerReference = $(this).val();
		var params = {
			ajax_Action: "editSheetAccess-flag-private-signups",
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: GLOBAL_confirmHandlerReference
		};
		updateSheetAccess(params);
	});

	$(".access_by_course_ckboxes").on("change", function () {
		var doAction = 'editSheetAccess-access-by-course-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-course-add';
		}
		var params = {
			ajax_Action: doAction,
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: $(this).attr('data-permval')
		};
		updateSheetAccess(params);
	});

	$(".access_by_instructor_ckboxes").on("change", function () {
		var doAction = 'editSheetAccess-access-by-instructor-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-instructor-add';
		}
		var params = {
			ajax_Action: doAction,
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: $(this).attr('data-permval')
		};
		updateSheetAccess(params);
	});

	$("#access_by_role_teacher").on("change", function () {
		var doAction = 'editSheetAccess-access-by-role-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-role-add';
		}
		var params = {
			ajax_Action: doAction,
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: 'teacher'
		};
		updateSheetAccess(params);
	});

	$("#access_by_role_student").on("change", function () {
		var doAction = 'editSheetAccess-access-by-role-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-role-add';
		}
		var params = {
			ajax_Action: doAction,
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: 'student'
		};
		updateSheetAccess(params);
	});

	$("#access_by_any").on("change", function () {
		var doAction = 'editSheetAccess-access-by-any-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-any-add';
		}
		var params = {
			ajax_Action: doAction,
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: 'all'
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
			ajax_Action: doAction,
			ajax_Primary_ID: $("#hiddenSheetID").val(),
			ajax_Custom_Data: $(this).val()
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