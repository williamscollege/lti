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

	# takes: an array BY REFERENCE (i.e. sorce var is modified) and the name of the input parameter
	# does: cleans and stores te contents of the input data in _REQUEST into the array
	# NOTE: special input value ANY returns sets the array to empty
	function processArrayInput(&$toArray, $webParamName) {
		if (isset($_REQUEST[$webParamName])) {
			$toArray = array();
			//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
			if (is_array($_REQUEST[$webParamName])) {
				foreach ($_REQUEST[$webParamName] as $webVal) {
					$toArray[] = quote_smart($webVal);
				}
			}
			else {
				$toArray[] = quote_smart($_REQUEST[$webParamName]);
			}
		}
	}

	# takes: an SQL query string, the name of a column to check, and an array containing 0 or more items that the column may have (0 means no condition is added)
	# does: adds conditions to the string based on the contents of the given array
	function addArrayConditionsToSQLQuery(&$sql, $colName, $fromArray) {
		if (!$fromArray) {
			return;
		}
		$sql .= " AND ( 1=0 ";
		foreach ($fromArray as $value) {
			$sql .= "\n  OR $colName = $value";
		}
		$sql .= ")";
	}

	$semesterFilter = Array();
	processArrayInput($semesterFilter, 'filterRange');


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


	# SQL: Fetch all courses per desired semester
	$queryCourses = "
SELECT
	u.firstname
	,u.lastname
	,(SELECT COUNT(raUser.userid) FROM mdl_context AS ctxUser JOIN mdl_role_assignments AS raUser ON raUser.contextid = ctxUser.id AND raUser.roleid IN (3) WHERE ctxUser.instanceid = c.id AND ctxUser.contextlevel = 50) AS teachercount
	,(SELECT COUNT(raUser.userid) FROM mdl_context AS ctxUser JOIN mdl_role_assignments AS raUser ON raUser.contextid = ctxUser.id AND raUser.roleid IN (5) WHERE ctxUser.instanceid = c.id AND ctxUser.contextlevel = 50) AS studentcount
	,c.id AS courseid
	,c.idnumber
	,count(l.id) AS EventLogActivity
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='course' AND log.course=c.id) AS coursehitcount
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='resource' AND log.course=c.id) AS resourcehitcount
	,(SELECT COUNT(resource.id) FROM mdl_resource AS resource WHERE resource.course = c.id) AS resourcecount
	,(SELECT COUNT(resource.id) FROM mdl_resource AS resource WHERE resource.course = c.id AND type = 'file') AS resourcefilecount
	,(SELECT COUNT(resource.id) FROM mdl_resource AS resource WHERE resource.course = c.id AND type = 'html') AS resourcehtmlcount
	,(SELECT COUNT(resource.id) FROM mdl_resource AS resource WHERE resource.course = c.id AND type = 'directory') AS resourcedirectorycount
	,(SELECT COUNT(resource.id) FROM mdl_resource AS resource WHERE resource.course = c.id AND type = 'text') AS resourcetextcount
	,(SELECT COUNT(CM.id) FROM mdl_course_modules AS CM JOIN mdl_modules as M ON CM.module = M.id AND M.name = 'forum' WHERE CM.course = c.id) AS activityForumCount
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='forum' AND log.course=c.id) AS forumhitcount
	,(SELECT COUNT(asgn.id) FROM mdl_assignment AS asgn WHERE asgn.course = c.id) AS TotalAssignments
	,(SELECT COUNT(asgn.id) FROM mdl_assignment AS asgn WHERE asgn.course = c.id AND asgn.assignmenttype IN ('upload','uploadsingle')) AS TotalAssignmentsUpload
	,(SELECT COUNT(asgn.id) FROM mdl_assignment AS asgn WHERE asgn.course = c.id AND asgn.assignmenttype = 'online') AS TotalAssignmentsOnline
	,(SELECT COUNT(asgn.id) FROM mdl_assignment AS asgn WHERE asgn.course = c.id AND asgn.assignmenttype = 'offline') AS TotalAssignmentsOffline
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='assignment' AND log.course=c.id) AS assignmenthitcount
	,(SELECT COUNT(quiz.id) FROM mdl_quiz AS quiz WHERE quiz.course = c.id) AS totalquizzes
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='quiz' AND log.course=c.id) AS quizhitcount
	,(SELECT COUNT(jrnl.id) FROM mdl_journal AS jrnl WHERE jrnl.course = c.id) AS totalJournals
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='journal' AND log.course=c.id) AS journalhitcount
	,(SELECT COUNT(lightboxgallery.id) FROM mdl_lightboxgallery AS lightboxgallery WHERE lightboxgallery.course = c.id) AS totallightboxes
	,(SELECT COUNT(log.id) FROM  mdl_log AS log WHERE log.module ='lightboxgallery' AND log.course=c.id) AS lightboxhitcount
FROM
	mdl_log AS l
		JOIN
	mdl_course AS c ON l.course = c.id
		";

	addArrayConditionsToSQLQuery($queryCourses, 'SUBSTRING(c.idnumber, 1, 3)', $semesterFilter);

	//TEST: AND ( 1=0 OR SUBSTRING(c.idnumber, 1, 3) = '10S')
	$queryCourses .= "
		JOIN
	mdl_context AS ctx ON ctx.instanceid = l.course AND ctx.contextlevel = 50
		JOIN
	mdl_role_assignments AS ra ON ra.contextid = ctx.id
		JOIN
	mdl_user AS u ON u.id = ra.userid AND ra.roleid IN (3)
WHERE
	c.idnumber REGEXP '^[0-9]{2}[a-z]{1}-'
GROUP BY u.firstname , u.lastname , c.id , c.idnumber
ORDER BY EventLogActivity DESC
LIMIT 0 , 2000
		";

	$resultsCourses = mysqli_query($moodle_connString, $queryCourses) or
	die(mysqli_error($moodle_connString));

	// array values: (SQL fieldname, HTML label abbreviated, HTML label full text, make it bold [0|1])
	$courseOutputColumnStructure =
		array(
			array('firstname', 'First Name', 1, 'text')
		, array('lastname', 'Last Name', 1, 'text')
		, array('teachercount', 'Instructors', 1, 'digit')
		, array('studentcount', 'Students', 1, 'digit')
		, array('idnumber', 'Course ID', 1, 'text')
		, array('EventLogActivity', 'Total Event Log Hits', 1, 'digit')
		, array('coursehitcount', 'Event Log Course Hits', 0, 'digit')
		, array('resourcehitcount', 'Event Log Resource Hits', 0, 'digit')
		, array('resourcecount', 'Resources', 1, 'digit')
		, array('resourcefilecount', 'File Resources', 0, 'digit')
		, array('resourcehtmlcount', 'HTML Resources', 0, 'digit')
		, array('resourcedirectorycount', 'Directory Resources', 0, 'digit')
		, array('resourcetextcount', 'Text Resources', 0, 'digit')
		, array('activityForumCount', 'Forums', 1, 'digit')
		, array('forumhitcount', 'Forum Hits', 0, 'digit')
		, array('TotalAssignments', 'Assignments', 1, 'digit')
		, array('TotalAssignmentsUpload', 'Upload Assignments', 0, 'digit')
		, array('TotalAssignmentsOnline', 'Online Assignments', 0, 'digit')
		, array('TotalAssignmentsOffline', 'Offline Assignments', 0, 'digit')
		, array('assignmenthitcount', 'Assignment Hits', 1, 'digit')
		, array('totalquizzes', 'Quizzes', 1, 'digit')
		, array('quizhitcount', 'Quiz Hits', 0, 'digit')
		, array('totalJournals', 'Journals', 1, 'digit')
		, array('journalhitcount', 'Journal Hits', 0, 'digit')
		, array('totallightboxes', 'LightBoxes', 1, 'digit')
		, array('lightboxhitcount', 'LightBox Hits', 0, 'digit')
		);

	/*	Tablesorter Reference table
	 source: http://mottie.github.com/tablesorter/docs/index.html

	 sorter: false	disable sort for this column.
	 sorter: "text"	Sort alpha-numerically.
	 sorter: "digit"	Sort numerically.
	 sorter: "currency"	Sort by currency value (supports "£$€¤¥¢").
	 sorter: "ipAddress"	Sort by IP Address.
	 sorter: "url"	Sort by url.
	 sorter: "isoDate"	Sort by ISO date (YYYY-MM-DD or YYYY/MM/DD).
	 sorter: "percent"	Sort by percent.
	 sorter: "usLongDate"	Sort by date (U.S. Standard, e.g. Jan 18, 2001 9:12 AM).
	 sorter: "shortDate"	Sort by a shorten date (see "dateFormat").
	 sorter: "time"	Sort by time (23:59 or 12:59 pm).
	 sorter: "metadata"	Sort by the sorter value in the metadata - requires the metadata plugin.
	 */

	//	echo $queryCourses;
	//	exit;
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
		//  Pager options for motte-tablesorter
		var pagerOptions = {
			container: $(".pager"),
			output: '{startRow} to {endRow} ({totalRows})',
			updateArrows: true,
			page: 0,
			size: 25,
			fixedHeight: false,
			removeRows: false,
			cssNext: '.next', // next page arrow
			cssPrev: '.prev', // previous page arrow
			cssFirst: '.first', // go to first page arrow
			cssLast: '.last', // go to last page arrow
			cssPageDisplay: '.pagedisplay', // location of where the "output" is displayed
			cssPageSize: '.pagesize', // page size selector - select dropdown that sets the "size" option
			cssDisabled: 'disabled' // note there is no period "." in front of this class name
		};

		// Initialize motte-tablesorter plugin
		$("#myTableSorter01")
			.tablesorter({
				// debug:true, // disable for live use
				theme: 'blue',
				sortList: [
					[5, 1]
				],
				widthFixed: true,
				sortReset: true, // allow you to click on the table header a third time to reset the sort direction
				// sortRestart: true, // start the sort with the sortInitialOrder when clicking on a previously unsorted column
				sortInitialOrder: 'desc', // starting sort direction "asc" or "desc"
				initWidgets: false, // set to false to apply the widgets only after the (presumably large) table is rendered within the pager plugin
				// initialize a bunch of widgets
				widgets: ['zebra', 'columns'],
				widgetOptions: {
					zebra: ['odd', 'even'],
					columns: ['primary', 'secondary', 'tertiary']
				}
			})
			// Initialize the pager plugin
			.tablesorterPager(pagerOptions)
			// Timer: assign the sortStart event
			.bind("sortStart", function (e, t) {
				start = e.timeStamp;
				//$("#sortertimer").html('Sort started. ');
			})
			.bind("sortEnd", function (e, t) {
				$("#sortertimer").html('Sort required: ' + ( (e.timeStamp - start) / 1000 ).toFixed(2) + ' seconds');
			});
		// Hide the default waiting message that showed while tablesorter was building
		$("P.temporaryMessage").css('display', 'none');

		// ***************************
		// BELOW: NON-TABLESORTER STUFF
		// ***************************

		// Filter by semester(s) combobox
		$("#frmSubmit").click(function () {
			$('#frmFilterResults').submit(); // trigger the native submit event
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
			<h3>Moodle: Overall Usage Statistics By Course</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<form name="frmFilterResults" id="frmFilterResults" action="moodle-overall-usage-statistics-by-course.php" method="post">
				<table class="table table-bordered table-condensed small">
					<caption>
						<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryListOfSemesters; ?>">Show SQL
							query</a>
					</caption>
					<tr>
						<td style="text-align: right;">
							<h4>Filter Semester(s)</h4>
							<label>
								<select name="filterRange[]" id="filterRange" multiple="multiple" size="<?php echo mysqli_num_rows($resultsListOfSemesters); ?>">
									<?php
										while ($rowCPS = mysqli_fetch_array($resultsListOfSemesters)) {

											$semesterID = $rowCPS["semesteridentifier"];

											echo "<option value=\"$semesterID\"";
											if (in_array($semesterID, $semesterFilter)) {
												echo " selected=\"selected\" ";
											}
											echo ">20$semesterID</option>";
										}
									?>
								</select>
							</label>
						</td>
						<td style="text-align: left;">
							<h4>Filter Field(s)</h4>
							<?php
								// Split array into two halves, to create two tables for key
								$len            = count($courseOutputColumnStructure);
								$ocs_firstHalf  = array_slice($courseOutputColumnStructure, 0, $len / 2);
								$ocs_secondHalf = array_slice($courseOutputColumnStructure, $len / 2);

								echo "<table class=\"table table-striped table-condensed small\">";
								// output first half of "terms key"
								$flag_first_time = TRUE;
								foreach ($ocs_firstHalf as $ocs) {
									// Create string to hold optional bold tag
									$strBold = ($ocs[2] == 1) ? " wms_bold " : "";
									//$strIndent = ($ocs[2] == 0) ? " wms_indent " : "";
									if ($flag_first_time && $strBold) {
										echo "<tr>";
										$flag_first_time = FALSE;
									}
									elseif ($strBold) {
										echo "</tr><tr>";
									}
									echo "<td class=\"wms_nowrap " . $strBold . " \"><input type=\"checkbox\" name=\"ck" . $ocs[0] . " value=\"1\"/>&nbsp;" . $ocs[1] . "</td>";
								}
								echo "</tr><tr>";

								// output second half of "terms key"
								$flag_first_time = TRUE;
								foreach ($ocs_secondHalf as $ocs) {
									// Create string to hold optional bold tag
									$strBold = ($ocs[2] == 1) ? " wms_bold " : "";
									//$strIndent = ($ocs[2] == 0) ? " wms_indent " : "";
									if ($flag_first_time && $strBold) {
										echo "<tr>";
										$flag_first_time = FALSE;
									}
									elseif ($strBold) {
										echo "</tr><tr>";
									}
									echo "<td class=\"wms_nowrap " . $strBold . " \"><input type=\"checkbox\" name=\"ck" . $ocs[0] . " value=\"1\"/>&nbsp;" . $ocs[1] . "</td>";
								}
								echo "</tr></table>";
							?>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="center">
							<input type="button" id="frmSubmit" name="frmSubmit" class="btn btn-small btn-success" value="Filter Results" />
						</td>
					</tr>
				</table>
				<?php
					echo "<div class=\"alert alert-success\" role=\"alert\">Result: <strong>" . mysqli_num_rows($resultsCourses) . " courses</strong></div>";
				?>
			</form>

			<div class="pager pull-left">
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/first.png" class="first" alt="First item" title="First item" />
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/prev.png" class="prev" alt="Previous item" title="Previous item" />
				<span class="pagedisplay"></span>
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/next.png" class="next" alt="Next item" title="Next item" />
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/last.png" class="last" alt="Last item" title="Last item" />
				<select class="pagesize">
					<option selected="selected" value="25">25 rows/page</option>
					<option value="50">50 rows/page</option>
					<option value="100">100 rows/page</option>
					<option value="200">200 rows/page</option>
				</select>

				<p class="temporaryMessage largerfont bold">
					<br /><img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/loading.gif" alt="Results are loading..." title="Results are loading..." width="20" height="20" />&nbsp;&nbsp;Results
					are loading...</p>
			</div>

			<table id="myTableSorter01" class="tablesorter small">
				<caption>
					<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryCourses; ?>">Show SQL query</a>)<br />
					Sortable Columns (use "shiftKey" to sort multiple columns)<span id="sortertimer" class="indent"></span>
				</caption>
				<thead>
				<tr>
					<?php
						foreach ($courseOutputColumnStructure as $ocs) {
							echo "<th title='Sort on &quot;$ocs[1]&quot;' class='sorter-$ocs[3]'><div class='rotateText'>$ocs[1]</div></th>";
						}
					?>
				</tr>
				</thead>
				<tbody class="tablesorter-hidden">
				<?php
					while ($row = mysqli_fetch_array($resultsCourses)) {
						echo "<tr>";
						foreach ($courseOutputColumnStructure as $ocs) {
							if ($ocs[0] == 'idnumber') {
								// build querystring
								$course_link_query_string = "course=" . $row["courseid"];
								foreach ($courseOutputColumnStructure as $ocsL2) {
									$course_link_query_string .= '&' . $ocsL2[0] . '=' . rawurlencode($row[$ocsL2[0]]);
								}
								echo "<td nowrap=\"nowrap\"><a class=\"moreInfo\" href=\"/glowstats/moodle-course-students.php?$course_link_query_string\" title=\"Student Activity (by Course)\">" . $row[$ocs[0]] . "</a></td>";
							}
							elseif ($ocs[0] == 'EventLogActivity') {
								echo "<td><a class=\"moreInfo\" href=\"http://oldglow.williams.edu/course/report/log/index.php?chooselog=1&showusers=1&showcourses=0&id=" . $row["courseid"] . "&user=0&date=0&modid=&modaction=0&logformat=showashtml\" title=\"OldGlow --> View Original Log Records (by Course)\">" . $row["EventLogActivity"] . "</a></td>";
							}
							else {
								echo "<td>" . $row[$ocs[0]] . "</td>";
							}
						}
						echo "</tr>\n";
					}
				?>
				</tbody>
			</table>

			<div class="pager pull-left">
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/first.png" class="first" alt="First item" title="First item" />
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/prev.png" class="prev" alt="Previous item" title="Previous item" />
				<span class="pagedisplay"></span>
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/next.png" class="next" alt="Next item" title="Next item" />
				<img src="<?php echo APP_ROOT_PATH; ?>/js/jquery/plugins/mottie-tablesorter/addons/pager/icons/last.png" class="last" alt="Last item" title="Last item" />
				<select class="pagesize">
					<option selected="selected" value="25">25 rows/page</option>
					<option value="50">50 rows/page</option>
					<option value="100">100 rows/page</option>
					<option value="200">200 rows/page</option>
				</select>
			</div>
		</div>
	</div> <!-- /.row -->

	<?php require_once(dirname(__FILE__) . '/../include/foot.php'); ?>
</div> <!-- /.container -->
</body>
</html>
