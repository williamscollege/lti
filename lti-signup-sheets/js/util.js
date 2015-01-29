// declare global variables
var GLOBAL_confirmHandlerData = -1; // data value of element
var GLOBAL_confirmHandlerReference = -1; // data value of reference element (i.e. container, parent, etc.)


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
	setTimeout(function() {
		$('#page_alert_div').hide();
	}, 5000);

	// Note: This issue seems to be resolved.
	// TODO: how to queue ajax actions to ensure that multiple rapidly clicked delete actions will update the UI (currently, the DB updates correctly, but UI fails to update)
	//.queue(function() {
	//		$( this ).toggleClass( "red" ).dequeue();
	//	})
}


// NOTE: could put this directly in the HTML or in a footer file or some such, but doing it here consolidates the code
$(document).ready(function () {
	$('#parent_container').prepend('<div id="page_alert_div" class="alert alert-dismissible small" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span id="page_alert_message"></span></div>');
	// hide message button placeholder
	$('#page_alert_div').hide();

	// TODO obsolete snippet
	$('.show-hide-control').click(function () {
		var target_id = $(this).attr("data-for_elt_id");
		$("#" + target_id).toggle('display');
	});
});


function dfnUtil_launchConfirm(msg, handler) {
	$('#confirmModal .modal-body').html(msg);
//    $('#confirmModal').modal({show:'true', backdrop:'static'});
	$('#confirmModal').modal({show: 'true'});
	$('#confirmModal #confirm-yes').focus();
	$('#confirm-yes').off("click");
	$('#confirm-yes').click(handler);
}


// NOTE: could put this directly in the HTML or in a footer file or some such, but doing it here consolidates the code
//$(document).ready(function () {
//    $('body').append('<div id="confirmModal" class="modal confirmationDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
//        '<div class="modal-dialog">' +
//        '<div class="modal-content">' +
//        '<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title"></h4></div>' +
//        '<div class="modal-body"></div>' +
//        '<div class="modal-footer">' +
//        '<button type="button" id="confirm-yes" class="btn btn-primary" data-dismiss="modal">Save</button>' +
//        '<button type="button" id="confirm-no" class="btn btn-default">Close</button>' +
//        '</div>' +
//        '</div>' +
//        '</div>' +
//        '</div>');
//});


function randomString(strSize) {
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	for (var i = 0; i < strSize; i++)
		text += possible.charAt(Math.floor(Math.random() * possible.length));

	return text;
}
