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
	# AJAX Form: Fetch posted value, or assign default
	#------------------------------------------------#
	$intFilter = (isset($_POST["ajaxVal"])) ? quote_smart(intval($_POST["ajaxVal"])) : 20;


	# CASE STATEMENT: AJAX
	switch ($_POST["ajaxForm"]) {
		case "frmNGC":
			// Form Name: frmNGC
			#------------------------------------------------#
			# SQL Purpose: Fetch number of Glow courses accessed at least n times, by semester
			# x = $intFilter
			#------------------------------------------------#
			$queryCoursesAccessedNTimes = "
SELECT
	SUBSTRING(c.idnumber, 1, 3) AS semesterIdentifier,
	COUNT(id) AS numCourses
FROM
	mdl_course c
JOIN
	(SELECT
		course, COUNT(id) AS accessCount
	FROM
		mdl_log
	GROUP BY course
	HAVING COUNT(id) >= $intFilter) AS course_access_ct
ON course_access_ct.course = c.id
AND c.visible = 1
WHERE
	c.idnumber REGEXP '^[0-9]{2}[A-Z]{1}-'
GROUP BY semesterIdentifier
ORDER BY c.idnumber;
			";
			$resultsCoursesAccessedNTimes = mysqli_query($moodle_connString, $queryCoursesAccessedNTimes) or
			die(mysqli_error($moodle_connString));
			?>
			<table class="tablesorter small">
				<caption>
					<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryCoursesAccessedNTimes; ?>">Show SQL
						query</a><br />
					How many active Glow courses were accessed at least <strong><em>X</em></strong> times, by semester?
				</caption>
				<thead>
				<tr>
					<th>Semester</th>
					<th>Number Courses</th>
				</tr>
				</thead>
				<tbody>
				<?php
					while ($rowCANT = mysqli_fetch_array($resultsCoursesAccessedNTimes)) {

						$semesterID    = $rowCANT["semesterIdentifier"];
						$numberCourses = $rowCANT["numCourses"];

						echo "<tr>";
						echo "<td>" . $semesterID . "</td>";
						echo "<td>" . $numberCourses . "</td>";
						echo "</tr>";
					}
				?>
				</tbody>
			</table>
			<?php
			break;

		case "frmSUG":
			// Form Name: frmSUG
			#------------------------------------------------#
			# SQL Purpose: Fetch How many students had at least one active Glow course, by semester?
			# REQUIRES: temp table "wms_tmp_course_access" (TBD: benchmark this as it may not provide desired efficiency boost)
			# x = $intFilter
			#------------------------------------------------#
			$queryStudentsUsingGlow = "
SELECT
	SUBSTRING(c.idnumber, 1, 3) AS semesterIdentifier,
	COUNT(DISTINCT u.id) AS enrolledStudentCount
FROM
	mdl_user u
		JOIN
	mdl_role_assignments ra ON ra.userid = u.id
		JOIN
	mdl_context con ON ra.contextid = con.id
		JOIN
	mdl_course c ON c.id = con.instanceid
	AND c.visible = 1
		JOIN
	mdl_role r ON ra.roleid = r.id
		JOIN
	wms_tmp_course_access AS wmsca ON wmsca.course = c.id
--		AND accessCount >= $intFilter
WHERE
	con.contextlevel = 50 AND r.id IN (5) AND c.idnumber REGEXP '^[0-9]{2}[A-Z]{1}-'
GROUP BY semesterIdentifier
ORDER BY semesterIdentifier DESC;
			";
			$resultsStudentsUsingGlow = mysqli_query($moodle_connString, $queryStudentsUsingGlow) or
			die(mysqli_error($moodle_connString));
			?>
			<table class="tablesorter small">
				<caption>
					<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryStudentsUsingGlow; ?>">Show SQL
						query</a><br />
					How many students had at least one active Glow course, by semester
					(where "active" means having at least <strong><em>X</em></strong> total log entries)?
				</caption>
				<thead>
				<tr>
					<th>Semester</th>
					<th>Students Using Glow</th>
				</tr>
				</thead>
				<tbody>
				<?php
					while ($rowSUG = mysqli_fetch_array($resultsStudentsUsingGlow)) {

						$semesterID       = $rowSUG["semesterIdentifier"];
						$enrolledStudents = $rowSUG["enrolledStudentCount"];

						echo "<tr>";
						echo "<td>" . $semesterID . "</td>";
						echo "<td>" . $enrolledStudents . "</td>";
						echo "</tr>";
					}
				?>
				</tbody>
			</table>
			<?php
			break;
		default:
			echo "Call OIT. There's a problem with an entered form value.";
			exit();
			break;
	}

	/*
Debugging:
	echo "<pre>" . print_r($_POST) . "</pre>";
	print_r($_REQUEST);
	echo $resultsStudentsUsingGlow;
	echo "ajaxForm: " . intval($_POST["ajaxForm"]);
	echo "ajaxVal: " . intval($_POST["ajaxVal"]);
	exit();
 */
?>

