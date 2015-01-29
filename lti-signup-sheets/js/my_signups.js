$(document).ready(function () {

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// ***************************
	// helper functions
	// ***************************

	// BootBox jQuery helper function
	function showConfirmBox(ary) {
		// alert(ary['ajax_action'] + ', ' + GLOBAL_confirmHandlerReference + ', ' + ary['ajax_id']);
		bootbox.dialog({
			title: ary['title'],
			message: ary['message'],
			buttons: {
				success: {
					label: ary['label'],
					className: ary['class'],
					callback: function () {
						// show status
						dfnUtil_setTransientAlert('progress', 'Saving...');
						$.ajax({
							type: 'GET',
							url: ary['url'],
							cache: false,
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
		if (action == 'delete-signup') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');

				// fetch count of remaining LI elements within this UL
				if ( $('#group-signups-for-opening-id-' + GLOBAL_confirmHandlerReference + ' UL LI').length > 1 ){
					// remove only this one LI item
					// alert('remove only this one LI item');
					$('#btn-remove-signup-id-' + GLOBAL_confirmHandlerData).closest('LI').remove();
				}
				else{
					// remove entire container shell (this is the last LI item in this group)
					// alert('this is the last LI item in this group; remove the entire container shell');
					$('#group-signups-for-opening-id-' + GLOBAL_confirmHandlerReference).remove();
				}
			}
			else {
				// error message
				$("#btn-remove-signup-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
	}

	// ***************************
	// Listeners
	// ***************************

	// Delete signup
	$(document).on("click", ".sus-delete-signup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "Really delete this signup for <strong>&quot;" + $(this).attr('data-for-signup-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

});