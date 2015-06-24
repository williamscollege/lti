<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('sheet_openings_all'));
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		echo "<div id=\"content_container\">"; // begin: div#content_container

		// ***************************
		// fetch available openings
		// ***************************
		$USER->loadMyAvailableSheetOpenings();
		$USER->cacheMyAvailableSheetOpenings();
		 util_prePrintR($USER->sheet_openings_all); // debugging


		// display sheet_openings_all: "I can signup for..."
		if (count($USER->sheet_openings_all) == 0) {
			echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
			echo "<tr class=\"\">";
			echo "<th class=\"col-sm-6 bg-info\">There currently are no sheets on which you can sign up.</th>";
			echo "</tr>";
			echo "</table>";
		}
		else {
			$course_based_sheets = [];
			$other_based_sheets  = [];
			foreach ($USER->sheet_openings_all as $sheet) {

				$base_sheet_link = "<a href=\"" . APP_ROOT_PATH . "/app_code/sheet_openings_signup.php?sheet=" . htmlentities($sheet['s_id'], ENT_QUOTES, 'UTF-8') . "\"  class=\"\" title=\"Signup for Openings\">" . htmlentities($sheet['s_name'], ENT_QUOTES, 'UTF-8') . "</a>";
				// show the sheet owner's name
				$person = User::getOneFromDb(['user_id' => $sheet["s_owner_user_id"]], $DB);
				$base_sheet_link .= " (" . htmlentities($person->first_name, ENT_QUOTES, 'UTF-8') . " " . htmlentities($person->last_name, ENT_QUOTES, 'UTF-8');

				// if exists, also show description
				if ($sheet['s_description']){
					$base_sheet_link .= ": <span class=\"text-muted\">&quot;" . htmlentities($sheet['s_description'], ENT_QUOTES, 'UTF-8') . "&quot;</span>";
				}
				// end visual parenthesis
				$base_sheet_link .= ")";

				// NOTE: the A) through G) leads on the keys are used to sort. The display trims the first 3 chars from the key.
				switch ($sheet["a_type"]) {
					case "byuser":
						if (isset($other_based_sheets["A) I was specifically given access"])) {
							$other_based_sheets["A) I was given access"] .= "<li>$base_sheet_link</li>";
						}
						else {
							$other_based_sheets["A) I was given access"] = "<li>$base_sheet_link</li>";
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
							$other_based_sheets["B) I am in a course taught by"] .= "<li>Professor " . htmlentities($instr->first_name, ENT_QUOTES, 'UTF-8') . " " . htmlentities($instr->last_name, ENT_QUOTES, 'UTF-8') . " - $base_sheet_link</li>";
						}
						else {
							$other_based_sheets["B) I am in a course taught by"] = "<li>Professor " . htmlentities($instr->first_name, ENT_QUOTES, 'UTF-8') . " " . htmlentities($instr->last_name, ENT_QUOTES, 'UTF-8') . " - $base_sheet_link</li>";
						}
						break;
					case "bydept":
						if (isset($other_based_sheets["C) I am in a course in this department"])) {
							$other_based_sheets["C) I am in a course in this department"] .= "<li>" . htmlentities($sheet["a_constraint_data"], ENT_QUOTES, 'UTF-8') . " - $base_sheet_link</li>";
						}
						else {
							$other_based_sheets["C) I am in a course in this department"] = "<li>" . htmlentities($sheet["a_constraint_data"], ENT_QUOTES, 'UTF-8') . " - $base_sheet_link</li>";
						}
						break;
					case "bygradyear":
						if (isset($other_based_sheets["D) my grad year is {$sheet["a_constraint_data"]}"])) {
							$other_based_sheets["D) my grad year is {$sheet["a_constraint_data"]}"] .= "<li>$base_sheet_link</li>";
						}
						else {
							$other_based_sheets["D) my grad year is {$sheet["a_constraint_data"]}"] = "<li>$base_sheet_link</li>";
						}
						break;
					case "byrole":
						if ($sheet["a_constraint_data"] == "teacher") {
							if (isset($other_based_sheets["E) I am a teacher"])) {
								$other_based_sheets["E) I am a teacher"] .= "<li>$base_sheet_link</li>";
							}
							else {
								$other_based_sheets["E) I am a teacher"] = "<li>$base_sheet_link</li>";
							}
						}
						elseif ($sheet["a_constraint_data"] == "student") {
							if (isset($other_based_sheets["F) I am a student"])) {
								$other_based_sheets["F) I am a student"] .= "<li>$base_sheet_link</li>";
							}
							else {
								$other_based_sheets["F) I am a student"] = "<li>$base_sheet_link</li>";
							}

						}
						break;
					case "byhasaccount":
						if (isset($other_based_sheets["G) I have an account"])) {
							$other_based_sheets["G) I have an account"] .= "<li>$base_sheet_link</li>";
						}
						else {
							$other_based_sheets["G) I have an account"] = "<li>$base_sheet_link</li>";
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
				// begin table
				echo "<table class=\"table table-condensed table-bordered table-hover col-sm-12\">";
				echo "<tr class=\"\"><th class=\"col-sm-6 info\">Sheets available because I am enrolled in...</th></tr>";

				foreach ($course_based_sheets as $course => $items) {
					echo "<tr><td>";
					echo "<p>" . htmlentities($course, ENT_QUOTES, 'UTF-8') . "</p>";
					echo "<ul>" . $items . "</ul>";
					echo "</td></tr>";
				}
				echo "</table>";
				// end table

				// begin table
				echo "<table class=\"table table-condensed table-bordered table-hover col-sm-12\">";
				echo "<tr class=\"\"><th class=\"col-sm-6 info\">Sheets available because...</th></tr>";

				foreach ($other_based_sheets as $reason => $items) {
					echo "<tr><td>";
					echo "<p>" . htmlentities(substr($reason, 3), ENT_QUOTES, 'UTF-8') . "</p>";
					echo "<ul>" . $items . "</ul>";
					echo "</td></tr>";
				}
				echo "</table>";
				// end table
			}
			else // only one list has info
			{
				// begin table
				echo "<table class=\"table table-condensed table-bordered table-hover col-sm-12\">";
				echo "<tr class=\"\"><th class=\"col-sm-6 info\">I can sign up for these because...</th></tr>";

				foreach ($course_based_sheets as $course => $items) {
					echo "<tr><td>";
					echo "<p>" . htmlentities($course, ENT_QUOTES, 'UTF-8') . "</p>";
					echo "<ul>" . $items . "</ul>";
					echo "<br /></td></tr>";
				}
				foreach ($other_based_sheets as $reason => $items) {
					echo "<tr><td>";
					echo "<p>" . htmlentities(substr($reason, 3), ENT_QUOTES, 'UTF-8') . "</p>";
					echo "<ul>" . $items . "</ul>";
					echo "</td></tr>";
				}
				echo "</table>";
				// end table
			}
		}

		echo "</div>"; // end: div#content_container
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once(dirname(__FILE__) . '/../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/sheet_openings_all.js"></script>
