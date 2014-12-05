<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_signups'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo 'hello world. page = available_openings.php';


		/* --------------------------- */
		/* START OLD CODE */
		/* --------------------------- */
		//		if (!verify_in_signup_sheets()) {
		//			die("not in signup_sheets");
		//		}

		// look at the sheet access table and get all the sheets on which the current user can sign up
		//$accessibleSheets = getSignupAccessibleSheets(TRUE);
		$accessibleSheets = getSignupAccessibleSheets(TRUE);

		echo '<div id="sus_accessible_sheets">' . "\n";
		if (count($accessibleSheets) < 1) {
			echo "There are no sheets on which you can sign up.";
		}
		else {
			$course_based_sheets = array();
			$other_based_sheets  = array();
			$prior_sheet_id      = '';
			foreach ($accessibleSheets as $sheet) {
				if ($sheet->s_id != $prior_sheet_id) {
					$prior_sheet_id  = $sheet->s_id;
					$cause           = '';
					$base_sheet_link = "<a href=\"$ss_href&action=signuponsheet&sheet={$sheet->s_id}&access={$sheet->a_id}\">{$sheet->s_name}</a>";
					// NOTE: the A) through G) leads on the keys are used to sort. The display trims the first 3 chars from the key.
					switch ($sheet->a_type) {
						case 'byuser':
							$other_based_sheets['A) you were specifically given access'] .= "      <li>$base_sheet_link</li>\n";
							break;
						case 'bycourse':
							$course = get_record('course', 'id', $sheet->a_constraint_id);
							$course_based_sheets[$course->fullname] .= "      <li>$base_sheet_link</li>\n";
							break;
						case 'byinstr':
							$instr = get_record('user', 'id', $sheet->a_constraint_id);
							$other_based_sheets["B) you're in a course taught by"] .= "      <li>Professor " . $instr->lastname . " - $base_sheet_link</li>\n";
							break;
						case 'bydept':
							$other_based_sheets["C) you're in a course in this department"] .= "      <li>" . $sheet->a_constraint_data . " - $base_sheet_link</li>\n";
							break;
						case 'bygradyear':
							$other_based_sheets["D) your grad year is {$sheet->a_constraint_data}"] .= "      <li>$base_sheet_link</li>\n";
							break;
						case 'byrole':
							if ($sheet->a_constraint_data == 'teacher') {
								$other_based_sheets["E) you're teaching a course"] .= "      <li>$base_sheet_link</li>\n";
							}
							else {
								if ($sheet->a_constraint_data == 'student') {
									$other_based_sheets["F) you're a student in a course"] .= "      <li>$base_sheet_link</li>\n";
								}
							}
							break;
						case 'byhasaccount':
							$other_based_sheets['G) you have an account on this system'] .= "      <li>$base_sheet_link</li>\n";
							break;
						default:
							break;
					}
				} // end if new sheet (i.e. cur != prior)
			}
			ksort($course_based_sheets);
			ksort($other_based_sheets);
			if ($course_based_sheets && $other_based_sheets) {
				?>
				<div id="course_sheets">
					<h3>Sheets available because you're enrolled in...</h3>
					<ul>
						<?php
							foreach ($course_based_sheets as $course => $items) {
								echo "
  <li>$course
    <ul>
$items
    </ul>
  </li>
";
							}
						?>
					</ul>
				</div>
				<div id="other_sheets">
					<h3>Sheets available because...</h3>
					<ul>
						<?php
							foreach ($other_based_sheets as $reason => $items) {
								echo "
  <li>" . substr($reason, 3) . "
    <ul>
$items
    </ul>
  </li>
";
							}
						?>
					</ul>
				</div>
			<?php
			}
			else // only one list has info
			{
				?>
				You can sign up for:
				<ul>
					<?php
						foreach ($course_based_sheets as $course => $items) {
							echo "
  <li>$course
    <ul>
$items
    </ul>
  </li>
";
						}
						foreach ($other_based_sheets as $reason => $items) {
							echo "
  <li>" . substr($reason, 3) . "
    <ul>
$items
    </ul>
  </li>
";
						}
					?>
				</ul>
			<?php
			}
		}
		?>
		</div><!-- end sus_accessible_sheets -->
		<?php

		/* --------------------------- */
		/* END OLD CODE */
		/* --------------------------- */
	}

	require_once('../foot.php');
