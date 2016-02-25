/***********************************************
 ** LTI Application: "Course Email"
 ** Purpose: Easily email course participants using your preferred email client (i.e Gmail, Thunderbird, Outlook, Mac Mail, etc.)
 ** Author: David Keiser-Clark, Williams College
 ***********************************************/

$(document).ready(function () {

	// ------------------------------------------------
	// Listeners: Buttons to select/deselect checkboxes
	// ------------------------------------------------

	// Roles: Select checkboxes
	$("#btn_add_all").click(function () {
		$("INPUT[name=email_ckbox]").prop("checked", true);
	});
	$("#btn_add_student_enrollments").click(function () {
		checkForZeroMatches("StudentEnrollment");
		$("INPUT[data-role=StudentEnrollment]").prop("checked", true);
	});
	$("#btn_add_ta_enrollments").click(function () {
		checkForZeroMatches("TaEnrollment");
		$("INPUT[data-role=TaEnrollment]").prop("checked", true);
	});
	$("#btn_add_grading_tas").click(function () {
		checkForZeroMatches("Grading-TA");
		$("INPUT[data-role=Grading-TA]").prop("checked", true);
	});
	$("#btn_add_grader_homeworks").click(function () {
		checkForZeroMatches("Grader-Homework");
		$("INPUT[data-role=Grader-Homework]").prop("checked", true);
	});
	$("#btn_add_teacher_enrollments").click(function () {
		checkForZeroMatches("TeacherEnrollment");
		$("INPUT[data-role=TeacherEnrollment]").prop("checked", true);
	});
	$("#btn_add_designers").click(function () {
		checkForZeroMatches("Designer");
		$("INPUT[data-role=Designer]").prop("checked", true);
	});
	$("#btn_add_observer_enrollments").click(function () {
		checkForZeroMatches("ObserverEnrollment");
		$("INPUT[data-role=ObserverEnrollment]").prop("checked", true);
	});

	// Roles: Deselect checkboxes
	$("#btn_remove_all").click(function () {
		$("INPUT[name=email_ckbox]").prop("checked", false);
	});
	$("#btn_remove_student_enrollments").click(function () {
		checkForZeroMatches("StudentEnrollment");
		$("INPUT[data-role=StudentEnrollment]").prop("checked", false);
	});
	$("#btn_remove_ta_enrollments").click(function () {
		checkForZeroMatches("TaEnrollment");
		$("INPUT[data-role=TaEnrollment]").prop("checked", false);
	});
	$("#btn_remove_grading_tas").click(function () {
		checkForZeroMatches("Grading-TA");
		$("INPUT[data-role=Grading-TA]").prop("checked", false);
	});
	$("#btn_remove_grader_homeworks").click(function () {
		checkForZeroMatches("Grader-Homework");
		$("INPUT[data-role=Grader-Homework]").prop("checked", false);
	});
	$("#btn_remove_teacher_enrollments").click(function () {
		checkForZeroMatches("TeacherEnrollment");
		$("INPUT[data-role=TeacherEnrollment]").prop("checked", false);
	});
	$("#btn_remove_designers").click(function () {
		checkForZeroMatches("Designer");
		$("INPUT[data-role=Designer]").prop("checked", false);
	});
	$("#btn_remove_observer_enrollments").click(function () {
		checkForZeroMatches("ObserverEnrollment");
		$("INPUT[data-role=ObserverEnrollment]").prop("checked", false);
	});

	// Sections: Add listeners for dynamically added action buttons to select/deselect sections (note: section buttons were dynamically added to the DOM via ajax)
	$(document).on('click', 'a[data-action-type=btn_add_section],a[data-action-type=btn_remove_section]', function () {
			var sectionID = $(this).attr("data-btn-section-id");
			if ($(this).attr("data-action-type") == "btn_add_section") {
				// select checkboxes that correspond to this dynamically created section button
				$("INPUT[data-section-ids]").each(function (index) {
					var sect_ids = $(this).attr('data-section-ids')
					// console.log( this.id + ": belongs to sections: " + sect_ids );
					if (sect_ids.indexOf(sectionID) != -1) {
						// console.log( this.id + ": belongs to THIS section: " + sectionID );
						$(this).prop("checked", true);
					}
				});
			}
			else if ($(this).attr("data-action-type") == "btn_remove_section") {
				// deselect checkboxes that correspond to this dynamically created section button
				$("INPUT[data-section-ids]").each(function (index) {
					var sect_ids = $(this).attr('data-section-ids')
					if (sect_ids.indexOf(sectionID) != -1) {
						$(this).prop("checked", false);
					}
				});
			}
			// update counter for clicked checkboxes
			updateCkBoxCounter();
		}
	);


	function checkForZeroMatches(role) {
		if ($("INPUT[data-role=" + role + "]").length == 0) {
			$("#warning_zero_matches").fadeIn("fast").removeClass("hide").fadeOut(1800, function () {
				$(this).addClass("hide");
			});
			return false;
		}
	}


	// ------------------------------------------------
	// Listeners: Counters
	// ------------------------------------------------

	// Update counter for href button clicks
	$("a.btn").click(function () {
		updateCkBoxCounter();
	});
	// Update counter for clicked checkboxes (note: these were dynamically added to the DOM via ajax)
	$(document).on('change', '[name=email_ckbox]', function () {
		updateCkBoxCounter();
	});

	/*
	 STRESS-TESTING CAPACITY:
	 - My testing shows Gmail (Windows7, Chrome browser version 36.0.1985.143 m) successfully can create a dynamic email with the following capacities:
	 - maximum of 261 short email addresses (<=21 characters each, totalling 6,582 characters)
	 - maximum of 171 short email addresses AND full name descriptive text (totalling 6,749 characters)
	 - maximum of 115 short email addresses via FireFox (Glow) browser to Chrome email client (totalling 2,191 characters)
	 - Google states: The limit for GAE Chrome Gmail is 2000 Williams email addresses (internal) or 500 non-Williams email address (external)
	 - Google states: GAE Google Mail limits other SMTP mail clients (i.e. MacMail, Outlook, Thunderbird) to just 99 recipient email addresses
	 */
	// Count selected checkboxes. Display counter and warning messages
	function updateCkBoxCounter() {
		var numCheckedBoxes = $("INPUT[name=email_ckbox]:checked").length;
		$("#displayCkBoxInteger").text(numCheckedBoxes);
		if (numCheckedBoxes > 99) { // 99 is maximum safe Google stated limit for Chrome browser (see "stress-testing capacity" notes above)
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

		// Remove error message upon selection of any checkbox
		if (numCheckedBoxes > 0) {
			$("#warning_nothing_selected").removeClass("show").addClass("hide");
		}
	}


	// ------------------------------------------------
	// Listeners: Compose email in mail client
	// ------------------------------------------------

	$("#btn_compose_email").click(function () {
		var checkedValues = $('input[name=email_ckbox]:checked').map(function () {
			return this.value;
		}).get();
		if (checkedValues == '') {
			$("#warning_nothing_selected").fadeIn("slow").removeClass("hide").addClass("show");
			return false;
		}
		else {
			var crsTitle = "[Glow] " + $("#courseTitle").val();
			crsTitle.replace(/\s+/g, '%20');
			$("#btn_compose_email").prop("href", "mailto:" + checkedValues + "?subject=" + crsTitle);
		}
	});


	// ------------------------------------------------
	// Listeners: Copy selected email addresses as text
	// ------------------------------------------------

	$("#btn_copy_as_text").click(function () {
		var checkedValues = $('input[name=email_ckbox]:checked').map(function () {
			return this.value;
		}).get();
		if (checkedValues == '') {
			$("#warning_nothing_selected").fadeIn("slow").removeClass("hide").addClass("show");
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


	// ------------------------------------------------
	// Listeners: Show modal with content of textarea highlighted
	// ------------------------------------------------

	$('#modalShowText').on('shown.bs.modal', function () {
		$('#modalTextarea').focus().select();
	})


	// ------------------------------------------------
	// Save - A utility function to quickly check X checkboxes for load testing with Gmail client
	// ------------------------------------------------

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
