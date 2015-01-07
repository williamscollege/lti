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
		$USER->cacheManagedSheets();


		// display my_signups: "I have signed up for"
		if ($USER->my_signups) {
			echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
			echo "<tr class=\"\">";
			echo "<th class=\"col-sm-6 info\">I've Signed up for...</th>";
//			echo "<th class=\"col-sm-1\">&nbsp;</th>";
			echo "<th class=\"col-sm-6 info\">Sign-ups on my Sheets...</th>";
			echo "</tr>";
			echo "<tr><td>";
			foreach ($USER->my_signups as $signup) {
				//echo "<td class=\"col-sm-11\">";
				// Date
				echo "<p>";
				echo "<strong>" . date('F d, Y', strtotime($signup->begin_datetime)) . "</strong>";
				echo "<br />";
				// Time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($signup->begin_datetime)) . " - " . date('g:i:A', strtotime($signup->begin_endtime));
				// popovers (bootstrap: must manually initialize popovers in JS file)
				echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"top\" data-trigger=\"focus\" title=\"" . $signup->name . "\" data-content=\"" . $signup->description . "\">" . $signup->name . "</a>";
				echo "</p>";
			}
			echo "</td>";
//			echo "<td>&nbsp;</td>";
			echo "<td>";
			echo " data <br /> data <br /> data <br /> data <br /> data <br /> data <br /> data <br /> data <br /> data <br /> data <br /> data <br /> data <br />";
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
