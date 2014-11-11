/* This function causes an alert to be displayed on the page. If the alert is of type success or error then it will fade away in a few seconds
REQUIRES: a div of id page_alert
 */

function appRootPath() {
    return "/digitalfieldnotebooks";
}

function dfnUtil_setTransientAlert(alertType,alertMessage,optionalReferenceElt) {
    if (optionalReferenceElt !== undefined) {
        $('#page_alert').css('top', optionalReferenceElt.position().top);
        $('#page_alert').css('left', optionalReferenceElt.position().left*1 + optionalReferenceElt.css('width')*1 + 100);
    }
    else {
        $('#page_alert').css('top', 100);
        $('#page_alert').css('left', 500);
    }
    $('#page_alert').removeClass("in_progress_alert");
    $('#page_alert').removeClass("success_alert");
    $('#page_alert').removeClass("error_alert");
    if (alertType == 'progress') {
        $('#page_alert').css("display",'block');
        $('#page_alert').html('<i class="icon-time"></i> '+alertMessage);
        $('#page_alert').addClass("in_progress_alert");
        $('#page_alert').stop();
        $('#page_alert').css("opacity",1);
    }
    else if (alertType == 'success') {
        $('#page_alert').css("display",'block');
        $('#page_alert').html('<i class="icon-ok"></i> '+alertMessage);
        $('#page_alert').addClass("success_alert");
        $('#page_alert').stop();
        $('#page_alert').css("opacity",1);
        $('#page_alert').fadeOut({duration: 3000, queue: false}); //,function(){$('#page_alert').addClass("hide");})
        //alert('success ta');
    }
    else if (alertType == 'error') {
//        console.log("alert position top is "+$('#page_alert').css("top"));
        $('#page_alert').css("display",'block');
        $('#page_alert').html('<i class="icon-exclamation-sign"></i> '+alertMessage);
        $('#page_alert').addClass("error_alert");
        $('#page_alert').stop();
        $('#page_alert').css("opacity",1);
        $('#page_alert').fadeOut({duration: 10000, queue: false});//,function(){$('#page_alert').addClass("hide");})
    }
}
// NOTE: could put this directly in the HTML or in a footer file or some such, but doing it here consolidates the code
$(document).ready(function () {
    $('body').append('<div id="page_alert" class="transient_alert in_progress_alert hide alert">Saved</div>');

    $('.show-hide-control').click(function() {
        var target_id = $(this).attr("data-for_elt_id");
        $("#"+target_id).toggle('display');
    });
});


function dfnUtil_launchConfirm(msg,handler) {
    $('#confirmModal .modal-body').html(msg);
//    $('#confirmModal').modal({show:'true', backdrop:'static'});
    $('#confirmModal').modal({show:'true'});
    $('#confirmModal #confirm-yes').focus();
    $('#confirm-yes').off("click");
    $('#confirm-yes').click(handler);
}
var GLOBAL_confirmHandlerData = -1;

// NOTE: could put this directly in the HTML or in a footer file or some such, but doing it here consolidates the code
$(document).ready(function () {
    $('body').append('<div id="confirmModal" class="modal hide confirmationDialog" data-backdrop="false">' +
        '<div class="modal-header"><a href="#" class="close" data-dismiss="modal">&times;</a><h3 class="confirmationTitle"></h3></div>' +
        '<div class="modal-body"></div>' +
        '<div class="modal-footer">' +
        '<input type="button" id="confirm-yes" class="btn btn-danger" data-dismiss="modal" value="Yes"/>' +
        '<input type="button" id="confirm-no" class="btn btn-cancel" data-dismiss="modal" value="No"/>' +
        '</div>' +
        '</div>');
});

function randomString(strSize)
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < strSize; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}
