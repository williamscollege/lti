<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('signups_all'));
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		// TODO: render fxns okay here? not sure how to incorporate these into a class?
		function _renderHtml_BEGIN($signup) {
			$rendered = '<div class="list-openings list-opening-id-' . htmlentities($signup['opening_id'], ENT_QUOTES, 'UTF-8') . '">';
			$rendered .= '<span class="opening-time-range">' . date_format(new DateTime($signup['begin_datetime']), "h:i A") . ' - ' . date_format(new DateTime($signup['end_datetime']), "h:i A") . '</span>';

			$customColorClass = "text-danger";
			if ($signup['current_signups'] < $signup['opening_max_signups'] || $signup['opening_max_signups'] == -1) {
				$customColorClass = "text-success";
			}

			$max_signups = $signup['opening_max_signups'];
			if ($max_signups == -1) {
				$max_signups = "*";
			}
			$rendered .= '<span class="opening-space-usage ' . $customColorClass . '"><strong>' . '(' . htmlentities($signup['current_signups'], ENT_QUOTES, 'UTF-8') . '/' . htmlentities($max_signups, ENT_QUOTES, 'UTF-8') . ')</strong></span><br />';

			return $rendered;
		}

		function _renderList_MYSELF($signup) {
			global $USER;
			$rendered = "<div class=\"small wms_indent\"><strong>Sheet:</strong> " . htmlentities($signup['sheet_name'], ENT_QUOTES, 'UTF-8') . "<br /></div>";
			$rendered .= "<ul class=\"unstyled small\"><li class=\"toggle_opening_details\">";
			if ($signup['opening_name'] != '') {
				$rendered .= "<strong>Opening:</strong> " . htmlentities($signup['opening_name'], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			if ($signup['opening_description'] != '') {
				$rendered .= "<strong>Description:</strong> " . htmlentities($signup['opening_description'], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			if ($signup['opening_location'] != '') {
				$rendered .= "<strong>Location:</strong> " . htmlentities($signup['opening_location'], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			$rendered .= '</li>';
			$rendered .= '<li>';

			$rendered .= "<ul class=\"wms-signups\">";

			$signedupUsers = $signup['array_signups'];
			//util_prePrintR($signedupUsers);
			// begin: loop through signed up users
			foreach ($signedupUsers as $u) {
				if (!$signup['sheet_flag_private_signups']) {
					// public: Users can see everyone who signed up
					$rendered .= "<li class=\"list-signups list-signup-id-" . htmlentities($u['signup_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlentities($u['full_name'], ENT_QUOTES, 'UTF-8');
					$rendered .= "<span class=\"\">&nbsp;";
					if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
						// TODO - add functionality to link click through
						// only allow self to cancel signup
						if ($u['username'] == $USER->username) {
							$rendered .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . htmlentities($signup['opening_id'], ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($u['signup_id'], ENT_QUOTES, 'UTF-8') . "\" data-for-signup-name=\"" . htmlentities($u['full_name'], ENT_QUOTES, 'UTF-8') . "\" data-for-sheet-name=\"" . htmlentities($signup['sheet_name'], ENT_QUOTES, 'UTF-8') . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
						}
					}
					$rendered .= "</span>";
					$rendered .= "</li>";
				}
				elseif ($signup['sheet_flag_private_signups'] && $u['username'] == $USER->username) {
					// private: Users can only see their own signup
					$rendered .= "<li class=\"list-signups list-signup-id-" . htmlentities($u['signup_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlentities($u['full_name'], ENT_QUOTES, 'UTF-8');
					$rendered .= "<span class=\"\">&nbsp;";
					if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
						// TODO - add functionality to link click through
						$rendered .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . htmlentities($signup['opening_id'], ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($u['signup_id'], ENT_QUOTES, 'UTF-8') . "\" data-for-signup-name=\"" . htmlentities($u['full_name'], ENT_QUOTES, 'UTF-8') . "\" data-for-sheet-name=\"" . htmlentities($signup['sheet_name'], ENT_QUOTES, 'UTF-8') . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
					}
					$rendered .= "</span>";
					$rendered .= "</li>";
				}
			}
			// end: loop through signed up users
			$rendered .= "</ul>";
			$rendered .= "</li>";
			$rendered .= "</ul>";
			$rendered .= "</div>";

			return $rendered;
		}

		function _renderList_OTHERS($signup) {
			$rendered = "<div class=\"small wms_indent\"><strong>Sheet:</strong> <a href=\"" . APP_ROOT_PATH . "/app_code/sheets_edit_one.php?sheet=" . htmlentities($signup['sheet_id'], ENT_QUOTES, 'UTF-8') . "\" class=\"\" title=\"Edit sheet\">" . htmlentities($signup['sheet_name'], ENT_QUOTES, 'UTF-8') . "</a><br /></div>";
			$rendered .= "<ul class=\"unstyled small\"><li class=\"toggle_opening_details\">";
			if ($signup['opening_name'] != '') {
				$rendered .= "<strong>Opening:</strong> " . htmlentities($signup['opening_name'], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			if ($signup['opening_description'] != '') {
				$rendered .= "<strong>Description:</strong> " . htmlentities($signup['opening_description'], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			if ($signup['opening_location'] != '') {
				$rendered .= "<strong>Location:</strong> " . htmlentities($signup['opening_location'], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			$rendered .= '</li>';

			if ($signup['array_signups']) {
				//util_prePrintR($signup);
				$rendered .= '<li>';
				$rendered .= "<ul class=\"wms-signups\">";
				$signedupUsers = $signup['array_signups'];
				// begin: loop through signed up users
				foreach ($signedupUsers as $u) {
					$rendered .= "<li class=\"list-signups list-signup-id-" . htmlentities($u['signup_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlentities($u['full_name'], ENT_QUOTES, 'UTF-8');
					$rendered .= "<span class=\"toggle_opening_details small\">&nbsp;(" . htmlentities($u['username'], ENT_QUOTES, 'UTF-8') . ", " . util_datetimeFormatted($u['signup_created_at']) . ")</span>";
					$rendered .= "<span class=\"\">&nbsp;";
					if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
						// TODO - add functionality to link click through
						$rendered .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . htmlentities($signup['opening_id'], ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($u['signup_id'], ENT_QUOTES, 'UTF-8') . "\" data-for-signup-name=\"" . htmlentities($u['full_name'], ENT_QUOTES, 'UTF-8') . "\" data-for-sheet-name=\"" . htmlentities($signup['sheet_name'], ENT_QUOTES, 'UTF-8') . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
					}
					$rendered .= "</span>";
					$rendered .= "</li>";
				}
				// end: loop through signed up users
				$rendered .= "</ul>";
				$rendered .= "</li>";
			}
			$rendered .= "</ul>";
			$rendered .= "</div>";
			return $rendered;
		}

		function renderAsHtmlForMySignups($signup) {
			$rendered = _renderHtml_BEGIN($signup);
			$rendered .= _renderList_MYSELF($signup);
			return $rendered;
		}

		function renderAsHtmlForOthersSignups($signup) {
			$rendered = _renderHtml_BEGIN($signup);
			$rendered .= _renderList_OTHERS($signup);
			return $rendered;
		}


		echo "<div id=\"content_container\">"; // begin: div#content_container
		?>
		<div class="container">
			<div class="row">
				<!-- Begin: My Signups -->
				<div class="col-sm-5">
					<div class="row">
						<div class="tab-container PrintArea wms_print_MySignups" role="tabpanel" data-example-id="set1">
							<ul id="boxMySignupsHeader" class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active">
									<strong>I've Signed Up</strong>
								</li>
							</ul>
							<div id="boxMySignupsContent" class="tab-content">
								<!-- Begin: My Signups (Content) -->
								<div id="buttons_my_signups">
									<!-- PrintArea: Print a specific div -->
									<a href="#" class="wmsPrintArea" data-what-area-to-print="wms_print_MySignups" title="Print only this section"><i class="glyphicon glyphicon-print"></i></a>&nbsp;
									<!-- Button: today -->
									<a href="#" id="scroll-to-todayish-my-signups" type="button" class="btn btn-success btn-xs" title="go to date nearest today">today</a>&nbsp;
									<!-- TOGGLE LINK: Show optional details -->
									<a href="#" id="link_for_opening_details_1" type="button" class="btn btn-info btn-xs" title="toggle details">show details</a>
								</div>
								<div role="tabpanel" id="tabMySignups" class="tab-pane fade active in" aria-labelledby="tabMySignups">
									<?php
										$USER->cacheMySignups();
										// util_prePrintR($USER->signups_all);

										// PANEL 1: "My Signups..."
										if (count($USER->signups_all) == 0) {
											echo "<div class='bg-info'>You have not yet signed up for any sheet openings.<br />To sign-up, click on <strong>&quot;Available Openings&quot;</strong> (above).</div>";
										}
										else {
											echo '<div id="container-my-signups">' . "\n";

											$lastOpeningDate = '';
											$daysOpenings    = [];
											$todayYmd        = explode(' ', util_currentDateTimeString())[0];
											foreach ($USER->signups_all as $signup) {
												$curOpeningDate = explode(' ', $signup['begin_datetime'])[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render signups for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo renderAsHtmlForMySignups($op);
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
												echo renderAsHtmlForMySignups($op);
											}
											echo '</div>' . "\n"; // end: .opening-list-for-date
											echo '</div>' . "\n"; // end: #container-my-signups
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
				<div class="col-sm-5">
					<div class="row">
						<div class="tab-container PrintArea wms_print_OnMySheets" role="tabpanel" data-example-id="set2">
							<ul id="boxSignupsOnMySheetsHeader" class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active">
									<strong>On My Sheets</strong>
								</li>
							</ul>
							<div id="boxSignupsOnMySheetsContent" class="tab-content">
								<!-- Begin: Signups on my Sheets (Content) -->
								<div id="buttons_on_my_sheets">
									<!-- PrintArea: Print a specific div -->
									<a href="#" class="wmsPrintArea" data-what-area-to-print="wms_print_OnMySheets" title="Print only this section"><i class="glyphicon glyphicon-print"></i></a>&nbsp;
									<!-- Button: today -->
									<a href="#" id="scroll-to-todayish-others-signups" type="button" class="btn btn-success btn-xs" title="go to date nearest today">today</a>&nbsp;
									<!-- TOGGLE LINK: Show optional details -->
									<a href="#" id="link_for_opening_details_2" type="button" class="btn btn-info btn-xs" title="toggle details">show details</a>
								</div>
								<div role="tabpanel" id="tabOthersSignups" class="tab-pane fade active in" aria-labelledby="tabOthersSignups">
									<?php
										$USER->cacheSignupsOnMySheets();
										// util_prePrintR($USER->signups_on_my_sheets);

										// PANEL 2: "Sign-ups on my Sheets..."
										if (count($USER->signups_on_my_sheets) == 0) {
											echo "<div class='bg-info'>No one has signed up on your sheets.<br />To see your sheets, click on <strong>&quot;Sheets&quot;</strong> (above).</div>";
										}
										else {
											echo '<div id="container-others-signups">' . "\n";

											$lastOpeningDate = '';
											$daysOpenings    = [];
											$todayYmd        = explode(' ', util_currentDateTimeString())[0];
											foreach ($USER->signups_on_my_sheets as $signup) {
												$curOpeningDate = explode(' ', $signup['begin_datetime'])[0];
												if ($curOpeningDate != $lastOpeningDate) {
													// render signups for the day (these are reverse sorted (i.e ascending) from the larger list through which we're stepping)
													foreach ($daysOpenings as $op) {
														echo renderAsHtmlForOthersSignups($op);
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
												// util_prePrintR($op);
												echo renderAsHtmlForOthersSignups($op);
											}
											echo '</div>' . "\n"; // end: .opening-list-for-date
											echo '</div>' . "\n"; // end: #container-others-signups
										}
									?>

								</div>
								<!-- End: Signups on my Sheets (Content) -->
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Signups on my Sheets -->
		</div> <!-- end: div.container -->

		<?php
		echo "</div>"; // end: div#content_container
	}
	else {
		# redirect to home
		header('Location: ' . APP_ROOT_PATH . '/index.php');
	}

	require_once(dirname(__FILE__) . '/../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/signups_all.js"></script>
