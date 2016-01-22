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
	# Errorhandling: Ensure querystring, else redirect
	#------------------------------------------------#
	if ($_SERVER["QUERY_STRING"] == "") {
		header("Location: /glowstats/moodle-courses-per-semester.php");
	}


	#------------------------------------------------#
	# Querystring: Fetch value
	#------------------------------------------------#
	$intCourseListing = intval($_GET["course"]);


	#------------------------------------------------#
	# SQL Purpose: Fetch all students per this one course
	#------------------------------------------------#
	$queryStudents = "
SELECT
	u.id
	,u.firstname AS firstname
	,u.lastname AS lastname
	,c.fullname as CourseName
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id) AS EventLogActivity
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='course') AS coursehitcount
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='resource') AS resourcehitcount
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='forum') AS forumhitcount
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='assignment') AS assignmenthitcount
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='quiz') AS quizhitcount
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='journal') AS journalhitcount
	,(SELECT count(*) FROM mdl_log WHERE course = c.id and userid = u.id AND module='lightboxgallery') AS lightboxhitcount
FROM
	mdl_user u
		JOIN
	mdl_role_assignments ra ON ra.userid = u.id
		JOIN
	mdl_context con ON ra.contextid = con.id
		JOIN
	mdl_course c ON c.id = con.instanceid
		JOIN
	mdl_role r ON ra.roleid = r.id
WHERE
	con.contextlevel = 50 AND r.id in (5) AND c.id = $intCourseListing
ORDER BY EventLogActivity DESC
LIMIT 0 , 500
	";
	$resultsStudents = mysqli_query($moodle_connString, $queryStudents) or
	die(mysqli_error($moodle_connString));
	$numRows = mysqli_num_rows($resultsStudents);

	$studentOutputColumnStructure =
		array(
			array('firstname', 'First Name', 'text')
		, array('lastname', 'Last Name', 'text')
		, array('EventLogActivity', 'Hits', 'digit')
		, array('coursehitcount', 'Course Hits', 'digit')
		, array('resourcehitcount', 'Resc Hits', 'digit')
		, array('forumhitcount', 'Forum Hits', 'digit')
		, array('assignmenthitcount', 'Assignment Hits', 'digit')
		, array('quizhitcount', 'Quiz Hits', 'digit')
		, array('journalhitcount', 'Journal Hits', 'digit')
		, array('lightboxhitcount', 'LightBox Hits', 'digit')
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

	//echo $queryStudents;
	//exit;
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
		$("#myTableSorter01")
			.tablesorter({
				// debug:true, // disable for live use
				theme: 'blue',
				// prevent first column from being sortable
				headers: {
					0: {sorter: false}	// for numbering widget
				},
				sortList: [
					[3, 1]
				],
				widthFixed: true,
				sortReset: true, // allow you to click on the table header a third time to reset the sort direction
				// sortRestart: true, // start the sort with the sortInitialOrder when clicking on a previously unsorted column
				sortInitialOrder: 'desc', // starting sort direction "asc" or "desc"
				// initialize a bunch of widgets
				widgets: ['zebra', 'columns', 'numbering'], // note: numbering is a custom widget
				widgetOptions: {
					zebra: ['odd', 'even'],
					columns: ['primary', 'secondary', 'tertiary']
				}
			});
		// add custom numbering widget
		$.tablesorter.addWidget({
			id: "numbering",
			format: function (table) {
				var c = table.config;
				$("tr:visible", table.tBodies[0]).each(function (i) {
					$(this).find('td').eq(0).text(i + 1);
				});
			}
		});

		// ***************************
		// BELOW: NON-TABLESORTER STUFF
		// ***************************

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
			<h3>Moodle: Student Activity (by Course)</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<div class="well well-sm small">
				<?php
					echo "<div class=\"alert alert-success\" role=\"alert\">" . $numRows . " students in &quot;<strong>" . $_GET["idnumber"] . "&quot;</strong></div>";
				?>
			</div>

			<table id="myTableSorter01" class="tablesorter small">
				<caption>
					<a href="#" class="show-sql-statement" title="Show SQL query" data-sql-statement="<?php echo $queryStudents; ?>">Show SQL query</a>
				</caption>
				<thead>
				<tr><?php
						echo "<th>#</th>";
						foreach ($studentOutputColumnStructure as $ocs) {
							echo "
								<th title='Sort on &quot;$ocs[1]&quot;' class='sorter-$ocs[2]'>$ocs[1]</th>";
						}
					?>
				</tr>
				<tr><?php
						echo "<td style=\"text-align: center; font-weight: bold; border-top: 1px solid #000000;border-bottom: 1px solid #000000;\" colspan=\"3\">Class Statistic Totals:</td>";
						foreach ($studentOutputColumnStructure as $ocs) {
							if (($ocs[0] != "firstname") && ($ocs[0] != "lastname")) {
								echo "<td style=\"font-weight:bold; border-top: 1px solid #000000;border-bottom: 1px solid #000000;\">" . $_GET["$ocs[0]"] . "</td>";
							}
						}
					?>
				</tr>
				</thead>
				<tfoot>
				<tr><?php
						echo "<th>#</th>";
						foreach ($studentOutputColumnStructure as $ocs) {
							echo "
								<th>$ocs[1]</th>";
						}
					?>
				</tr>
				</tfoot>
				<tbody>

				<?php
					$intCounter = 0;
					while ($row = mysqli_fetch_array($resultsStudents)) {
						echo "<tr>";
						$intCounter += 1;
						echo "<td>$intCounter</td>";
						foreach ($studentOutputColumnStructure as $ocs) {
							if ($ocs[0] == "lastname") {
								echo "<td><a class=\"moreInfo\" href=\"http://oldglow.williams.edu/course/report/log/index.php?chooselog=1&showusers=1&showcourses=0&id=" . $intCourseListing . "&user=" . $row["id"] . "&date=0&modid=&modaction=0&logformat=showashtml\" title=\"OldGlow --> View Original Log Records (for Student)\">" . $row["lastname"] . "</a></td>";
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
		</div>
	</div> <!-- /.row -->

	<?php require_once(dirname(__FILE__) . '/../include/foot.php'); ?>
</div> <!-- /.container -->
</body>
</html>
