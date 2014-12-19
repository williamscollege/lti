$(document).ready(function () {

	// ***************************
	// Modal listeners
	// ***************************

	// ***************************
	// Dynamic Modal listeners
	// ***************************


	// Add sheetgroup
	$(".sus-add-sheetgroup").click(function () {
		// update modal values
		$("#ajaxSheetgroupLabel").text("Add Group");
		$("INPUT#ajaxSheetgroupAction").val("add-sheetgroup");
	});

	// Edit sheetgroup (dynamic: .on gets values changed by ajax)
	$(".sus-edit-sheetgroup").on("click", function () {
		var sheetgroup_id = $(this).attr("data-for-sheetgroup-id");
		var sheetgroup_name = $(this).attr("data-for-sheetgroup-name");
		var sheetgroup_description = $(this).attr("data-for-sheetgroup-description");
		var sheetgroup_max_total = $(this).attr("data-for-sheetgroup-max-total");
		var sheetgroup_max_pending = $(this).attr("data-for-sheetgroup-max-pending");
		// update modal values
		$("#ajaxSheetgroupLabel").text("Edit Group");
		$("INPUT#ajaxSheetgroupAction").val("edit-sheetgroup");
		$("INPUT#ajaxSheetgroupID").val(sheetgroup_id);
		$("INPUT#ajaxSheetgroupName").val(sheetgroup_name);
		$("TEXTAREA#ajaxSheetgroupDescription").val(sheetgroup_description);
		$("#ajaxSheetgroupMaxTotal").val(sheetgroup_max_total);
		$("#ajaxSheetgroupMaxPending").val(sheetgroup_max_pending);

		// debugging
		// alert('1) ' + "\n" + sheetgroup_id + "\n" + sheetgroup_name + "\n" + sheetgroup_description + "\n" + sheetgroup_max_total + "\n" + sheetgroup_max_pending);
	});


	var validateAjaxSheetgroup = $('#frmAjaxSheetgroup').validate({
		rules: {
			ajaxSheetgroupName: {
				required: true,
				minlength: 2
			},
			ajaxSheetgroupDescription: {
				required: true,
				minlength: 2
			}
		},
		highlight: function (element) {
			$(element).closest('.form-group').removeClass('success').addClass('error'); //.removeClass('success')
		},
		success: function (element) {
			element
				//.text('OK!').addClass('valid')
				.closest('.form-group').addClass('success').removeClass('error');//.addClass('success');
		},
		submitHandler: function (form) {
			var formName = $("#frmAjaxSheetgroup").attr('name');		// get name from the form element

			// show loading text (button)
			$("#btnAjaxSheetgroupSubmit").button('loading'); // bootstrap button label method
			var action = $('#' + formName + ' #ajaxSheetgroupAction').val();
			var sheetgroup_id = $('#' + formName + ' #ajaxSheetgroupID').val();
			var sheetgroup_name = $('#' + formName + ' #ajaxSheetgroupName').val();
			var sheetgroup_description = $('#' + formName + ' #ajaxSheetgroupDescription').val();
			var sheetgroup_max_total = $('#' + formName + ' #ajaxSheetgroupMaxTotal').val();
			var sheetgroup_max_pending = $('#' + formName + ' #ajaxSheetgroupMaxPending').val();
			// debugging
			alert('2) url=' + $("#frmAjaxSheetgroup").attr('action') + "\n" + formName + "\n" + action + "\n" + sheetgroup_id + "\n" + sheetgroup_name + "\n" + sheetgroup_description + "\n" + sheetgroup_max_total + "\n" + sheetgroup_max_pending);

			$.ajax({
				type: 'POST',
				url: $("#frmAjaxSheetgroup").attr('action'),
				data: {
					ajaxVal_Action: action,
					ajaxVal_SheetgroupID: sheetgroup_id,
					ajaxVal_Name: sheetgroup_name,
					ajaxVal_Description: sheetgroup_description,
					ajaxVal_Max_Total: sheetgroup_max_total,
					ajaxVal_Max_Pending: sheetgroup_max_pending
				},
				dataType: 'json',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxSheetgroupCancel").click();

					if (data.status == 'success') {
						// remove error messages
						$('DIV.alert-error').remove();

						alert('fish');
						$("#DKCTEST").prepend(data.which_action);
						$("#DKCTEST").append(data.html_output);
//TODO add html_output back into the DOM in correct location
						/*if (data.which_action == 'add-sheetgroup') {
						 // update element with resultant ajax data
						 $("UL#displayAllSheetgroups").append(data.html_output);
						 $("UL#displayAllSheetgroups").append(data.html_output);
						 }
						 else if (data.which_action == 'edit-sheetgroup') {
						 // update button data attributes
						 $("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-name", sheetgroup_name);
						 $("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-description", sheetgroup_description);
						 // update visible info
						 $("span#sheetgroupid-" + sheetgroup_id).html("<strong>" + sheetgroup_name + ": </strong>" + sheetgroup_description);
						 }*/
					}
					else {
						// error message
						$("#DKCTEST").text("<div><p>AJAX ERROR HERE!</p></div>");
						//$("UL#displayAllSheetgroups").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
					}
				}
			});
		}
	});


	// ***************************
	// Cancel and cleanup
	// ***************************

	function cleanUpForm(formName) {
		// reset forms
		validateAjaxSheetgroup.resetForm();
		// manually remove input highlights
		$(".form-group").removeClass('success').removeClass('error');
	}

	$('#btnAjaxSheetgroupCancel').click(function () {
		cleanUpForm("frmAjaxSheetgroup");
		// clear and reset form
		$("#frmAjaxSheetgroup textarea").val('');
		$("#frmAjaxSheetgroup input[type=text]").val('');
		$("#frmAjaxSheetgroup input[type=radio]").attr("checked", false);
		// reset submit button (avoid disabled state)
		$("#btnAjaxSheetgroupSubmit").button('reset');
	});


});