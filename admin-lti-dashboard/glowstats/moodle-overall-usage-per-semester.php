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
	# SQL: Fetch summary of all courses grouped by year (for dropdown menu)
	#------------------------------------------------#
	$queryListOfSemesters = "
		SELECT
			SUBSTRING(idnumber, 1, 3) AS semesteridentifier,
			SUBSTRING(idnumber, 1, 2) AS orderingyear,
			CASE SUBSTRING(idnumber, 3, 1)
				WHEN 'F' THEN 1
				WHEN 'W' THEN 2
				WHEN 'S' THEN 3
				ELSE 4
			END AS orderingsem
		FROM
			mdl_course
		WHERE
			idnumber REGEXP '^[0-9]{2}[a-z]{1}-'
		GROUP BY semesteridentifier , orderingyear , orderingsem
		ORDER BY orderingyear DESC , orderingsem DESC
	";
	$resultsListOfSemesters = mysqli_query($moodle_connString, $queryListOfSemesters) or
	die(mysqli_error($moodle_connString));


	#------------------------------------------------#
	# SQL: Moodle: Courses per Semester
	#------------------------------------------------#

	# Iterate through ListOfSemesters; for each, fetch SQL results, append results to associate array hash. output hash into table at bottom.
	$coursesAllSemesters = [];

	while ($rowLOS = mysqli_fetch_array($resultsListOfSemesters)) {
		$dateRange = $rowLOS["semesteridentifier"];
		// echo $dateRange . "<br />";

		$queryCoursesPerSemester = "
		SELECT
			SUBSTRING(c.idnumber, 1, 3) AS semesterIdentifier
			,(Select COUNT(*) from mdl_course as crs where SUBSTRING(crs.idnumber, 1, 3) = semesterIdentifier AND crs.visible = 1) AS numVisibleCourses
			,(SELECT COUNT(DISTINCT u.id) FROM mdl_user u JOIN mdl_role_assignments ra ON ra.userid = u.id JOIN mdl_context con ON ra.contextid = con.id JOIN mdl_course crs ON crs.id = con.instanceid AND crs.visible = 1 JOIN mdl_role r ON ra.roleid = r.id WHERE con.contextlevel = 50 AND r.id IN (3) AND crs.idnumber like '$dateRange%') as teacherCount
			,(SELECT COUNT(DISTINCT u.id) FROM mdl_user u JOIN mdl_role_assignments ra ON ra.userid = u.id JOIN mdl_context con ON ra.contextid = con.id JOIN mdl_course crs ON crs.id = con.instanceid AND crs.visible = 1 JOIN mdl_role r ON ra.roleid = r.id WHERE con.contextlevel = 50 AND r.id IN (5) AND crs.idnumber like '$dateRange%') as studentCount
			,(SELECT COUNT(crs.id) FROM mdl_course_modules AS cm JOIN mdl_modules as m ON cm.module = m.id AND m.name = 'forum' JOIN mdl_course as crs ON cm.course = crs.id AND crs.visible = 1 AND crs.idnumber like '$dateRange%') AS activityForumCount
			,(SELECT COUNT(log.id) FROM  mdl_log AS log JOIN mdl_course as crs ON log.course = crs.id AND crs.visible = 1 AND crs.idnumber like '$dateRange%' WHERE log.module ='forum') AS forumHitCount
			,(SELECT COUNT(crs.id) FROM  mdl_course AS crs WHERE crs.visible = 1 AND crs.showgrades = 1 AND crs.idnumber like '$dateRange%') AS numGradedCourses
		FROM
			mdl_course as c
		WHERE
			c.idnumber REGEXP '^[0-9]{2}[A-Z]{1}-'
		AND SUBSTRING(c.idnumber, 1, 3) = '$dateRange' -- '$dateRange'
			GROUP BY semesterIdentifier
			ORDER BY c.idnumber DESC;
		";

		$resultsCoursesPerSemester = mysqli_query($moodle_connString, $queryCoursesPerSemester) or die(mysqli_error($moodle_connString));

		while ($row = mysqli_fetch_array($resultsCoursesPerSemester)) {
			$coursesAllSemesters[$row['semesterIdentifier']] = [
				'semesterIdentifier'   => $row['semesterIdentifier']
				, 'numVisibleCourses'  => $row['numVisibleCourses']
				, 'teacherCount'       => $row['teacherCount']
				, 'studentCount'       => $row['studentCount']
				, 'activityForumCount' => $row['activityForumCount']
				, 'forumHitCount'      => $row['forumHitCount']
				, 'numGradedCourses'   => $row['numGradedCourses']
			];
		}
	}
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
			<h3>Moodle: Overall Usage per Semester</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<div class="well well-sm small">
				<strong>KEY:</strong>
				Semester,
				Active Courses = Count of all course.visible=1,
				Instructors = Count of all distinct teachers where course.visible=1),
				Students Enrolled = students enrolled at least in one course that has used Glow,
				Forums = Number of forums where course.visible=1,
				Forum Postings = Number of postings in all forums where course.visible=1,
				Gradebooks Enabled = Number of courses that have enabled gradebooks where course.visible=1,
				<em>(not implemented: Gradebooks Items = Number of items in all gradebooks where course.visible=1)</em>
			</div>

			<table class="tablesorter small">
				<caption>
					<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryCoursesPerSemester; ?>">Show SQL
						query</a>
				</caption>
				<thead>
				<tr>
					<th>Semester</th>
					<th>Active Courses</th>
					<th>Instructors</th>
					<th>Students Enrolled</th>
					<th>Forums</th>
					<th>Forum Postings</th>
					<th>Gradebooks Enabled</th>
					<th>Gradebooks Items</th>
				</tr>
				</thead>
				<tbody>
				<?php
					foreach ($coursesAllSemesters as $row) {
						echo "<tr>";
						echo "<td>" . $row["semesterIdentifier"] . "</td>";
						echo "<td>" . number_format($row["numVisibleCourses"]) . "</td>";
						echo "<td>" . number_format($row["teacherCount"]) . "</td>";
						echo "<td>" . number_format($row["studentCount"]) . "</td>";
						echo "<td>" . number_format($row["activityForumCount"]) . "</td>";
						echo "<td>" . number_format($row["forumHitCount"]) . "</td>";
						echo "<td>" . number_format($row["numGradedCourses"]) . "</td>";
						echo "<td>n/a</td>";
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
