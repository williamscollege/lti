$(document).ready(function () {

	// Add sheetgroup
	$(".sus-add-sheetgroup").on("click", function () {
		// update modal values
		$("#ajaxSheetgroupLabel").text("Add Group");
		$("INPUT#ajaxSheetgroupAction").val("add-sheetgroup");
	});


	// Edit sheetgroup
	$(".sus-edit-sheetgroup").on("click", function () {
		var sheetgroup_id = $(this).attr("data-for-sheetgroup-id");
		var sheetgroup_name = $(this).attr("data-for-sheetgroup-name");
		var sheetgroup_description = $(this).attr("data-for-sheetgroup-description");
		var sheetgroup_max_total = $(this).attr("data-for-sheetgroup-max-total");
		var sheetgroup_max_pending = $(this).attr("data-for-sheetgroup-max-pending");
		var sheetgroup_flag_is_default = $(this).attr("data-for-flag-is-default");
		// update modal values
		$("#ajaxSheetgroupLabel").text("Edit Group");
		$("INPUT#ajaxSheetgroupAction").val("edit-sheetgroup");
		$("INPUT#ajaxSheetgroupID").val(sheetgroup_id);
		$("INPUT#ajaxSheetgroupName").val(sheetgroup_name);
		$("TEXTAREA#ajaxSheetgroupDescription").val(sheetgroup_description);
		$("#ajaxSheetgroupMaxTotal").val(sheetgroup_max_total);
		$("#ajaxSheetgroupMaxPending").val(sheetgroup_max_pending);
		// show delete button on all groups except for the 'default' group
		if (sheetgroup_flag_is_default == 0) {
			$("#btnAjaxSheetgroupDelete").removeClass('hide');
		}
		else {
			$("#btnAjaxSheetgroupDelete").addClass('hide');
		}
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
			 // TODO WHY oh WHY is state whacked? second click through works... first click.. button clicked is a mystery
			 //$("BUTTON").on("click", function () {
			 //$("BUTTON").click(function () {
				// alert("button click alert next, maybe...");
				// alert(this.id);
			 //});


			var formName = $("#frmAjaxSheetgroup").attr('name');		// get name from the form element
			var action = $('#' + formName + ' #ajaxSheetgroupAction').val();
			var sheetgroup_id = $('#' + formName + ' #ajaxSheetgroupID').val();
			var sheetgroup_name = $('#' + formName + ' #ajaxSheetgroupName').val();
			var sheetgroup_description = $('#' + formName + ' #ajaxSheetgroupDescription').val();
			var sheetgroup_max_total = $('#' + formName + ' #ajaxSheetgroupMaxTotal').val();
			var sheetgroup_max_pending = $('#' + formName + ' #ajaxSheetgroupMaxPending').val();
			var sheetgroup_flag_is_default = $('#' + formName + ' #ajaxSheetgroupFlagIsDefault').val();

			$.ajax({
				type: 'GET',
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

						$("#DKCTEST").text(data.test);

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
	// Modal listeners
	// ***************************
	$("#btnAjaxSheetgroupDelete").click(function () {
		// TODO Add bootbox confirm dialog here (are you sure?)
		$("INPUT#ajaxSheetgroupAction").val("delete-sheetgroup");
	});
	$("#btnAjaxSheetgroupSubmit").click(function () {
		$("INPUT#ajaxSheetgroupAction").val("edit-sheetgroup");
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
		$("#frmAjaxSheetgroup input[type=text]").val('');
		$("#frmAjaxSheetgroup input[type=radio]").attr("checked", false);
		// reset submit button (avoid disabled state)
		$("#btnAjaxSheetgroupDelete").button('reset');
		$("#btnAjaxSheetgroupSubmit").button('reset');
	});

});