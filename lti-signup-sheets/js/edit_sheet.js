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
		var today =  new Date();
		$("#inputSheetDateStart").datepicker('setDate',today);

		var futureDate = new Date(today.getTime());
		futureDate.setMonth( futureDate.getMonth( ) + 1 );
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

});