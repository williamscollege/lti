$(document).ready(function () {

	$("#input_sheet_name").focus().select();

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// Calendar datepicker
	$("#inputSheetDateStart, #inputSheetDateEnd").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		yearRange: '-4:+4'
	});


	// New Sheet: set default date (today)
	if ($("#inputSheetDateStart").val() == "") {
		var today = new Date();
		$("#inputSheetDateStart").datepicker('setDate', today);

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
		GLOBAL_confirmHandlerData = $(this).attr('id');
		GLOBAL_confirmHandlerReference = $(GLOBAL_confirmHandlerData).val();
		var params = {
			//title: "Delete Signup",
			//message: "Really delete this signup for <strong>&quot;" + $(this).attr('data-for-signup-name') + "&quot;</strong>?",
			//label: "Delete Signup",
			//class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "edit-sheet-access",
			ajax_id: GLOBAL_confirmHandlerData,
			ajax_val: GLOBAL_confirmHandlerReference
		};
		updateSheetAccess(params);
	});

	/*

	 $("input[name=radioSignupPrivacy]").change(function (e) {
	 var ele_id = $(this).prop('id');
	 var ele_val = $(this).val();
	 var params = {

	 }
	 ary = [];
	 alert(ele_id + ' , ' + ele_val);

	 updateSheetAccess(ele_id, ele_val);
	 });*/
	/*	$('input:radio').on('change', function(){
	 //access value of changed radio group with $(this).val()
	 });*/


	function updateSheetAccess(ary) {
		alert(ary['url'] + '\n ' + ary['ajax_action'] + '\n ' + ary['ajax_id'] + '\n' + ary['ajax_val']);
		// show status
		dfnUtil_setTransientAlert('progress', 'Saving...');
		$.ajax({
			type: 'GET',
			url: ary['url'],
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

});