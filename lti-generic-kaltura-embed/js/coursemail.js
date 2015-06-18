/***********************************************
 ** LTI Application: "Generic Kaltura Embed"
 ** Purpose: Build a dynamic LTI video player that will play the requested video based on Kaltura params (entry_id, wid) while leveraging Canvas LDAP authentication.
 ** Author: David Keiser-Clark, Williams College
 ***********************************************/

$(document).ready(function () {

	// Listener: Remove primary error message upon any valid checkbox selection
	$("INPUT[name=email_ckbox], #btn_add_all, #btn_add_students, #btn_add_tas, #btn_add_teachers").click(function () {
		if ($("#email_warning_msg").hasClass("show")) {
			$("#email_warning_msg").fadeOut("slow", function () {
				$(this).removeClass("show").addClass("hide");
			});
		}
	});

	// Listener: Select all checkboxes
	$("#btn_add_all").click(function () {
		$("INPUT[name=email_ckbox]").prop("checked", true);
	});

	// Listener: Remove all checkboxes
	$("#btn_remove_all").click(function () {
		$("INPUT[name=email_ckbox]").prop("checked", false);
	});

	// Listener: Any href button click
	$("a.btn,input[type=checkbox]").click(function () {
		updateCkBoxCounter();
	});

	// Listener: Count selected checkboxes. Display counter and warning messages
	/*
	 STRESS-TESTING CAPACITY:
	 - My testing shows Gmail (Windows7, Chrome browser version 36.0.1985.143 m) successfully can create a dynamic email with the following capacities:
	 - maximum of 261 short email addresses (totalling 6,582 characters)
	 - maximum of 171 short email addresses and full name descriptive text (totalling 6,749 characters)
	 - Alternate: Manually creating a Gmail email and pasting in desired list of email addresses enables more than 1,000 email addresses (unsure of limit)
	 */
	function updateCkBoxCounter() {
		var numCheckedBoxes = $("INPUT[name=email_ckbox]:checked").length;
		$("#displayCkBoxInteger").text('(' + numCheckedBoxes + ')');
		if (numCheckedBoxes > 230) { // 230 is max safe value; see "stress-testing capacity" notes above
			$("#btn_compose_email").addClass("disabled").addClass("wms-btn-grey");
			$("#icon_compose_email").removeClass("glyphicon-envelope").addClass("glyphicon-ban-circle");
			$("#displayCkBoxCounter").addClass("text-danger");
			$("#displayCkBoxInstructions").removeClass("hidden").addClass("show");
		}
		else {
			$("#btn_compose_email").removeClass("disabled").removeClass("wms-btn-grey");
			$("#icon_compose_email").removeClass("glyphicon-ban-circle").addClass("glyphicon-envelope");
			$("#displayCkBoxCounter").removeClass("text-danger");
			$("#displayCkBoxInstructions").removeClass("show").addClass("hidden");
		}
	}

	// Listener: Compose email in mail client
	/* NOTES: replace all runs of white spaces with html entity; cast object as string as replace() is only a function when the operand is a string */
	/* NOTES: checkedValues = checkedValues.toString().replace(/\s+/g, "%20"); */
	$("#btn_compose_email").click(function () {
		var checkedValues = $('input[name=email_ckbox]:checked').map(function () {
			return this.value;
		}).get();
		if (checkedValues == '') {
			$("#email_warning_msg").fadeIn("slow").removeClass("hide").addClass("show");
			return false;
		}
		else {
			$("#btn_compose_email").prop("href", "mailto:" + checkedValues);
		}
	});

	// Listener: Copy selected email addresses as text
	$("#btn_copy_as_text").click(function () {
		var checkedValues = $('input[name=email_ckbox]:checked').map(function () {
			return this.value;
		}).get();
		if (checkedValues == '') {
			$("#email_warning_msg").fadeIn("slow").removeClass("hide").addClass("show");
			return false;
		}
		else {
			$("#modalTextarea").text(checkedValues);
			// Listener: Enable contents to be re-selected with a single click
			$("#modalTextarea").click(function () {
				$(this).focus().select();
			});
		}
	});

	// Listener: Focus and Select dynamic content of modal textarea
	$('#modalShowText').on('shown.bs.modal', function () {
		$('#modalTextarea').focus().select();
	})


	// Listener: Ajax call - request filtered data subset from server
	$("#btn_add_students,#btn_remove_students,#btn_add_tas,#btn_remove_tas,#btn_add_teachers,#btn_remove_teachers").click(function (e) {
		// TODO: PUT VALIDATION CODE HERE

		// assign attributes from target that was clicked
		// var event = $(this).attr("id");			// example: btn_add_teachers
		var role = $(this).attr("data-role");		// example: teachers
		var action = $(this).attr("data-action");	// example: add
		var course = $(this).attr("data-course");	// example: 123456
		// alert(role + ", " + action + ", " + course);

		// validation: prevent unnecessary and expensive API calls
		if (action == 'add') {
			// all recipients already selected
			if ($("INPUT[name=email_ckbox]:checked").length == $("INPUT[name=email_ckbox]").length) {
				// console.log($("INPUT[name=email_ckbox]:checked").length + ", " + $("INPUT[name=email_ckbox]").length); // debugging
				$("#checkboxes_stop_add").fadeIn(1800).removeClass("hide").fadeOut(1800, function () {
					$(this).addClass("hide");
				});
				return false;
			}
		}
		else if (action == 'remove') {
			// no recipient is selected yet
			if ($("INPUT[name=email_ckbox]:checked").length == 0) {
				$("#checkboxes_stop_remove").fadeIn(1800).removeClass("hide").fadeOut(1800, function () {
					$(this).addClass("hide");
				});
				return false;
			}
		}

		// start: ajax status indicator (UI)
		$("#filter_progress_indicator").addClass("wms_ajax_status_cursor");
		$("#filter_progress_indicator A").addClass("disabled");//.addClass("wms_ajax_status_btn");
		//$("#filter_progress_indicator A").addClass("disabled");//.addClass("wms_ajax_status");

		$.ajax({
			url: 'ajax-get-data.php',
			type: 'GET',
			data: {
				ajaxVal_Role: role,
				ajaxVal_Action: action,
				ajaxVal_Course: course
			},
			dataType: 'json',
			success: function (data) {
				// alert if update failed
				if (data) {
					// update the element with new data from the ajax call
					// alert('successful: ' + data); // debugging
					for (var i = 0; i < data.length; i++) {
						// look at each json object //console.log(obj);
						var obj = data[i];
						for (var key in obj) {
							// capture value of specific object key
							if (key == "id") { // 'id': is the canvas_user_id
								//  console.log(key + " -> " + obj[key]); // output desired key of this json object
								if (action == "add") {
									$("#" + obj[key]).prop("checked", true);
								}
								else if (action == "remove") {
									$("#" + obj[key]).prop("checked", false);
								}
							}
						}
					}
					// end: ajax status indicator (UI)
//					$("#filter_progress_indicator A").removeClass("disabled");
					$("#filter_progress_indicator").removeClass("wms_ajax_status_cursor");
					$("#filter_progress_indicator A").removeClass("disabled");//.removeClass("wms_ajax_status_btn");

					updateCkBoxCounter();
				}
				else {
					// end: ajax status indicator (UI)
					$("#filter_progress_indicator A").removeClass("disabled");

					// graceful error message
					$("#ajax_empty").fadeIn(1800).removeClass("hide").fadeOut(1800, function () {
						$(this).addClass("hide");
					});
					return false;
				}
			},
			error: function (data) {
				// end: ajax status indicator (UI)
				$("#filter_progress_indicator A").removeClass("disabled");

				// graceful error message

// DKC TODO
				//$("#ajax_failure").fadeIn("fast").removeClass("hide").fadeOut(1800, function () {
				$("#ajax_failure").fadeIn(1800).removeClass("hide").fadeOut(1800, function () {
					$(this).addClass("hide");
				});
				return false;
			}
		});
	});


	// Save - Utility function to quickly check X checkboxes for load testing with Gmail client
	/*
	 // create array of all checkboxes
	 var ids = new Array();
	 num_i = 0;
	 $("input:checkbox[name=email_ckbox]").each(function()
	 {
	 // add $(this).val() to your array
	 ids[num_i] = $(this).val();
	 num_i++;
	 });

	 // HTML: <a href="#" id="DKC">DKC TEST: check X boxes</a><br />
	 $("a#DKC").click(function () {
	 for (i = 0; i < 262; i++) {
	 $("INPUT#" + ids[i]).prop("checked", "checked");
	 }
	 updateCkBoxCounter();
	 });
	 */

});

