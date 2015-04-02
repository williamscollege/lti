$(document).ready(function () {

	// ***************************
	// helper functions
	// ***************************


	// ***************************
	// Listeners
	// ***************************

	// Delete sheetgroup
	$(document).on("click", ".sus-delete-sheetgroup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-sheetgroup-id');
		var params = {
			title: "Delete Group",
			message: "Any sheets in this group will be deleted. Really delete this group?<br /><br /><strong>&quot;" + $(this).parent('TH').prev().children("A").attr('data-for-sheetgroup-name') + "&quot;</strong>",
			label: "Delete Group",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-sheetgroup",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	// Delete sheet
	$(document).on("click", ".sus-delete-sheet", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-sheet-id');
		var params = {
			title: "Delete Sheet",
			message: "This sheet will be deleted. Really delete this sheet?<br /><br /><strong>&quot;" + $(this).parent('TD').prev().children("A").attr('data-for-sheet-name') + "&quot;</strong>",
			label: "Delete Sheet",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-sheet",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	// Add sheetgroup
	$(".sus-add-sheetgroup").click(function () {

		// update values in modal
		$("#ajaxSheetgroupLabel").text("Add Group");
		$("#ajaxSheetgroupAction").val("add-sheetgroup");
		$("#ajaxSheetgroupMaxTotal").val(); // required to set 'selected="selected"' as default within modal environment
		$("#ajaxSheetgroupMaxPending").val(); // required to set 'selected="selected"' as default within modal environment
	});

	// Edit sheetgroup
	$(document).on("click", ".sus-edit-sheetgroup", function () {

		// fetch values from DOM
		var sheetgroup_id = $(this).attr("data-for-sheetgroup-id");
		var sheetgroup_name = $(this).attr("data-for-sheetgroup-name");
		var sheetgroup_description = $(this).attr("data-for-sheetgroup-description");
		var sheetgroup_max_total = $(this).attr("data-for-sheetgroup-max-total");
		var sheetgroup_max_pending = $(this).attr("data-for-sheetgroup-max-pending");

		// update values in modal
		$("#ajaxSheetgroupLabel").text("Edit Group");
		$("#ajaxSheetgroupAction").val("edit-sheetgroup");
		$("#ajaxSheetgroupID").val(sheetgroup_id);
		$("#ajaxSheetgroupName").val(sheetgroup_name);
		$("#ajaxSheetgroupDescription").val(sheetgroup_description);
		$("#ajaxSheetgroupMaxTotal").val(sheetgroup_max_total);
		$("#ajaxSheetgroupMaxPending").val(sheetgroup_max_pending);

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
			var owner_user_id = $('#' + formName + ' #ajaxOwnerUserID').val();
			// ensure that add sheetgroup has integer value, instead of no value
			if ($('#' + formName + ' #ajaxSheetgroupID').val() != "") {
				var sheetgroup_id = $('#' + formName + ' #ajaxSheetgroupID').val();
			}
			else {
				var sheetgroup_id = 0;
			}
			var sheetgroup_name = $('#' + formName + ' #ajaxSheetgroupName').val();
			var sheetgroup_description = $('#' + formName + ' #ajaxSheetgroupDescription').val();
			var sheetgroup_max_total = $('#' + formName + ' #ajaxSheetgroupMaxTotal').val();
			var sheetgroup_max_pending = $('#' + formName + ' #ajaxSheetgroupMaxPending').val();
			// debugging
			//alert('2) url=' + $("#frmAjaxSheetgroup").attr('action') + "\n" + formName + "\n" + action + "\n" + owner_user_id + "\n" + sheetgroup_id + "\n" + sheetgroup_name + "\n" + sheetgroup_description + "\n" + sheetgroup_max_total + "\n" + sheetgroup_max_pending);

			$.ajax({
				type: 'POST',
				url: $("#frmAjaxSheetgroup").attr('action'),
				cache: false,
				data: {
					ajax_Action: action,
					ajax_Primary_ID: sheetgroup_id,
					ajax_OwnerUserID: owner_user_id,
					ajax_Name: sheetgroup_name,
					ajax_Description: sheetgroup_description,
					ajax_MaxTotal: sheetgroup_max_total,
					ajax_MaxPending: sheetgroup_max_pending
				},
				dataType: 'json',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxSheetgroupCancel").click();

					if (data.status == 'success') {
						// remove error messages
						$('DIV.alert-danger').remove();

						//alert(data.which_action); // debugging

						// inject updates back into the DOM
						if (data.which_action == 'add-sheetgroup') {
							// * Add Sheetgroup *

							// update visible UI
							$("#container-add-new-group").before(data.html_output);
						}
						else if (data.which_action == 'edit-sheetgroup') {
							// * Edit Sheetgroup *

							// update data attributes
							$("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-id", sheetgroup_id);
							$("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-name", sheetgroup_name);
							$("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-description", sheetgroup_description);
							$("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-max-total", sheetgroup_max_total);
							$("#btn-edit-sheetgroup-id-" + sheetgroup_id).attr("data-for-sheetgroup-max-pending", sheetgroup_max_pending);

							// update visible UI
							$("#btn-edit-sheetgroup-id-" + sheetgroup_id).text(sheetgroup_name);
						}
						else {
							// error message
							$("#DKCTEST").text("<div><p>AJAX ERROR HERE!</p></div>");
						}
					}
					else {
						// error message
						$("#DKCTEST").text("<div><p>AJAX ERROR HERE!</p></div>");
						//$("UL#displayAllSheetgroups").after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
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

		// manually clear modal values
		$("#ajaxSheetgroupID").val(0);
		$("#ajaxSheetgroupLabel").text('');
		$("#ajaxSheetgroupAction").val('');
		$("#frmAjaxSheetgroup textarea").val('');
		$("#frmAjaxSheetgroup input[type=text]").val('');
		$("#frmAjaxSheetgroup input[type=radio]").attr("checked", false);
		$("#frmAjaxSheetgroup select").val(0);

		// reset submit button (avoid disabled state)
		$("#btnAjaxSheetgroupSubmit").button('reset');
	});

});