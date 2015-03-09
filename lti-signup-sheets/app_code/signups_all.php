<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('signups_all'));
	require_once('../app_head.php');


	function renderAsHtmlForSignupsMine($signup) {
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
			$rendered .= "<a href=\"#\" id=\"btn-remove-my-signup-id-" . $signup['signup_id'] . "\"  class=\"btn btn-xs btn-danger sus-delete-my-signup\" data-bb=\"alert_callback\" data-for-signup-id=\"" . $signup['signup_id'] . "\" data-for-sheet-name=\"" . $signup['sheet_name'] . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
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
		$rendered .= '</li>';
		$rendered .= '</ul>';
		$rendered .= '</div>';

		return $rendered;
	}

	function renderAsHtmlForSignupsOthers($signup) {
		$rendered = '<div class="list-opening-signups list-opening-id-' . $signup['opening_id'] . '">';
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
		$rendered .= '</li>';
		// begin: loop through signed up users
		//		util_prePrintR($signup['array_signups']);
		if ($signup['array_signups']) {
			$rendered .= '<li>';
			$rendered .= renderAsHtmlListSignups($signup);
			$rendered .= '</li>';
		}
		// end: loop through signed up users
		$rendered .= '</ul>';
		$rendered .= '</div>';

		return $rendered;
	}

	function renderAsHtmlListSignups($signup) {
		//util_prePrintR($signup);
		$signedupUsers = $signup['array_signups'];
		$rendered      = "<ul class=\"wms-signups\">";
		foreach ($signedupUsers as $u) {
			$rendered .= "<li id=\"list-others-signup-id-\"" . $u['signup_id'] . "\" class='list-signups'>" . $u['full_name'];
			// display date signup created
			//					foreach ($this->signups as $u) {
			//						if ($u->signup_user_id == $u->user_id) {
			$rendered .= ' <span class="small">(' . $u['username'] . ', ' . util_datetimeFormatted($u['signup_created_at']) . ')</span> ';
			$rendered .= '<span class="">';
			if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
				$rendered .= "<a href=\"#\" id=\"btn-remove-others-signup-id-" . $u['signup_id'] . "\"  class=\"btn btn-xs btn-danger sus-delete-others-signup\" data-bb=\"alert_callback\" data-for-signup-id=\"" . $u['signup_id'] . "\" data-for-signup-name=\"" . $u['full_name'] . "\" data-for-sheet-name=\"" . $signup['sheet_name'] . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
				// scrap tidbit: data-for-opening-id=\"" . $signup['opening_id'] . "\"
			}
			$rendered .= '</span>';
			//						}
			//					}
			$rendered .= "</li>";
		}
		$rendered .= "</ul>";
		return $rendered;

	}


	if ($IS_AUTHENTICATED) {
		echo "<div id=\"parent_container\">"; // begin: div#parent_container
		echo "<h3>" . $pageTitle . "</h3>";
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
									<a href="#" id="scroll-to-todayish-my-signups" type="button" class="btn btn-success btn-small" title="scroll to current date">current
										date</a>

									<?php
										$USER->cacheMySignups();
										// util_prePrintR($USER->signups_all);

										// PANEL 1: "My Signups..."
										if (count($USER->signups_all) == 0) {
											echo "<div class='bg-warning'>You have not yet signed up for any sheet openings.<br />To sign-up, click on &quot;My Available Openings&quot; (above).</div>";
										}
										else {
											echo '<div id="my-signups-list-container">' . "\n";

											$lastOpeningDate = '';
											$daysOpenings    = [];
											$todayYmd        = explode(' ', util_currentDateTimeString())[0];
											// foreach ($s->openings as $opening) {
											foreach ($USER->signups_all as $signup) {
												$curOpeningDate = explode(' ', $signup['begin_datetime'])[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render signups for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo renderAsHtmlForSignupsMine($op);
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
												echo renderAsHtmlForSignupsMine($op);
											}
											echo '</div>' . "\n"; // end: .opening-list-for-date
											echo '</div>' . "\n"; // end: #my-signups-list-container
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
									<a href="#" id="scroll-to-todayish-others-signups" type="button" class="btn btn-success btn-small" title="scroll to current date">current
										date</a>

									<?php
										$USER->cacheSignupsOnMySheets();
										// util_prePrintR($USER->signups_on_my_sheets);

										// PANEL 2: "Sign-ups on my Sheets..."
										if (count($USER->signups_on_my_sheets) == 0) {
											echo "<div class='bg-warning'>No one has signed up on your sheets.</div>";
										}
										else {
											echo '<div id="others-signups-list-container">' . "\n";

											$lastOpeningDate = '';
											$daysOpenings    = [];
											$todayYmd        = explode(' ', util_currentDateTimeString())[0];
											// foreach ($s->openings as $opening) {
											foreach ($USER->signups_on_my_sheets as $signup) {
												$curOpeningDate = explode(' ', $signup['begin_datetime'])[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render signups for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo renderAsHtmlForSignupsOthers($op);
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
												util_prePrintR($op);
												echo renderAsHtmlForSignupsOthers($op);
											}
											echo '</div>' . "\n"; // end: .opening-list-for-date
											echo '</div>' . "\n"; // end: #others-signups-list-container
										}

										//											foreach ($USER->signups_on_my_sheets as $scheduled) {
										//												// date
										//												echo "<div id=\"group-signups-for-opening-id-" . $scheduled['opening_id'] . "\">";
										//												echo "<strong>" . date('F d, Y', strtotime($scheduled['begin_datetime'])) . "</strong>";
										//												echo "<br />";
										//												// time opening
										//												echo "&nbsp;&nbsp;&nbsp;&nbsp;" . date('g:i:A', strtotime($scheduled['begin_datetime'])) . " - " . date('g:i:A', strtotime($scheduled['end_datetime']));
										//												// display x of y total signups for this opening
										//												echo "&nbsp;(" . $scheduled['current_signups'] . "/" . $scheduled['opening_max_signups'] . ")";
										//												// link to edit
										//												// TODO - add functionality to link click through
										//												echo "&nbsp;<a href=\"edit_opening.php?opening_id=" . $scheduled['opening_id'] . "\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>Description:</strong> " . $scheduled['opening_description'] . "<br /><strong>Where:</strong> " . $scheduled['opening_location'] . "\">" . $scheduled['opening_name'] . "</a>";
										//												// list signups
										//												echo "<ul class=\"unstyled\">";
										//												foreach ($scheduled['array_signups'] as $person) {
										//													//util_prePrintR($person);
										//													echo "<li>";
										//													// dkc says: could tighten-up UI, if needed: by btn-link instead of btn btn-xs. can color the remove icon red, manually/automatically?
										//													echo "<a href=\"#\" id=\"btn-remove-signup-id-" . $person['signup_id'] . "\"  class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $person['opening_id'] . "\" data-for-signup-name=\"" . $person['full_name'] . "\" data-for-signup-id=\"" . $person['signup_id'] . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
										//													echo "<a href=\"#\" tabindex=\"0\" class=\"btn btn-link\" role=\"button\" data-toggle=\"popover\" data-placement=\"right\" data-trigger=\"hover\" data-html=\"true\" data-content=\"<strong>User:</strong>&nbsp; " . $person['username'] . "<br /><strong>Email:</strong> " . $person['email'] . "<br /><strong>Signed up:</strong> " . date('n/j/Y g:i:A', strtotime($person['signup_created_at'])) . "\">" . $person['full_name'] . "</a>";
										//													echo "</li>";
										//												}
										//												echo "</ul>";
										//												echo "</div>";
										//											}
										//										}
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
