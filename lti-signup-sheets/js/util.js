// ***************************
// declare global variables
// ***************************
var GLOBAL_confirmHandlerData = -1; // data value of element
var GLOBAL_confirmHandlerReference = -1; // data value of reference element (i.e. container, parent, etc.)


// ***************************
// Listeners: (NOTE: could put this directly in the HTML or in a footer file or some such, but doing it here consolidates the code)

var GLOBAL_util_showConfirmBox = null;

// ***************************
$(document).ready(function () {
	// create container to hold ajax messages; hide #page_alert_div
	$('#parent_container').prepend('<div id="page_alert_div" class="alert alert-dismissible small" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span id="page_alert_message"></span></div>');
	$('#page_alert_div').hide();
//});

// ***************************
// helper functions
// ***************************

// BootBox jQuery confirm box (helper function)
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

	GLOBAL_util_showConfirmBox = showConfirmBox;

	function updateDOM(action, ret) {
		if (action == 'delete-sheetgroup') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');
				// remove element
				$('#btn-edit-sheetgroup-id-' + GLOBAL_confirmHandlerData).closest('TABLE').remove();
			}
			else {
				// error message
				$("#btn-edit-sheetgroup-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		else if (action == 'delete-sheet') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');
				// remove element
				$('#btn-edit-sheet-id-' + GLOBAL_confirmHandlerData).closest('TR').remove();
			}
			else {
				// error message
				$("#btn-edit-sheet-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		else if (action == 'delete-opening') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');

				// check to see if this the last opening on this date
				var countRemainingOpenings = $('#list-opening-id-' + GLOBAL_confirmHandlerData).siblings(".list-opening").length;

				if (countRemainingOpenings == 0) {
					// this is the last opening on this date!
					// remove the list container from DOM for both: "Calendar Openings" overlay AND calendar "List Openings"
					$('#list-opening-id-' + GLOBAL_confirmHandlerData).parent().parent(".calendar-cell-openings").remove();
					$('#tabOpeningsList #list-opening-id-' + GLOBAL_confirmHandlerData).parent(".opening-list-for-date").remove();
				}
				else {
					// additional openings still exist on this date...
					// remove single opening from DOM for both: "Calendar Openings" overlay AND calendar "List Openings"
					$('#list-opening-id-' + GLOBAL_confirmHandlerData).remove();
					$('#tabOpeningsList #list-opening-id-' + GLOBAL_confirmHandlerData).remove();
				}
			}
			else {
				// error message
				$("#list-opening-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		else if (action == 'delete-signup') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');

				// fetch count of remaining LI elements within this UL
				if ($('#group-signups-for-opening-id-' + GLOBAL_confirmHandlerReference + ' UL LI').length > 1) {
					// remove only this one LI item
					// alert('remove only this one LI item');
					$('#btn-remove-signup-id-' + GLOBAL_confirmHandlerData).closest('LI').remove();
				}
				else {
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
		else if (action == 'delete-signup-from-edit-opening-modal') {
			if (ret) {
				// show status
				dfnUtil_setTransientAlert('success', 'Saved');

				// fetch count of remaining LI elements within this UL
				GLOBAL_calendar_fetchSignupsforOpening(GLOBAL_confirmHandlerReference);
				//if ( $('#group-signups-for-opening-id-' + GLOBAL_confirmHandlerReference + ' UL LI').length > 1 ){
				// remove only this one LI item
				// alert('remove only this one LI item');
				//$('#btn-remove-signup-id-' + GLOBAL_confirmHandlerData).closest('LI').remove();
				//}
				//else{
				//	// remove entire container shell (this is the last LI item in this group)
				//	// alert('this is the last LI item in this group; remove the entire container shell');
				//	$('#group-signups-for-opening-id-' + GLOBAL_confirmHandlerReference).remove();
				//}
			}
			else {
				// error message
				$("#btn-remove-signup-id-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
	}

	function appRootPath() {
		return "/GITHUB/lti/lti-signup-sheets";
	}


// REQUIRES: a div of id page_alert_div
	function dfnUtil_setTransientAlert(alertType, alertMessage) {

		// show the pre-existing alert button in DOM
		$('#page_alert_div').show();

		if (alertType == 'progress') {
			$('#page_alert_div').addClass('alert-success').removeClass('alert-danger').removeClass("alert-info");
			$('#page_alert_message').html('<i class="glyphicon glyphicon-time"></i> ' + alertMessage);
		}
		else if (alertType == 'success') {
			// alert('INSIDE SUCCESS: ' + alertType + ',' + alertMessage);
			$('#page_alert_div').addClass('alert-success').removeClass('alert-danger').removeClass("alert-info");
			$('#page_alert_message').html('<i class="glyphicon glyphicon-ok"></i> ' + alertMessage);
		}
		else if (alertType == 'error') {
			$('#page_alert_div').removeClass('alert-success').addClass('alert-danger').removeClass("alert-info");
			$('#page_alert_message').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + alertMessage);
		}

		// pause for user to read the alert, then hide alert button
		setTimeout(function () {
			$('#page_alert_div').hide();
		}, 5000);

		// Note: This issue seems to be resolved: how to queue multiple rapidly clicked ajax actions and have them all fire and report back
		//.queue(function() {
		//		$( this ).toggleClass( "red" ).dequeue();
		//	})
	}


	function dfnUtil_launchConfirm(msg, handler) {
		$('#confirmModal .modal-body').html(msg);
//    $('#confirmModal').modal({show:'true', backdrop:'static'});
		$('#confirmModal').modal({show: 'true'});
		$('#confirmModal #confirm-yes').focus();
		$('#confirm-yes').off("click");
		$('#confirm-yes').click(handler);
	}


	function randomString(strSize) {
		var text = "";
		var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

		for (var i = 0; i < strSize; i++)
			text += possible.charAt(Math.floor(Math.random() * possible.length));

		return text;
	}

});

