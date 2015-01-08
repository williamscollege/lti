<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_signups'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div>";
		echo "<h3>My Signups</h3>";
		echo "<p>&nbsp;</p>";

		// ***************************
		// fetch signups: "I have signed up for"
		// ***************************
		$USER->cacheMySignups();
		//util_prePrintR($USER->my_signups);

		// ***************************
		// fetch my_signups: "Signups on my Sheets"
		// ***************************
		$USER->cacheSignupsOnMySheets();


		// display my_signups: "I have signed up for"
		if ($USER->my_signups) {
			echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
			echo "<tr class=\"\">";
			echo "<th class=\"col-sm-6 info\">I've Signed up for...</th>";
			echo "<th class=\"col-sm-6 info\">Sign-ups on my Sheets...</th>";
			echo "</tr>";
			echo "<tr><td>";
			foreach ($USER->my_signups as $signup) {
				// date
				echo "<p>";
				echo "<strong>" . date('F d, Y', strtotime($signup->begin_datetime)) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($signup->begin_datetime)) . " - " . date('g:i:A', strtotime($signup->end_datetime));
				// display x of y total signups for this opening
				echo "&nbsp;(x/" . $signup->max_signups . ")";
				// popovers (bootstrap: must manually initialize popovers in JS file)
				echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"top\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Description:</strong> " . $signup->description . "<br /><strong>Where:</strong> " . $signup->location .  "\">" . $signup->name . "</a>";
				echo "</p>";
			}
			echo "</td>";
			echo "<td>";
			foreach ($USER->signups_on_my_sheets as $who_signed_up) {
				// date
				echo "<p>";
				echo "<strong>" . date('F d, Y', strtotime($who_signed_up->begin_datetime)) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($who_signed_up->begin_datetime)) . " - " . date('g:i:A', strtotime($who_signed_up->end_datetime));
				// display x of y total signups for this opening
				echo "&nbsp;(x/" . $who_signed_up->max_signups . ")";
				// link to edit sheet
				echo "&nbsp;<a href=\"edit_sheet.php?sheet_id=" . $who_signed_up->sheet_id . "\"  title=\"Edit sheet\">" . $who_signed_up->name . "</a>";
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


<script type="text/javascript" src="../js/my_signups.js"></script>
