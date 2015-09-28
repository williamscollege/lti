<?php
	/***********************************************
	 ** Project: "Course Email" (LTI application)
	 ** Purpose: Easily email course participants using your preferred email client (i.e Gmail, Thunderbird, Outlook, Mac Mail, etc.)
	 ** Author: David Keiser-Clark, Williams College
	 ** Current features:
	 **  - Global selector: select/deselect all course participants
	 **  - Roles: select/deselect everyone, students, TA's, or teachers
	 **  - Sections: select/deselect everyone within a section (this displays only when > 1 section exists)
	 **  - Add or remove the selectors or manually click checkboxes to get desired list
	 **  - Compose Email: send all selected addresses as recipients to your default email client
	 **  - Copy as Text: manually copy all selected addresses as comma-separated text list
	 **  - Validation: User friendly and helpful validation messages (jQuery)
	 **  - Bells and Whistles: dynamic counts for recipients selected and static totals of everyone, roles, sections
	 **  - More Bells: AJAX loader enables fast LTI load followed by spinner and "fetching data" message; action buttons enabled only after ajax completes its data fetch
	 **  - PHP Curl command fetches participants of current Canvas course (utilizes their API)
	 **  - Bootstrap framework standardizes responsive CSS on all our LTI apps
	 **  - Instructions for configuring Chrome GMail as default email client
	 **  - Application modified as per results of local stress testing; see comments in code
	 **  - Fixed efficiency issues and maximum limits with courses used for large placement exams
	 **  - Fixed: exclude Canvas' undocumented and mostly-hidden hack that silently includes a "Test Student, type=StudentViewEnrollment" in every section to enable the standard "StudentView" for course participants
	 **  - This codebase utilizes (and slightly forks) Stephen P Vickers sample LTI "Rating" project (http://www.spvsoftwareproducts.com/php/rating/). Thank you Stephen.
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher), MySQL 5x, phpMyAdmin, emacs
	 **  - Enable PHP modules: PDO, curl, mbyte, dom
	 ** Future advanced features:
	 **  - add group(s) as filter
	 ***********************************************/


	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/lti_lib.php');
	require_once(dirname(__FILE__) . '/util.php');

	// Initialise database
	$db = NULL;
	init($db);

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo LTI_APP_NAME; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo LTI_APP_NAME; ?>">
	<meta name="author" content="<?php echo LANG_AUTHOR_NAME; ?>">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/coursemail.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<script src="<?php echo PATH_BOOTSTRAP_JS; ?>"></script>
	<!-- local JS -->
	<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
</head>
<body>
<form name="frmCourseEmail" id="frmCourseEmail" role="form">
	<input type="hidden" id="courseTitle" value="">
	<!-- total col-md per row should = 12 -->
	<div class="container">
		<div class="row">
			<div class="col-sm-12 small">&nbsp;</div>
		</div>
		<div class="row">
			<div class="col-sm-5">
				<div class="row">
					<div class="col-sm-6"><label class="text-primary">Select Recipient(s)</label></div>
					<div class="col-sm-6">
						<!-- display X recipients selected -->
						<div id="displayCkBoxCounter" class="text-right text-muted small">(<strong><span id="displayCkBoxInteger">0</span></strong>) recipients
							selected
						</div>
					</div>
				</div>
				<div class="wms-ckbox-container">
					<!-- temporary status while ajax is loading data -->
					<div id="wrapperFetchingData" class="text-center">
						<br /><br /><br /><br /><br />
						<span id="messageFetchingData"><img src="images/spinner.gif" alt="Fetching data..." title="Fetching data..." />Fetching data...</span>
					</div>
					<!-- placeholder table for ajax driven data; table utilized to enable "table-hover" UI feature -->
					<table id="tableEnrollments" class="table-hover small">
					</table>
				</div>
				<br />
			</div>
			<div class="col-sm-1">&nbsp;</div>
			<div class="col-sm-6">
				<div class="row">
					<div class="col-sm-12 small"><label class="text-primary">&nbsp;</label></div>
				</div>
				<div id="actions_for_dynamic_content">
					<div class="row form-group">
						<div class="col-sm-1">
							<a href="#" id="btn_add_all" class="btn btn-success btn-xs" title="Select all recipients">
								&nbsp;<i class="glyphicon glyphicon-plus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-1">
							<a href="#" id="btn_remove_all" class="btn btn-danger btn-xs" title="Deselect all recipients">
								&nbsp;<i class="glyphicon glyphicon-minus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-10 small"><strong>Everyone</strong><span id="numEveryone" class="text-muted small"></span></div>
					</div>
					<div class="row form-group">
						<div class="col-sm-1">
							<a href="#" id="btn_add_students" class="btn btn-success btn-xs" title="Select all students">
								&nbsp;<i class="glyphicon glyphicon-plus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-1">
							<a href="#" id="btn_remove_students" class="btn btn-danger btn-xs" title="Deselect all students">
								&nbsp;<i class="glyphicon glyphicon-minus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-10 small"><strong>Students</strong><span id="numStudents" class="text-muted small"></span></div>
					</div>
					<div class="row form-group">
						<div class="col-sm-1">
							<a href="#" id="btn_add_tas" class="btn btn-success btn-xs" title="Select all TA's">
								&nbsp;<i class="glyphicon glyphicon-plus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-1">
							<a href="#" id="btn_remove_tas" class="btn btn-danger btn-xs" title="Deselect all TA's">
								&nbsp;<i class="glyphicon glyphicon-minus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-10 small"><strong>TA's</strong><span id="numTas" class="text-muted small"></span></div>
					</div>
					<div class="row form-group">
						<div class="col-sm-1">
							<a href="#" id="btn_add_teachers" class="btn btn-success btn-xs" title="Select all teachers">
								&nbsp;<i class="glyphicon glyphicon-plus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-1">
							<a href="#" id="btn_remove_teachers" class="btn btn-danger btn-xs" title="Deselect all teachers">
								&nbsp;<i class="glyphicon glyphicon-minus"></i>&nbsp;</a>
						</div>
						<div class="col-sm-10 small"><strong>Teachers</strong><span id="numTeachers" class="text-muted small"></span></div>
					</div>

					<!-- dynamically created section buttons would go here (if > 1 sections exist) -->
					<div id="dynamicallyCreatedSectionButtons"></div>
				</div>

				<!-- validation: various hidden messages -->
				<div class="row form-group wms-validation-box">
					<div class="col-sm-8">
						<!-- too many selected recipients will exceed Gmail's ability to dynamically create an email; workaround: manually open (compose) an email, then paste the entire address list in 'TO' field -->
						<div id="displayCkBoxInstructions" class="hidden text-danger small">
							<p><strong>Browser limit: List too long for "Compose Email"</strong></p>
							How to proceed:<br />
							<ol type="1">
								<li>Click "Copy as Text" button</li>
								<li>Open Gmail (or email client)</li>
								<li>Create new email</li>
								<li>Paste text into email "To" field</li>
							</ol>
						</div>
						<div id="warning_nothing_selected" class="alert alert-warning hide small">
							<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
							<strong>Nothing selected!</strong><br />Please select one or more email addresses.
						</div>

						<div id="warning_zero_matches" class="alert alert-warning hide small">
							<span class="glyphicon glyphicon-flash"></span>&nbsp;
							No matches found.
						</div>
						<div id="warning_ajax_failure" class="alert alert-danger hide small">
							<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
							Ajax failed to load course participants.<br />Please call OIT ITech.
						</div>
					</div>
					<div class="col-sm-4">&nbsp;</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-6">
						<p>
							<a href="#" id="btn_compose_email" class="btn btn-primary form-control" title="Compose an email in your default email program" target="_blank">
								<i id="icon_compose_email" class="glyphicon glyphicon-envelope"></i>&nbsp;Compose Email
							</a>
						</p>
					</div>
					<div class="col-sm-6">&nbsp;</div>
				</div>

				<label class="text-primary small">Problem?</label>

				<p class="small text-muted">
					You must set your default email client<br />
					<a href="http://oit.williams.edu/files/2014/03/Set-Chrome-to-be-the-Default-Mail-Handler.pdf" title="Instructions: Set Chrome to be your default email client" target="_blank"><i class="glyphicon glyphicon-new-window"></i>&nbsp;Set
						Chrome as default email client</a><br />
					<a href="http://oit.williams.edu/itech/glow/how-can-i-email-my-class-using-course-email-tool/" title="Recipient Limit: Mac Mail/Outlook/Thunderbird: 99 (Chrome Gmail: 2,000)" target="_blank"><i class="glyphicon glyphicon-new-window"></i>&nbsp;Recipient
						Limit: Mac Mail/Outlook/Thunderbird: 99 (Gmail: 2,000)</a><br /><br />
				</p>

				<div class="row form-group">
					<div class="col-sm-6">
						<!-- trigger for modal dialogue -->
						<a href="#" id="btn_copy_as_text" class="btn btn-primary form-control" data-toggle="modal" data-target="#modalShowText" title="Alternate: Copy selected email addresses as text"><i class="glyphicon glyphicon-align-justify"></i>&nbsp;Copy
							as Text</a>
					</div>
					<div class="col-sm-6">&nbsp;</div>
				</div>

				<!-- modal dialogue: start -->
				<div class="modal fade" id="modalShowText" tabindex="-1" role="dialog" aria-labelledby="modalShowTextLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="modalShowTextLabel">Copy to clipboard (pc: Ctrl+c, mac: Command+c)</h4>
							</div>
							<div class="modal-body">
								<textarea id="modalTextarea" class="form-control" rows="15" style="cursor: pointer; font-size: small" autofocus="autofocus" readonly></textarea>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<!-- modal dialogue: end -->

			</div>
		</div>
	</div>
</form>
<?php
	// LOCAL TESTING ONLY! This should be remmed out for live site.
	// $_SESSION['custom_canvas_course_id'] = 123456;
?>
<!-- jQuery -->
<script>
	$(document).ready(function () {

		// set initial conditions: disable functionality of action buttons until after ajax has successfully completed
		$("#actions_for_dynamic_content a.btn, #btn_compose_email, #btn_copy_as_text").addClass("disabled");

		getEnrollments(); // note: call sections from within getEnrollments() to easily preserve access to dynamically created data in DOM

		// use AJAX to get enrollments for this course via API call; append to DOM
		function getEnrollments() {
			$.ajax({
				url: 'lti_ajax_get_enrollments.php',
				type: 'GET',
				data: {
					ajaxVal_Course: <?php if (isset($_SESSION['custom_canvas_course_id'])){echo $_SESSION['custom_canvas_course_id'];}else{echo "0";} ?>
				},
				dataType: 'json',
				success: function (data) {
					if (data) {

						// start: building table.html() to create checkboxes from returned json array
						var populateCheckboxList = '<tbody>';

						for (var key in data) {
							// build checkbox list (exclude Canvas' undocumented and mostly-hidden hack that silently includes a "Test Student, type=StudentViewEnrollment" in every section to enable the standard "StudentView" for course participants)
							if (data[key]["type"] != "StudentViewEnrollment") {
								populateCheckboxList += '<tr><td><label for="' + data[key]["user_id"] + '"><input type="checkbox" name="email_ckbox" id="' + data[key]["user_id"] + '" value="' + data[key]["email"] + '" data-role="' + data[key]["role"] + '" data-section-ids="' + data[key]["section_id"] + '" />&nbsp;' + data[key]["full_name"] + '<span class="text-muted small"> (' + data[key]["email"] + ')</span></label></td></tr>';
							}
						}

						populateCheckboxList += "</tbody>";
						// end: building table.html()

						// insert table contents into DOM
						$("#tableEnrollments").html(populateCheckboxList);

						// now get sections data
						getSections();

						/* Debugging
						 var data_str = "data is:\n";
						 for (var f in data) {
						 data_str += f + "\n";
						 data_str += f + " : " + data[f]["user_id"] + "\n";
						 }
						 data_str += "responseText : " + data['responseText'] + "\n";
						 data_str += "status : " + data['status'] + "\n";
						 data_str += "statusText : " + data['statusText'] + "\n";
						 alert(data_str);
						 */
					}
					else {
						// graceful error message
						$("#warning_ajax_failure").fadeIn("fast").removeClass("hide");

						// change initial "fetching data" message
						$("#messageFetchingData").text("Oops, please call OIT ITech.");

						return false;
					}
				},
				error: function (data) {
					// graceful error message
					$("#warning_ajax_failure").fadeIn("fast").removeClass("hide");

					// change initial "fetching data" message
					$("#messageFetchingData").text("Oops... please call OIT ITech.");

					return false;
				}
			});
		}

		// use AJAX to get sections for this course via API call; append to DOM
		function getSections() {
			$.ajax({
				url: 'lti_ajax_get_sections.php',
				type: 'GET',
				data: {
					ajaxVal_Course: <?php if (isset($_SESSION['custom_canvas_course_id'])){echo $_SESSION['custom_canvas_course_id'];}else{echo "0";} ?>
				},
				dataType: 'json',
				success: function (data) {
					if (data) {

						// array to hold distinct course sections
						var arrSections = [];

						function addDistinctSections(id, name) {
							var found = arrSections.some(function (el) {
								return el.id === id;
							});
							if (!found) {
								arrSections.push({id: id, name: name});
							}
						}

						for (var key in data) {
							// build array of sections
							addDistinctSections(data[key]["id"], data[key]["name"]);
						}

						// sort sections, get smallest section_id value to set hidden "courseTitle" for subsequent email subject line
						arrSections.sort(function(a, b) {
							var valueA, valueB;

							valueA = a['id']; // sort by this index
							valueB = b['id'];
							if (valueA < valueB) {
								return -1;
							}
							else if (valueA > valueB) {
								return 1;
							}
							return 0;
						});
						// console.dir(arrSections);
						$("#courseTitle").val(arrSections[0]["name"]);

						// dynamically create action buttons to enable selecting/deselecting each section (if > 1 sections exist)
						if (arrSections.length > 1) {
							var sectionButtons = "";
							for (var key in arrSections) {
								// get count of number of enrollments per each section
								var sect_cnt = 0;

								$("INPUT[data-section-ids]").each(function (index) {
									var sect_ids = $(this).attr('data-section-ids')
									// console.log( this.id + ": belongs to sections: " + sect_ids );
									if (sect_ids.indexOf(arrSections[key]["id"]) != -1) {
										sect_cnt += 1;
									}
								});

								sectionButtons += '<div class="row form-group">' + "\n" +
								'<div class="col-sm-1">' + "\n" +
								'<a href="#" data-action-type="btn_add_section" data-btn-section-id="' + arrSections[key]["id"] + '" class="btn btn-success btn-xs" title="Select all from this section">' + "\n" +
								'&nbsp;<i class="glyphicon glyphicon-plus"></i>&nbsp;</a>' + "\n" +
								'</div>' + "\n" +
								'<div class="col-sm-1">' + "\n" +
								'<a href="#" data-action-type="btn_remove_section" data-btn-section-id="' + arrSections[key]["id"] + '" class="btn btn-danger btn-xs" title="Deselect all from this section">' + "\n" +
								'&nbsp;<i class="glyphicon glyphicon-minus"></i>&nbsp;</a>' + "\n" +
								'</div>' + "\n" +
								'<div class="col-sm-10 small"><strong>Section: ' + arrSections[key]["name"] + '&nbsp;</strong><span class="text-muted small">(' + sect_cnt + ')</span><span class="text-muted small">&nbsp;(ID #' + arrSections[key]["id"] + ')</span></div>' + "\n" +
								'</div>' + "\n";
							}
							$("#dynamicallyCreatedSectionButtons").html(sectionButtons);
						}

						// hide initial "fetching data" message
						$("#wrapperFetchingData").addClass("hide");

						// enable functionality of buttons
						$("#actions_for_dynamic_content a.btn, #btn_compose_email, #btn_copy_as_text").removeClass("disabled");

						// provide static count of each category
						$("#numEveryone").text(" (" + $("INPUT[name='email_ckbox']").length + ")");
						$("#numStudents").text(" (" + $("INPUT[data-role=StudentEnrollment]").length + ")");
						$("#numTas").text(" (" + $("INPUT[data-role=TaEnrollment]").length + ")");
						$("#numTeachers").text(" (" + $("INPUT[data-role=TeacherEnrollment]").length + ")");

						/* Debugging
						 var data_str = "data is:\n";
						 for (var f in data) {
						 data_str += f + " : " + data[f]["id"] + "\n";
						 }
						 data_str += "responseText : " + data['responseText'] + "\n";
						 data_str += "status : " + data['status'] + "\n";
						 data_str += "statusText : " + data['statusText'] + "\n";
						 console.log(data_str);
						 */
					}
					else {
						// graceful error message
						$("#warning_ajax_failure").fadeIn("fast").removeClass("hide");

						// change initial "fetching data" message
						$("#messageFetchingData").text("Oops, please call OIT ITech.");

						return false;
					}
				},
				error: function (data) {
					// graceful error message
					$("#warning_ajax_failure").fadeIn("fast").removeClass("hide");

					// change initial "fetching data" message
					$("#messageFetchingData").text("Oops... please call OIT ITech.");

					return false;
				}
			});
		}
	});
</script>

</body>
</html>