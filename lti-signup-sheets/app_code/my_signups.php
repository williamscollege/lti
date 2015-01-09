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
				echo "<strong>" . date('F d, Y', strtotime($signup['begin_datetime'])) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($signup['begin_datetime'])) . " - " . date('g:i:A', strtotime($signup['end_datetime']));
				// display x of y total signups for this opening
				echo "&nbsp;(" . $signup['current_signups'] . "/" . $signup['max_signups'] . ")";
				// popovers (bootstrap: must manually initialize popovers in JS file)
				echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Description:</strong> " . $signup['description'] . "<br /><strong>Where:</strong> " . $signup['location'] .  "\">" . $signup['name'] . "</a>";
				echo "</p>";
			}
			echo "</td>";
			echo "<td>";
			foreach ($USER->signups_on_my_sheets as $scheduled) {
				// date
				echo "<strong>" . date('F d, Y', strtotime($scheduled['begin_datetime'])) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($scheduled['begin_datetime'])) . " - " . date('g:i:A', strtotime($scheduled['end_datetime']));
				// display x of y total signups for this opening
				echo "&nbsp;(" . $scheduled['current_signups'] . "/" . $scheduled['max_signups'] . ")";
				// link to edit
				// TODO - add functionality to link click through
				echo "&nbsp;<a href=\"edit_opening.php?opening_id=" . $scheduled['opening_id'] . "\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Description:</strong> " . $scheduled['description'] . "<br /><strong>Where:</strong> " . $scheduled['location'] .  "\">" . $scheduled['name'] . "</a>";
				// list signups
				echo "<ul class=\"unstyled\">";
				foreach ($scheduled['array_signups'] as $person) {
					// TODO - add ajax and test removal functionality
					echo "<li>";
					echo "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-for-opening-id=\"" . $person['opening_id'] . "\" data-for-signup-id=\"" . $person['signup_id'] . "\" title=\"Remove signup\"><i class=\"glyphicon glyphicon-trash\"></i></a>";
					echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>User:</strong>&nbsp; " . $person['username'] . "<br /><strong>Email:</strong> " . $person['email'] . "<br /><strong>Signed up:</strong> " .  date('n/j/Y g:i:A', strtotime($person['signup_created_at'])) .  "\">" . $person['full_name'] . "</a>";
					echo "</li>";
				}
				echo "</ul>";

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
