<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('sheet_openings_all'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {

		echo "<div id=\"content_container\">"; // start: div#content_container

		// ***************************
		// fetch available openings
		// ***************************
		$USER->cacheMyAvailableSheetOpenings();
		// util_prePrintR($USER->sheet_openings_all); // debugging


		// display sheet_openings_all: "I can signup for..."
		if (count($USER->sheet_openings_all) == 0) {
			echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
			echo "<tr class=\"\">";
			echo "<th class=\"col-sm-6 bg-warning\">There are no sheets on which you can sign up.</th>";
			echo "</tr>";
			echo "</table>";
		}
		else {
			$course_based_sheets = [];
			$other_based_sheets  = [];
			foreach ($USER->sheet_openings_all as $sheet) {

				$base_sheet_link = "<a href=\"sheet_openings_signup.php?sheet=" . $sheet['s_id'] . "\"  class=\"\" title=\"Signup for Openings\">" . $sheet['s_name'] . "</a> (" . $sheet['s_description'] . ")";

				// NOTE: the A) through G) leads on the keys are used to sort. The display trims the first 3 chars from the key.
				switch ($sheet["a_type"]) {
					case "byuser":
						if (isset($other_based_sheets["A) I was specifically given access"])) {
							$other_based_sheets["A) I was specifically given access"] .= "<li>$base_sheet_link</li>";
						}
						else {
							$other_based_sheets["A) I was specifically given access"] = "<li>$base_sheet_link</li>";
						}
						break;
					case "bycourse":
						$course = Course::getAllFromDb(['course_idstr' => $sheet["a_constraint_data"]], $DB);

						if (isset($course_based_sheets[$course[0]->course_idstr])) {
							$course_based_sheets[$course[0]->short_name] .= "<li>$base_sheet_link</li>";
						}
						else {
							$course_based_sheets[$course[0]->short_name] = "<li>$base_sheet_link</li>";
						}
						break;
					case "byinstr":
						$instr = User::getOneFromDb(['user_id' => $sheet["a_constraint_id"]], $DB);

						if (isset($other_based_sheets["B) I am in a course taught by"])) {
							$other_based_sheets["B) I am in a course taught by"] .= "<li>Professor " . $instr->first_name . " " . $instr->last_name . " - $base_sheet_link</li>";
						}
						else {
							$other_based_sheets["B) I am in a course taught by"] = "<li>Professor " . $instr->first_name . " " . $instr->last_name . " - $base_sheet_link</li>";
						}
						break;
					case "bydept":
						if (isset($other_based_sheets["C) I am in a course in this department"])) {
							$other_based_sheets["C) I am in a course in this department"] .= "<li>" . $sheet["a_constraint_data"] . " - $base_sheet_link</li>";
						}
						else {
							$other_based_sheets["C) I am in a course in this department"] = "<li>" . $sheet["a_constraint_data"] . " - $base_sheet_link</li>";
						}
						break;
					case "bygradyear":
						if (isset($other_based_sheets["D) your grad year is {$sheet["a_constraint_data"]}"])) {
							$other_based_sheets["D) your grad year is {$sheet["a_constraint_data"]}"] .= "<li>$base_sheet_link</li>";
						}
						else {
							$other_based_sheets["D) your grad year is {$sheet["a_constraint_data"]}"] = "<li>$base_sheet_link</li>";
						}
						break;
					case "byrole":
						if ($sheet["a_constraint_data"] == "teacher") {
							if (isset($other_based_sheets["E) I am teaching a course"])) {
								$other_based_sheets["E) I am teaching a course"] .= "<li>$base_sheet_link</li>";
							}
							else {
								$other_based_sheets["E) I am teaching a course"] = "<li>$base_sheet_link</li>";
							}
						}
						elseif ($sheet["a_constraint_data"] == "student") {
							if (isset($other_based_sheets["F) I am a student in a course"])) {
								$other_based_sheets["F) I am a student in a course"] .= "<li>$base_sheet_link</li>";
							}
							else {
								$other_based_sheets["F) I am a student in a course"] = "<li>$base_sheet_link</li>";
							}

						}
						break;
					case "byhasaccount":
						if (isset($other_based_sheets["G) you have an account on this system"])) {
							$other_based_sheets["G) you have an account on this system"] .= "<li>$base_sheet_link</li>";
						}
						else {
							$other_based_sheets["G) you have an account on this system"] = "<li>$base_sheet_link</li>";
						}
						break;
					default:
						break;
				}
			}

			// util_prePrintR($other_based_sheets);
			ksort($course_based_sheets);
			ksort($other_based_sheets);

			if ($course_based_sheets && $other_based_sheets) {
				// start table
				echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
				echo "<tr class=\"\"><th class=\"col-sm-6 info\">Sheets available because I am enrolled in...</th></tr>";
				echo "<tr><td>";

				foreach ($course_based_sheets as $course => $items) {
					echo "<strong>" . $course . "</strong>";
					echo "<ul>" . $items . "</ul>";
				}
				// end table
				echo "</td></tr></table>";

				// start table
				echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
				echo "<tr class=\"\"><th class=\"col-sm-6 info\">Sheets available because...</th></tr>";
				echo "<tr><td>";

				foreach ($other_based_sheets as $reason => $items) {
					echo "<strong>" . substr($reason, 3) . "</strong>";
					echo "<ul>" . $items . "</ul>";
				}

				// end table
				echo "</td></tr></table>";
			}
			else // only one list has info
			{
				// start table
				echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
				echo "<tr class=\"\"><th class=\"col-sm-6 info\">I can sign up for these because...</th></tr>";
				echo "<tr><td>";

				foreach ($course_based_sheets as $course => $items) {
					echo "<strong>" . $course . "</strong>";
					echo "<ul>" . $items . "</ul>";
				}
				foreach ($other_based_sheets as $reason => $items) {
					echo "<br /><strong>" . substr($reason, 3) . "</strong>";
					echo "<ul>" . $items . "</ul>";
				}
				// end table
				echo "</td></tr></table>";
			}
		}

		echo "</div>"; // end: div#content_container
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/sheet_openings_all.js"></script>
