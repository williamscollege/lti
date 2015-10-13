$(document).ready(function () {

	// ***************************
	// Listeners
	// ***************************

	// Copy sheet
	$(document).on("click", ".sus-copy-sheet", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-sheet-id');

		var params = {
			title: "Copy Sheet",
			message: '<p>Really create a copy of this sheet?<br /><br /><strong>&quot;' + $(this).prev().attr('data-for-sheet-name') + '&quot;</strong></p>' +
			'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;Copying this sheet will create a duplicate sheet with identical sheet info and sheet access, but without any openings or signups.</p>',
			label: "Copy Sheet",
			class: "btn btn-primary",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "copy-sheet",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	// Delete sheet
	$(document).on("click", ".sus-delete-sheet", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-sheet-id');

		var params = {
			title: "Delete Sheet",
			message: '<p>Really delete this sheet?<br /><br /><strong>&quot;' + $(this).prev().prev().attr('data-for-sheet-name') + '&quot;</strong></p>' +
			'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;Deleting this sheet will also delete any associated openings and cancel any signups. An alert will be sent to the owner of each cancelled signup.</p>',
			label: "Delete Sheet",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-sheet",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	// Delete sheetgroup
	$(document).on("click", ".sus-delete-sheetgroup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-sheetgroup-id');
		var params = {
			title: "Delete Group",
			message: '<p>Really delete this group?<br /><br /><strong>&quot;' + $(this).prev().attr('data-for-sheetgroup-name') + '&quot;</strong></p>' +
			'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;Deleting this group will also delete any sheets in this group, delete any associated openings and cancel any signups. An alert will be sent to the owner of each cancelled signup.</p>',
			label: "Delete Group",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-sheetgroup",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	// Add sheetgroup
	$(".sus-add-sheetgroup").click(function () {
		// reset form values in modal (solution for: the ESC key will dismiss a modal but fails to clear values per next usage)
		cleanUpForm("frmAjaxSheetgroup");

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
	});

	// form validation
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

			// show button loading text (bootstrap)
			$("#btnAjaxSheetgroupSubmit").button('loading');

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
				error: function (req, textStatus, err) {
					susUtil_setTransientAlert('error', "Error making ajax request: " + err.toString());
				},
				success: function (data) {
					// Cancel button: dismiss modal
					$("#btnAjaxSheetgroupCancel").click();

					if (data.status == 'success') {
						// remove error messages
						$('DIV.alert-danger').remove();

						// inject updates back into the DOM
						if (data.which_action == 'add-sheetgroup') {
							// * Add Sheetgroup *
							// update visible UI
							$("#container-add-new-group").after(data.html_output);
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
							$("#display-name-sheetgroup-id-" + sheetgroup_id).text(sheetgroup_name);
						}
						else {
							// error message
							susUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
						}
					}
					else {
						// error message
						susUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
					}
				}
			});
		}
	});

	// Cancel button: dismiss modal
	$('#btnAjaxSheetgroupCancel').click(function () {
		// reset form
		cleanUpForm("frmAjaxSheetgroup");
	});


	// ***************************
	// Cancel and cleanup (helper function)
	// ***************************

	function cleanUpForm(formName) {
		// reset form (using jquery validate plugin)
		validateAjaxSheetgroup.resetForm();

		// reset form (using standard jquery)
		$('#' + formName).trigger("reset");

		// manually remove input highlights
		$(".form-group").removeClass('success').removeClass('error');

		// reset submit button (avoid disabled state)
		$("#btnAjaxSheetgroupSubmit").button('reset');
	}

});