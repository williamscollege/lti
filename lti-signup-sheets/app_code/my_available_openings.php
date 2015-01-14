<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_available_openings'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div>";
		echo "<h3>" . ucfirst(util_lang('my_available_openings')) . "</h3>";
		echo "<p>&nbsp;</p>";

		// ***************************
		// fetch available openings
		// ***************************
		$USER->cacheMyAvailableOpenings();

		echo "Hah the count is: " . count($USER->my_available_openings);
		util_prePrintR($USER->my_available_openings);





		// display my_available_openings: "I can signup for..."
		if ($USER->my_available_openings) {
			echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
			echo "<tr class=\"\">";
			echo "<th class=\"col-sm-6 info\">I May Signup for...</th>";
			echo "</tr>";
			echo "<tr><td>";
			foreach ($USER->my_available_openings as $opening) {
				// date
				echo "<p>";
				echo "<strong>" . date('F d, Y', strtotime($opening['begin_datetime'])) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($opening['begin_datetime'])) . " - " . date('g:i:A', strtotime($opening['end_datetime']));
				// display x of y total signups for this opening
				echo "&nbsp;(" . $opening['current_signups'] . "/" . $opening['max_signups'] . ")";
				// popovers (bootstrap: must manually initialize popovers in JS file)
				echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Description:</strong> " . $opening['description'] . "<br /><strong>Where:</strong> " . $opening['location'] . "\">" . $opening['name'] . "</a>";
				echo "</p>";
			}
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}

		// end parent div
		echo "</div>";
	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="../js/my_available_openings.js"></script>
