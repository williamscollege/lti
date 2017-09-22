// ***************************
// declare global variables
// ***************************
var GLOBAL_confirmHandlerData = -1; // data value of element
var GLOBAL_confirmHandlerReference = -1; // data value of reference element (i.e. container, parent, etc.)
var GLOBAL_util_showConfirmBox = null; // hack to enable passing of JS values between fxns in different files


// ***************************
// Listeners
// ***************************
$(document).ready(function () {
	// create container to hold ajax messages; hide #page_alert_div
	// note: dkc removed dismiss button from screen alerts to standardize user behaviour
	// <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	$('#content_container').prepend('<div id="page_alert_div" class="alert alert-dismissible small" role="alert"><span id="page_alert_message"></span></div>');
	$('#page_alert_div').hide();

	// PrintArea: Print any specific div (the div is assigned a unique class called: "wms_print_XYZ"; eg, wms_print_CalSetup, wms_print_EditOne, wms_print_OpeningSignup)
	$(".wmsPrintArea").click(function () {
		var print_this_area = $(this).data("what-area-to-print");
		var options = {
			mode: "popup", // avoid mode: "iframe" (bug): big spaces created if multiple pages are to be printed
			standard: "html5"
		};
		// CSS overrides pre-existing max-height, removes scrollbar; otherwise, PrintArea jQuery plugin outputs to print only what is visible within visible boundary
		$("#openings-list-container, #container-my-signups, #container-others-signups").addClass("printareaPatch");
		$("div." + print_this_area).printArea(options);
		$("#openings-list-container, #container-my-signups, #container-others-signups").removeClass("printareaPatch");
	});

	// CSV output: download file
	function exportTableToCSV(csvText, filename) {
		csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csvText);
		$(this)
			.attr({
				'download': filename,
				'href': csvData,
				'target': '_blank'
			});
	}
	// This must be a hyperlink
	$(".wmsExportCSV").on('click', function (event) {
		//csv = '"sample1","sample2","cat","dog"';
		csv = $(".wms_export_CSV").text();
		//console.log(csv);
		// IF CSV, don't do event.preventDefault() or return false (we actually need this to be a typical hyperlink)
		exportTableToCSV.apply(this, [csv, 'export.csv']);
	});

});


// ***************************
// helper functions
// ***************************

// BootBox jQuery confirm box (helper function)
function showConfirmBox(ary) {
	// CPH: Moved this code to the callback function below.
	// console.dir(ary); // debugging
	//if (ary['ajax_action'] == 'sus-delete-opening') {
	//	var custom_data = $("input[name='custom_user_value']:checked").val();
	// }
	// else
	// if (ary['ajax_action'] == 'send-email-to-participants-for-opening-id') {
		//var custom_data = ary['subject_message_json'];
        //console.log("custom_data = "+JSON.parse(custom_data));
		// issue: showConfirmBox dialog naturally removes scrollbar from the layer below; if that layer is also a dialog, this can be bad for UI
		// solution: reintroduce scrollbar to modal
		//$("#modal-edit-opening").css("cssText", "overflow-x: hidden !important; overflow-y: auto !important; display: block !important;");
	//}


	bootbox.dialog({
		title: ary['title'],
		message: ary['message'],
		buttons: {
			success: {
				label: ary['label'],
				className: ary['class'],
				callback: function () {

                    if (ary['ajax_action'] == 'delete-opening') {
                        // console.log("callback function array is delete-opening");

						var custom_data = $("input[name='custom_user_value']:checked").val();
                        // console.log("callback function = "+ custom_data);

                    }


					if (ary['ajax_action'] == 'send-email-to-participants-for-opening-id') {

                        var custom_data = ary['subject_message_json'];

                        // issue: showConfirmBox dialog naturally removes scrollbar from the layer below; if that layer is also a dialog, this can be bad for UI
                        // solution: reintroduce scrollbar to modal
                        $("#modal-edit-opening").css("cssText", "overflow-x: hidden !important; overflow-y: auto !important; display: block !important;");

						// show button loading text (bootstrap) only after clicking "Send" button
						$("#notifyParticipantsButton").button('loading');

                        // console.log("callback function = "+ custom_data);

                    }

					// show status
					susUtil_setTransientAlert('progress', 'Working...');
					$.ajax({
						type: 'GET',
						url: ary['url'],
						cache: false,
						data: {
							'ajax_Action': ary['ajax_action'],
							'ajax_Primary_ID': ary['ajax_id'],
							'ajax_Custom_Data': custom_data // see above for how this custom value is set
						},
						dataType: 'json',
						error: function (data) {
							// console.log("error section"); console.dir(data);
							updateDOM(ary, false, data);
						},
						success: function (data) {
							// console.log("success section"); console.dir(data);
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
		// remove the list container from DOM for both: "Calendar View" overlay AND calendar "List View"
		$('.list-opening-id-' + openingID).parent().parent(".calendar-cell-openings").remove();
		$('#tabOpeningsListView .list-opening-id-' + openingID).parent(".opening-list-for-date").remove();
	}
	else {
		// additional openings still exist on this date...
		// remove single opening from DOM for both: "Calendar View" overlay AND calendar "List View"
		$('.list-opening-id-' + openingID).remove();
		$('#tabOpeningsListView .list-opening-id-' + openingID).remove();
	}
}

function updateDOM(action_ary, ret, data) {
	//console.dir(action_ary);
	//console.log(ret);
	//console.log(data);
	if (action_ary.ajax_action == 'copy-sheet') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Copied');
			// redirect: to newly copied sheet
			location.href = data["url_redirect"];
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'delete-sheetgroup') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Deleted');
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
			susUtil_setTransientAlert('success', 'Deleted');
			// remove element
			$('#btn-edit-sheet-id-' + GLOBAL_confirmHandlerData).closest('TR').remove();
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'delete-opening') {

        // console.log('Data: ' + parseInt(data.customData));

		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Deleted');
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
			susUtil_setTransientAlert('success', 'Deleted');

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
				$('#container-my-signups').html("<div class='bg-info'>You have not yet signed up for any sheet openings.<br />To sign-up, click on <strong>&quot;Available Openings&quot;</strong> (above).</div>");
			}
			if ($('#container-others-signups .list-signups').length == 0) {
				$('#container-others-signups').html("<div class='bg-info'>No one has signed up on your sheets.<br />To see your sheets, click on <strong>&quot;Sheets&quot;</strong> (above).</div>");
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
			susUtil_setTransientAlert('success', 'Deleted');
			// fetch count of remaining LI elements within this UL
			GLOBAL_calendar_fetchSignupsforOpening(GLOBAL_confirmHandlerReference);
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
		}
	}
	else if (action_ary.ajax_action == 'send-email-to-participants-for-opening-id') {
		if (ret) {
			// show status
			susUtil_setTransientAlert('success', 'Sent');
			// hide button, show confirmation text
			$('#notifyParticipantsButton').hide();
			$('#btnConfirmationText').html(data.html_output);
		}
		else {
			// error message
			susUtil_setTransientAlert('error', 'Failed: No action taken: ' + data.notes);
			// hide button, show confirmation text
			$('#notifyParticipantsButton').hide();
			$('#btnConfirmationText').html(data.html_output);
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
	}, 3000);
}


function randomString(strSize) {
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	for (var i = 0; i < strSize; i++)
		text += possible.charAt(Math.floor(Math.random() * possible.length));

	return text;
}

