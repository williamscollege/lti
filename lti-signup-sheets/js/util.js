// ***************************
// declare global variables
// ***************************
var GLOBAL_confirmHandlerData = -1; // data value of element
var GLOBAL_confirmHandlerReference = -1; // data value of reference element (i.e. container, parent, etc.)
var GLOBAL_util_showConfirmBox = null; // hack to enable passing of JS values between fxns in different files


// ***************************
// Listeners: (NOTE: could put this directly in the HTML or in a footer file or some such, but doing it here consolidates the code)
// ***************************
$(document).ready(function () {
	// create container to hold ajax messages; hide #page_alert_div
	$('#content_container').prepend('<div id="page_alert_div" class="alert alert-dismissible small" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span id="page_alert_message"></span></div>');
	$('#page_alert_div').hide();

	// Enable PrintArea for Area1
	$(".wmsPrintArea1").click(function () {
		var options = {
			mode: "iframe",
			standard: "html5"
		};
		$("div.PrintArea.Area1").printArea([options]);
	});

	// Enable PrintArea for Area2
	$(".wmsPrintArea2").click(function () {
		var options = {
			mode: "iframe",
			standard: "html5"
		};
		$("div.PrintArea.Area2").printArea([options]);
	});
});


// ***************************
// helper functions
// ***************************

// BootBox jQuery confirm box (helper function)
function showConfirmBox(ary) {
	// console.dir(ary);
	bootbox.dialog({
		title: ary['title'],
		message: ary['message'],
		buttons: {
			success: {
				label: ary['label'],
				className: ary['class'],
				callback: function () {
					// show status
					susUtil_setTransientAlert('progress', 'Saving...');
					$.ajax({
						type: 'GET',
						url: ary['url'],
						cache: false,
						data: {
							'ajax_Action': ary['ajax_action'],
							'ajax_Primary_ID': ary['ajax_id'],
							'ajax_Custom_Data': $("input[name='custom_user_value']:checked").val() // custom value, currently only from ".sus-delete-opening" (calendar.js)
						},
						dataType: 'json',
						error: function (data) {
							updateDOM(ary, false, data);
						},
						success: function (data) {
							// console.dir(data);
							if (data.status == 'success') {
								// remove element
								updateDOM(ary, true, data);
							}
							else {
								// error message
								updateDOM(ary, false, data);
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

function helper_Remove_DOM_Elements(openingID) {
	// check to see if this the last opening on this date
	var countRemainingOpenings = $('.list-opening-id-' + openingID).siblings(".list-opening").length;

	if (countRemainingOpenings == 0) {
		// this is the last opening on this date!
		// remove the list container from DOM for both: "Calendar Openings" overlay AND calendar "List Openings"
		$('.list-opening-id-' + openingID).parent().parent(".calendar-cell-openings").remove();
		$('#tabOpeningsList .list-opening-id-' + openingID).parent(".opening-list-for-date").remove();
	}
	else {
		// additional openings still exist on this date...
		// remove single opening from DOM for both: "Calendar Openings" overlay AND calendar "List Openings"
		$('.list-opening-id-' + openingID).remove();
		$('#tabOpeningsList .list-opening-id-' + openingID).remove();
	}
}

function updateDOM(action_ary, ret, data) {
	// console.dir(action_ary);
	if (action_ary.ajax_action == 'delete-sheetgroup') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Saved');
			// remove element
			$('#btn-edit-sheetgroup-id-' + GLOBAL_confirmHandlerData).closest('TABLE').remove();
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'delete-sheet') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Saved');
			// remove element
			$('#btn-edit-sheet-id-' + GLOBAL_confirmHandlerData).closest('TR').remove();
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'delete-opening') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Saved');
			switch (parseInt(data.customData)) {
				case 0:
					// delete only this opening
					//console.log('reached case 0. customData = ' + parseInt(data.customData));
					//console.dir(data.updateIDs_ary);

					var openingID = GLOBAL_confirmHandlerData;
					helper_Remove_DOM_Elements(openingID);

					break;
				case 1:
					// delete all openings for this single day
					//console.log('reached case 1. customData = ' + parseInt(data.customData));
					//console.dir(data.updateIDs_ary);

					var openingID = GLOBAL_confirmHandlerData;

					for (i = 0; i < data.updateIDs_ary.length; i++) {
						// loop through returned IDs, and remove each from DOM
						openingID = parseInt(data.updateIDs_ary[i]);
						helper_Remove_DOM_Elements(openingID);
					}
					break;
				case 2:
					// delete this and all future openings in this series
					//console.log('reached case 2. customData = ' + parseInt(data.customData));
					//console.dir(data.updateIDs_ary);

					var openingID = GLOBAL_confirmHandlerData;

					for (i = 0; i < data.updateIDs_ary.length; i++) {
						// loop through returned IDs, and remove each from DOM
						openingID = parseInt(data.updateIDs_ary[i]);
						helper_Remove_DOM_Elements(openingID);
					}
					break;
				case 3:
					// delete this and all past and future openings in this series
					//console.log('reached case 3. customData = ' + parseInt(data.customData));
					//console.dir(data.updateIDs_ary);

					var openingID = GLOBAL_confirmHandlerData;

					for (i = 0; i < data.updateIDs_ary.length; i++) {
						// loop through returned IDs, and remove each from DOM
						openingID = parseInt(data.updateIDs_ary[i]);
						helper_Remove_DOM_Elements(openingID);
					}
					break;
				default:
					// default condition (failsafe)
					var openingID = GLOBAL_confirmHandlerData;
					helper_Remove_DOM_Elements(openingID);
					break;
			}
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'delete-signup') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Saved');

			// count remaining signups within this opening
			var countMySignupsRemaining = $('#tabMySignups .list-signup-id-' + GLOBAL_confirmHandlerData).siblings(".list-signups").length;
			var countOthersSignupsRemaining = $('#tabOthersSignups .list-signup-id-' + GLOBAL_confirmHandlerData).siblings(".list-signups").length;
			// console.log('countMySignupsRemaining = ' + countMySignupsRemaining + ', countOthersSignupsRemaining = ' + countOthersSignupsRemaining);

			// My Signups: determine if signup_id exists in DOM
			if ($('#tabMySignups .list-signup-id-' + GLOBAL_confirmHandlerData).length > 0) {
				if (countMySignupsRemaining == 0) {
					// is this the last opening on this date?
					if ($('#tabMySignups .list-opening-id-' + GLOBAL_confirmHandlerReference).siblings(".list-openings").length == 0) {
						// this is the last opening on this date: remove this date from DOM
						$('#tabMySignups .list-opening-id-' + GLOBAL_confirmHandlerReference).parent('.opening-list-for-date').remove();
					}
					else {
						// this is the last signup on this opening: remove this opening from DOM
						$('#tabMySignups .list-opening-id-' + GLOBAL_confirmHandlerReference).remove();
					}
				}
				else {
					// additional signups still exist on this opening: remove only this signup from DOM
					$('#tabMySignups .list-signup-id-' + GLOBAL_confirmHandlerData).remove();
				}
			}

			// Sign-ups on my Sheets: determine if signup_id exists in DOM
			if ($('#tabOthersSignups .list-signup-id-' + GLOBAL_confirmHandlerData).length > 0) {
				if (countOthersSignupsRemaining == 0) {
					// is this the last opening on this date?
					if ($('#tabOthersSignups .list-opening-id-' + GLOBAL_confirmHandlerReference).siblings(".list-openings").length == 0) {
						// this is the last opening on this date: remove this date from DOM
						$('#tabOthersSignups .list-opening-id-' + GLOBAL_confirmHandlerReference).parent('.opening-list-for-date').remove();
					}
					else {
						// this is the last signup on this opening: remove this opening from DOM
						$('#tabOthersSignups .list-opening-id-' + GLOBAL_confirmHandlerReference).remove();
					}
				}
				else {
					// additional signups still exist on this opening: remove only this signup from DOM
					$('#tabOthersSignups .list-signup-id-' + GLOBAL_confirmHandlerData).remove();
				}
			}

			// restore default text if no signups remain in either container
			if ($('#container-my-signups .list-signups').length == 0) {
				$('#container-my-signups').html("<div class='bg-info'>You have not yet signed up for any sheet openings.<br />To sign-up, click on &quot;Available Openings&quot; (above).</div>");
			}
			if ($('#container-others-signups .list-signups').length == 0) {
				$('#container-others-signups').html("<div class='bg-info'>No one has signed up on your sheets.</div>");
			}
			// console.log('container-my-signups = ' + $('#container-my-signups .list-signups').length + ', container-others-signups = ' + $('#container-others-signups .list-signups').length);
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'delete-signup-from-edit-opening-modal') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Saved');
			// fetch count of remaining LI elements within this UL
			GLOBAL_calendar_fetchSignupsforOpening(GLOBAL_confirmHandlerReference);
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
}

function appRootPath() {
	return "/GITHUB/lti/lti-signup-sheets";
}


// REQUIRES: a div of id page_alert_div
function susUtil_setTransientAlert(alertType, alertMessage) {
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
}


function randomString(strSize) {
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	for (var i = 0; i < strSize; i++)
		text += possible.charAt(Math.floor(Math.random() * possible.length));

	return text;
}

