<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('signups_all'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div id=\"parent_container\">"; // start: div#parent_container
		echo "<h3>" . $pageTitle . "</h3>";
		echo "<p>&nbsp;</p>";

		// ***************************
		// fetch signups: "I have signed up for"
		// ***************************
		$USER->cacheMySignups();
		// util_prePrintR($USER->signups_all);

		// ***************************
		// fetch signups_all: "Signups on my Sheets"
		// ***************************
		$USER->cacheSignupsOnMySheets();
		// util_prePrintR($USER->signups_on_my_sheets);


		echo "<table class=\"table table-condensed table-bordered col-sm-12\">";
		echo "<tr class=\"\">";
		echo "<th class=\"col-sm-6 info\">I've Signed up for...</th>";
		echo "<th class=\"col-sm-6 info\">Sign-ups on my Sheets...</th>";
		echo "</tr>";
		echo "<tr><td>";

		// TODO - if empty array, err msg: "Fatal error: an invalid value was given in the search hash in C:\xampp\htdocs\GITHUB\lti\lti-signup-sheets\classes\db_linked.class.php on line 299"
		// COLUMN 1: "I've Signed up for..."
		if (count($USER->signups_all) == 0) {
			echo "<p class=\"col-sm-6 bg-warning\">You have not signed up for any openings.</p>";
		}
		else {
			// obsolete - original code
			util_prePrintR($USER->signups_all);
			foreach ($USER->signups_all as $signup) {
				// date
				echo "<p>";
				echo "<strong>" . date('F d, Y', strtotime($signup['begin_datetime'])) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($signup['begin_datetime'])) . " - " . date('g:i:A', strtotime($signup['end_datetime']));
				// display x of y total signups for this opening
				echo "&nbsp;(" . $signup['current_signups'] . "/" . $signup['opening_max_signups'] . ")";
				// popovers (bootstrap: must manually initialize popovers in JS file)
				echo " for <a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Opening Name:</strong> " . $signup['opening_name'] . "<br /><strong>Description:</strong> " . $signup['opening_description'] . "<br /><strong>Where:</strong> " . $signup['opening_location'] . "\">" . $signup['sheet_name'] . "</a>";
				echo "</p>";
			}

			// new code
			?>
			<div id="openings-list-container">

				<?php
/*
					//$s->cacheOpenings();
					$lastOpeningDate = '';
					$daysOpenings    = [];
					$todayYmd        = explode(' ', util_currentDateTimeString())[0];
					//					foreach ($s->openings as $opening) {
					foreach ($USER->signups_all as $signup) {
						$curOpeningDate = explode(' ', $opening->begin_datetime)[0];
						if ($curOpeningDate != $lastOpeningDate) {
							// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
							foreach ($daysOpenings as $op) {
								echo $op->renderAsHtmlShortWithFullControls() . "\n";
							}

							if ($lastOpeningDate) {
								echo '</div>' . "\n";
							}
							$relative_time_class = 'in-the-past';
							//util_prePrintR('$curOpeningDate : $todayYmd = '.$curOpeningDate .':'. $todayYmd);
							//exit;
							if ($curOpeningDate == $todayYmd) {
								$relative_time_class = 'in-the-present';
							}
							elseif ($curOpeningDate > $todayYmd) {
								$relative_time_class = 'in-the-future';
							}
							echo '<div class="opening-list-for-date ' . $relative_time_class . '" data-for-date="' . $curOpeningDate . '"><h4>' . date_format(new DateTime($opening->begin_datetime), "m/d/Y") . '</h4>';
							$daysOpenings = [];
						}
						//													echo $opening->renderAsHtmlShortWithFullControls()."\n";
						array_unshift($daysOpenings, $opening);

						$lastOpeningDate = $curOpeningDate;
						//													util_prePrintR($opening);
						//$s = substr('tmp', $lastOpeningDate);
					}
					// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
					foreach ($daysOpenings as $op) {
						echo $op->renderAsHtmlShortWithFullControls() . "\n";
					}
					echo '</div>' . "\n";*/

				?>

			</div>
		<?php
		}
		echo "</td>";

		echo "<td>";
		// COLUMN 2: "Sign-ups on my Sheets..."
		// TODO - if empty array, err msg: "Fatal error: an invalid value was given in the search hash in C:\xampp\htdocs\GITHUB\lti\lti-signup-sheets\classes\db_linked.class.php on line 299"
		if (count($USER->signups_on_my_sheets) == 0) {
			echo "<p class=\"col-sm-6 bg-warning\">No one has signed up on your sheets.</p>";
		}
		else {
			foreach ($USER->signups_on_my_sheets as $scheduled) {
				// date
				echo "<div id=\"group-signups-for-opening-id-" . $scheduled['opening_id'] . "\">";
				echo "<strong>" . date('F d, Y', strtotime($scheduled['begin_datetime'])) . "</strong>";
				echo "<br />";
				// time opening
				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($scheduled['begin_datetime'])) . " - " . date('g:i:A', strtotime($scheduled['end_datetime']));
				// display x of y total signups for this opening
				echo "&nbsp;(" . $scheduled['current_signups'] . "/" . $scheduled['max_signups'] . ")";
				// link to edit
				// TODO - add functionality to link click through
				echo "&nbsp;<a href=\"edit_opening.php?opening_id=" . $scheduled['opening_id'] . "\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Description:</strong> " . $scheduled['description'] . "<br /><strong>Where:</strong> " . $scheduled['location'] . "\">" . $scheduled['name'] . "</a>";
				// list signups
				echo "<ul class=\"unstyled\">";
				foreach ($scheduled['array_signups'] as $person) {
					//util_prePrintR($person);
					echo "<li>";
					// dkc says: could tighten-up UI, if needed: by btn-link instead of btn btn-xs. can color the remove icon red, manually/automatically?
					echo "<a href=\"#\" id=\"btn-remove-signup-id-" . $person['signup_id'] . "\"  class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $person['opening_id'] . "\" data-for-signup-name=\"" . $person['full_name'] . "\" data-for-signup-id=\"" . $person['signup_id'] . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
					echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>User:</strong>&nbsp; " . $person['username'] . "<br /><strong>Email:</strong> " . $person['email'] . "<br /><strong>Signed up:</strong> " . date('n/j/Y g:i:A', strtotime($person['signup_created_at'])) . "\">" . $person['full_name'] . "</a>";
					echo "</li>";
				}
				echo "</ul>";
				echo "</div>";
			}
		}
		echo "</td>";
		echo "</tr>";
		echo "</table>";

		echo "</div>"; // end: div#parent_container
	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/signups_all.js"></script>
