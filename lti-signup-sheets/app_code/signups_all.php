<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('signups_all'));
	require_once('../app_head.php');


	function renderAsHtmlShortForSignupsMine($signup) {
		$rendered = '<div class="list-signups list-signup-id-' . $signup['signup_id'] . '">';
		$rendered .= '<span class="opening-time-range">' . date_format(new DateTime($signup['begin_datetime']), "h:i A") . ' - ' . date_format(new DateTime($signup['end_datetime']), "h:i A") . '</span>';

		$customColorClass = " text-danger ";
		if ($signup['current_signups'] < $signup['opening_max_signups']) {
			$customColorClass = " text-success ";
		}
		$max_signups = $signup['opening_max_signups'];
		if ($max_signups == -1) {
			$max_signups = "*";
		}

		$rendered .= '<span class="opening-space-usage ' . $customColorClass . '"><strong>' . '(' . $signup['current_signups'] . '/' . $max_signups . ')</strong></span>';
		$rendered .= '<span class="">';
		if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
			$rendered .= "<a href=\"#\" id=\"btn-remove-signup-mine-id-" . $signup['signup_id'] . "\"  class=\"btn btn-xs btn-danger sus-delete-signup-from-mine\" data-bb=\"alert_callback\" data-for-signup-id=\"" . $signup['signup_id'] . "\" data-for-sheet-name=\"" . $signup['sheet_name'] . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
			// scrap tidbit: data-for-opening-id=\"" . $signup['opening_id'] . "\"
		}
		$rendered .= '</span>';

		$rendered .= "<br /><ul class=\"unstyled small\"><li>";
		$rendered .= "<strong>Sheet:</strong> <a href=\"#\" class=\"\" title=\"View this sheet\">" . $signup['sheet_name'] . "</a><br />";
		if ($signup['opening_name'] != '') {
			$rendered .= "<strong>Opening:</strong> " . $signup['opening_name'] . "<br />";
		}
		if ($signup['opening_description'] != '') {
			$rendered .= "<strong>Description:</strong> " . $signup['opening_description'] . "<br />";
		}
		if ($signup['opening_location'] != '') {
			$rendered .= "<strong>Location:</strong> " . $signup['opening_location'] . "<br />";
		}
		$rendered .= '</li></ul></div>';

		return $rendered;
	}


	if ($IS_AUTHENTICATED) {
		echo "<div id=\"parent_container\">"; // begin: div#parent_container
		echo "<h3>" . $pageTitle . "</h3>";


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

		?>
		<div class="container">
			<div class="row">
				<!-- Begin: My Signups -->
				<div class="col-sm-5">
					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<ul id="boxMySignupsHeader" class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active">
									<strong>I've Signed up for...</strong>
								</li>
							</ul>
							<div id="boxMySignupsContent" class="tab-content">
								<!-- Begin: My Signups (Content) -->
								<div role="tabpanel" id="tabSignupsMine" class="tab-pane fade active in" aria-labelledby="tabSignupsMine">
									<a href="#" id="scroll-to-todayish-signups-mine" type="button" class="btn btn-success btn-small" title="scroll to current date">current
										date</a>

									<?php
										// PANEL 1: "My Signups..."
										// TODO - if empty array, err msg: "Fatal error: an invalid value was given in the search hash in C:\xampp\htdocs\GITHUB\lti\lti-signup-sheets\classes\db_linked.class.php on line 299"
										if (count($USER->signups_all) == 0) {
											echo "<div class='bg-warning'>You have not yet signed up for any sheet openings.<br />To sign-up, click on &quot;My Available Openings&quot; (above).</div>";
										}
										else {
											echo '<div id="signups-list-container-mine">' . "\n";

											//$s->cacheOpenings();
											$lastOpeningDate = '';
											$daysOpenings    = [];
											$todayYmd        = explode(' ', util_currentDateTimeString())[0];
											// foreach ($s->openings as $opening) {
											foreach ($USER->signups_all as $signup) {
												$curOpeningDate = explode(' ', $signup['begin_datetime'])[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render signups for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo renderAsHtmlShortForSignupsMine($op);
													}

													if ($lastOpeningDate) {
														echo '</div>' . "\n";
													}
													$relative_time_class = 'in-the-past';
													//util_prePrintR('$curOpeningDate : $todayYmd = '.$curOpeningDate .':'. $todayYmd);
													if ($curOpeningDate == $todayYmd) {
														$relative_time_class = 'in-the-present';
													}
													elseif ($curOpeningDate > $todayYmd) {
														$relative_time_class = 'in-the-future';
													}
													echo '<div class="opening-list-for-date ' . $relative_time_class . '" data-for-date="' . $curOpeningDate . '"><h4>' . date_format(new DateTime($signup['begin_datetime']), "m/d/Y") . '</h4>';
													$daysOpenings = [];
												}
												array_unshift($daysOpenings, $signup);
												$lastOpeningDate = $curOpeningDate;
											}
											// render openings for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
											foreach ($daysOpenings as $op) {
												echo renderAsHtmlShortForSignupsMine($op);
											}
											echo '</div>' . "\n"; // end: .opening-list-for-date
											echo '</div>' . "\n"; // end: #signups-list-container-mine
										}
									?>
								</div>
								<!-- End: My Signups (Content) -->
							</div>
						</div>
					</div>
				</div>
				<!-- End: My Signups -->
				<div class="col-sm-1">&nbsp;</div>
				<!-- Begin: Signups on my Sheets -->
				<div class="col-sm-6">
					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set2">
							<ul id="boxSignupsOnMySheetsHeader" class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active">
									<strong>Sign-ups on my Sheets...</strong>
								</li>
							</ul>
							<div id="boxSignupsOnMySheetsContent" class="tab-content">
								<!--Begin: Signups on my Sheets (Content) -->
								<div role="tabpanel" id="tabSignupsOthers" class="tab-pane fade active in" aria-labelledby="tabSignupsOthers">
									<a href="#" id="scroll-to-todayish-signups-others" type="button" class="btn btn-success btn-small" title="scroll to current date">current
										date</a>

									<?php
										// PANEL 2: "Sign-ups on my Sheets..."
										// TODO - if empty array, err msg: "Fatal error: an invalid value was given in the search hash in C:\xampp\htdocs\GITHUB\lti\lti-signup-sheets\classes\db_linked.class.php on line 299"
										if (count($USER->signups_on_my_sheets) == 0) {
											echo "<div class='bg-warning'>No one has signed up on your sheets.</div>";
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
									?>

								</div>
								<!--End: Signups on my Sheets (Content) -->
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Signups on my Sheets -->
		</div> <!-- end: div.container -->

		<?php
		echo "</div>"; // end: div#parent_container

	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/signups_all.js"></script>
