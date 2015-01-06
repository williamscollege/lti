$(document).ready(function () {

	// ***************************
	// helper functions
	// ***************************

	function showConfirmBox(ary) {
		alert(ary['ajax_action'] + ', ' + ary['ajax_id']);

		bootbox.dialog({
			title: ary['title'],
			message: ary['message'],
			buttons: {
				success: {
					label: ary['label'],
					className: ary['class'],
					callback: function () {
						// show status
						dfnUtil_setTransientAlert('progress', 'saving...');
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
		if (action == 'delete-sheetgroup') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'saved');
				// remove element
				$('#btn-edit-sheetgroup-id-' + GLOBAL_confirmHandlerData).closest('TABLE').remove();
			}
			else {
				// error message
				$("#btn-edit-sheetgroup-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		else if (action == 'delete-sheet') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'saved');
				// remove element
				$('#btn-edit-sheet-id-' + GLOBAL_confirmHandlerData).closest('TR').remove();
			}
			else {
				// error message
				$("#btn-edit-sheet-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
	}

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
			url: "../ajax_actions/ajax_sheetgroup.php",
			ajax_action: "delete-sheetgroup",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	// Add sheetgroup
	$(".sus-add-sheetgroup").click(function () {

		// update values in modal
		$("#ajaxSheetgroupLabel").text("Add Group");
		$("INPUT#ajaxSheetgroupAction").val("add-sheetgroup");
	});

	// Edit sheetgroup
	$(document).on("click", ".sus-edit-sheetgroup", function (evt) {

		// fetch values from DOM
		var sheetgroup_id = $(this).attr("data-for-sheetgroup-id");
		var sheetgroup_name = $(this).attr("data-for-sheetgroup-name");
		var sheetgroup_description = $(this).attr("data-for-sheetgroup-description");
		var sheetgroup_max_total = $(this).attr("data-for-sheetgroup-max-total");
		var sheetgroup_max_pending = $(this).attr("data-for-sheetgroup-max-pending");

		// update values in modal
		$("#ajaxSheetgroupLabel").text("Edit Group");
		$("INPUT#ajaxSheetgroupAction").val("edit-sheetgroup");
		$("INPUT#ajaxSheetgroupID").val(sheetgroup_id);
		$("INPUT#ajaxSheetgroupName").val(sheetgroup_name);
		$("TEXTAREA#ajaxSheetgroupDescription").val(sheetgroup_description);
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
				data: {
					ajaxVal_Action: action,
					ajaxVal_OwnerUserID: owner_user_id,
					ajaxVal_SheetgroupID: sheetgroup_id,
					ajaxVal_Name: sheetgroup_name,
					ajaxVal_Description: sheetgroup_description,
					ajaxVal_MaxTotal: sheetgroup_max_total,
					ajaxVal_MaxPending: sheetgroup_max_pending
				},
				dataType: 'json',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxSheetgroupCancel").click();

					if (data.status == 'success') {
						// remove error messages
						$('DIV.alert-error').remove();

						alert(data.which_action); // debugging

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