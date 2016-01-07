<?php
	/***********************************************
	 ** Project:    Glowstats
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:
	 ** - View all available Glow LMS statistics and analytics
	 ** - Versions: Canvas 2013-present, Moodle 2010-2013, Blackboard 2003-2010
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, dom
	 ***********************************************/

	require_once(dirname(__FILE__) . '/../institution.cfg.php');
	require_once(dirname(__FILE__) . '/../include/connDB.php');
	require_once(dirname(__FILE__) . '/../util.php');


	#------------------------------------------------#
	# Validation Routines
	#------------------------------------------------#
	# takes: a string
	# returns: a cleaned version of that string, in theory safe for use in queries
	function quote_smart($value) {
		// Stripslashes
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		// Quote if not integer
		if (!is_numeric($value) || $value[0] == '0') {
			$value = "'" . trim(mysqli_real_escape_string($GLOBALS["moodle_connString"], $value)) . "'";
		}
		return $value;
	}


	#------------------------------------------------#
	# Forms Collections: Fetch AJAX values
	#------------------------------------------------#
	$intNGC = (isset($_POST["inputNGC"])) ? quote_smart(intval($_POST["inputNGC"])) : 10;
	$intSUG = (isset($_POST["inputSUG"])) ? quote_smart(intval($_POST["inputSUG"])) : 10;


	#------------------------------------------------#
	# SQL: Moodle: Courses per Semester
	// Semester
	// Active Courses (visible=1)
	// Inactive Courses (visible=0)
	// All Courses (visible=1 or visible=0)
	// % Active (Active Courses / All Courses)
	#------------------------------------------------#
	$queryCoursesPerSemester = "
SELECT
	SUBSTRING(c.idnumber, 1, 3) AS semesterIdentifier
	,(Select COUNT(*) from mdl_course where SUBSTRING(mdl_course.idnumber, 1, 3) = semesterIdentifier AND mdl_course.visible = 1) AS numActiveCourses
	,(Select COUNT(*) from mdl_course where SUBSTRING(mdl_course.idnumber, 1, 3) = semesterIdentifier AND mdl_course.visible = 0) AS numInactiveCourses
	,(Select COUNT(*) from mdl_course where SUBSTRING(mdl_course.idnumber, 1, 3) = semesterIdentifier) AS numAllCourses
	,COUNT(*) AS numTotalCourses
FROM
	mdl_course as c
WHERE
	c.idnumber REGEXP '^[0-9]{2}[A-Z]{1}-'
GROUP BY semesterIdentifier
ORDER BY c.idnumber DESC;
	";
	$resultsCoursesPerSemester = mysqli_query($moodle_connString, $queryCoursesPerSemester) or
	die(mysqli_error($moodle_connString));

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo LTI_APP_NAME; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo LTI_APP_NAME; ?>">
	<meta name="author" content="<?php echo LANG_AUTHOR_NAME; ?>">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo PATH_JQUERYUI_CSS; ?>" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/css/theme.blue.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/jquery.tablesorter.pager.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/wms-mottie-tablesorter-patch.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/wms-custom.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERYUI_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<script src="<?php echo PATH_BOOTSTRAP_JS; ?>"></script>
	<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/js/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/js/jquery.tablesorter.widgets.js"></script>
	<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/jquery.tablesorter.pager.js"></script>
	<!-- local JS -->
	<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
</head>
<body>
<script>
	$(document).ready(function () {
		// Initialize motte-tablesorter plugin
		$(".tablesorter")
			.tablesorter({
				// debug:true, // disable for live use
				theme: 'blue',
				sortList: [
					[0, 1]
				],
				widthFixed: true,
				sortReset: true, // allow you to click on the table header a third time to reset the sort direction
				// sortRestart: true, // start the sort with the sortInitialOrder when clicking on a previously unsorted column
				sortInitialOrder: 'desc', // starting sort direction "asc" or "desc"
				// initialize a bunch of widgets
				widgets: ['zebra', 'columns'],
				widgetOptions: {
					zebra: ['odd', 'even'],
					columns: ['primary', 'secondary', 'tertiary']
				}
			});

		// ***************************
		// BELOW: NON-TABLESORTER STUFF
		// ***************************
		// Prevent normal submission of form
		$('FORM').submit(function (event) {
			return false;
		});

		// validate and activate AJAX form submit
		$("INPUT").change(function () {

			// TO DO: PUT VALIDATION CODE HERE
			// ...
			// remove any non-numbers

			// show spinner
			$("#spinner_1").removeClass("hidden");

			// store reference to this form
			var $this = $(this);

			// get url from the form element
			var url = $(this).parents('form').attr('action');

			// get name from the form element
			var formName = $(this).parents('form').attr('name');
			//alert(formName);

			// get data from the form element
			var dataToSend = $('#' + formName + ' INPUT').val();
			//alert(dataToSend);

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					ajaxForm: formName,
					ajaxVal: dataToSend
				},
				dataType: 'html',
				success: function (data) {
					// alert if update failed
					if (data) {
						//alert(data);
						$('#' + formName + ' TABLE').html(data);
						$('#' + formName + ' TABLE.tablesorter').tablesorter({
							widgets: ['zebra']
						});
						$('.notice').css('display', 'none');
						$('#' + formName + ' .notice').addClass('text-success').text('Results updated.').fadeOut().fadeIn();
						$("#spinner_1").addClass("hidden");
					}
					else {
						//Load output into a DIV
						$('.notice').css('display', 'none');
						$('#' + formName + ' .notice').addClass('text-failure').text('Requested action failed.').fadeOut().fadeIn();
					}
				}
			});
		});
	});
</script>

<div id="dialog-alert">
	<div id="dialog-message" class="small"></div>
</div>

<div class="container">
	<div class="row">
		<div class="page-header">
			<h1><?php echo LTI_APP_NAME; ?></h1>
			<h5><?php echo LANG_INSTITUTION_NAME . ": Moodle Statistics"; ?></h5>

			<div id="breadCrumbs" class="small"><?php require_once(dirname(__FILE__) . '/../include/breadcrumbs.php'); ?></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<?php require_once(dirname(__FILE__) . '/../include/subnav-statistics.php'); ?>
			<h3>Moodle: Courses per Semester</h3>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12 col-sm-12">
			<div class="well well-sm small">
				<strong>KEY:</strong>
				Semester,
				Active Courses (visible=1),
				Inactive Courses (visible=0),
				All Courses (visible=1 or visible=0),
				% Active (Active Courses / All Courses)
			</div>


			<table class="tablesorter small">
				<caption>
					<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryCoursesPerSemester; ?>">Show SQL
						query</a><br />
					Moodle: Courses per Semester (similar class sections are not combined)
				</caption>
				<thead>
				<tr>
					<th>Semester</th>
					<th># Active Courses</th>
					<th># Inactive Courses</th>
					<th># All Courses</th>
					<th>% Active (Active/All)</th>
				</tr>
				</thead>
				<tbody>
				<?php
					while ($rowCPS = mysqli_fetch_array($resultsCoursesPerSemester)) {

						echo "<tr>";
						echo "<td>" . $rowCPS["semesterIdentifier"] . "</td>";
						echo "<td>" . $rowCPS["numActiveCourses"] . "</td>";
						echo "<td>" . $rowCPS["numInactiveCourses"] . "</td>";
						echo "<td>" . $rowCPS["numAllCourses"] . "</td>";
						echo "<td>" . round($rowCPS["numActiveCourses"] / $rowCPS["numAllCourses"] * 100) . "%</td>";
						echo "</tr>";
					}
				?>
				</tbody>
			</table>
		</div>
	</div> <!-- /.row -->

	<?php require_once(dirname(__FILE__) . '/../include/foot.php'); ?>
</div> <!-- /.container -->
</body>
</html>
