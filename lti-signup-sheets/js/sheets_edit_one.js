$(document).ready(function () {

	// initial default condition: focus cursor
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
			inputSheetName: {
				required: "Please enter a sheet name",
				minlength: "Please enter a longer sheet name"
			}
		},
		//errorPlacement: function(label, element) {
		//	label.addClass('wms-validation-fix');
		//	label.insertAfter(element);
		//},
		//wrapper: 'span',
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
		//success: function (element) {
		//	//.text('OK!').addClass('valid')
		//},
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
		$("#display_opening_signup_details").prepend('<a href="#" title="close" id="wms_demo_close_x" class="close"><span aria-hidden="true">&times;</span></a>');
	});

	// close opening signup details (DOM overlay previously copied from calendar)
	$(document).on('click', '#wms_demo_close_x', function () {
		$("#toggle_openings_instructions").show();
		$("#display_opening_signup_details").hide();
	});

	// select list: redirect to new page on change
	$("#breadcrumbs_select_list").change(function () {
		var cur_url = location.href;
		var base_url = cur_url.split(".php");
		location.href = ( base_url[0] + ".php?sheet=" + $(this).val() );
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

	// Set initial condition: hide history
	$("#tabOpeningsListView .toggle_opening_history").hide();

	// Display optional history
	$('#link_for_history_openings').click(function () {
		if ($("#tabOpeningsListView .toggle_opening_history").hasClass('wmsToggle')) {
			// hide history
			$("#tabOpeningsListView .toggle_opening_history").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
			$("#link_for_history_openings").text('show history');
		}
		else {
			// show history
			$("#tabOpeningsListView .toggle_opening_history").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
			$("#link_for_history_openings").text('hide history');
		}
	});

});