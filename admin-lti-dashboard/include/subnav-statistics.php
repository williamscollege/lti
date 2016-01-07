<?php
	function checkLocation($fileName) {
		$filePath = $_SERVER["REQUEST_URI"];
		//echo $filePath;
		if (strpos($filePath, $fileName)) {
			//return TRUE;
			echo ' class="wms_bold_nav_link" ';
		}
		else {
			return FALSE;
		}
	}
?>

<table class="table-bordered table-condensed small">
	<tbody>
	<tr class="bg-success">
		<td><strong>Canvas</strong></td>
		<td>2013-present</td>
		<td>
			<a href="https://glow.williams.edu/accounts/98616/statistics" title="Canvas: Statistics" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Canvas:
				Statistics</a>
		</td>
		<td>
			<a href="https://glow.williams.edu/accounts/98616/analytics" title="Canvas: Analytics" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Canvas:
				Analytics</a>
		</td>
		<td>
			<a href="glow-all-versions.xlsx" title="Download All Statistics (.xlsx)" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Download
				All Statistics (.xlsx)</a></td>
	</tr>
	<tr class="bg-warning">
		<td rowspan="2"><strong>Moodle</strong></td>
		<td rowspan="2">2010-2013</td>
		<td><a href="moodle-overall-usage-per-semester.php" title="Moodle: Overall Usage per Semester"
				<?php checkLocation("moodle-overall-usage-per-semester.php"); ?>
			>Overall Usage per Semester</a></td>
		<td>
			<a href="moodle-overall-usage-statistics-by-course.php" title="Moodle: Overall Usage Statistics By Course"
				<?php checkLocation("moodle-overall-usage-statistics-by-course.php"); ?>
			>Overall Usage Statistics By Course</a></td>
		<td><a href="moodle-courses-per-semester.php" title="Moodle: Courses per Semester"
				<?php checkLocation("moodle-courses-per-semester.php"); ?>
			>Courses per Semester</a></td>
	</tr>
	<tr class="bg-warning">
		<td><a href="moodle-courses-accessed-x-times.php" title="Moodle: Courses Accessed (x times)"
				<?php checkLocation("moodle-courses-accessed-x-times.php"); ?>
			>Courses Accessed (x times)</a></td>
		<td><a href="moodle-student-use-of-glow.php" title="Moodle: Student Use of Glow (x times)"
				<?php checkLocation("moodle-student-use-of-glow.php"); ?>
			>Student Use of Glow (x times)</a></td>
		<td><a href="moodle-student-enrollment-per-semester.php" title="Moodle: Student Enrollment per Semester"
				<?php checkLocation("moodle-student-enrollment-per-semester.php"); ?>
			>Student Enrollment per Semester</a>
		</td>
	</tr>
	<tr class="bg-danger">
		<td><strong>Blackboard</strong></td>
		<td>2003-2010</td>
		<td>
			<a href="blackboard-statistics/index.htm" title="Blackboard: Archived Reports (All)" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;Archived
				Reports (All)</a>
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</tbody>
</table>
