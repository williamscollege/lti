<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = ucfirst(util_lang('signups_all'));
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {

		// TODO: render fxns okay here? not sure how to incorporate these into a class?
		function _renderHtml_BEGIN($signup) {
			$rendered = '<div class="list-openings list-opening-id-' . $signup['opening_id'] . '">';
			$rendered .= '<span class="opening-time-range">' . date_format(new DateTime($signup['begin_datetime']), "h:i A") . ' - ' . date_format(new DateTime($signup['end_datetime']), "h:i A") . '</span>';

			$customColorClass = "text-danger";
			if ($signup['current_signups'] < $signup['opening_max_signups'] || $signup['opening_max_signups'] == -1) {
				$customColorClass = "text-success";
			}

			$max_signups = $signup['opening_max_signups'];
			if ($max_signups == -1) {
				$max_signups = "*";
			}
			$rendered .= '<span class="opening-space-usage ' . $customColorClass . '"><strong>' . '(' . $signup['current_signups'] . '/' . $max_signups . ')</strong></span><br />';

			return $rendered;
		}

		function _renderList_MYSELF($signup) {
			global $USER;
			$rendered = "<div class=\"small wms_indent\"><strong>Sheet:</strong> " . $signup['sheet_name'] . "<br /></div>";
			$rendered .= "<ul class=\"unstyled small\"><li class=\"toggle_opening_details\">";
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
			$rendered .= '<li>';

			$rendered .= "<ul class=\"wms-signups\">";

			$signedupUsers = $signup['array_signups'];
			//util_prePrintR($signedupUsers);
			// begin: loop through signed up users
			foreach ($signedupUsers as $u) {
				if (!$signup['sheet_flag_private_signups']) {
					// public: Users can see everyone who signed up
					$rendered .= "<li class=\"list-signups list-signup-id-" . $u['signup_id'] . "\">" . $u['full_name'];
					$rendered .= "<span class=\"\">";
					if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
						// TODO - add functionality to link click through
						// only allow self to cancel signup
						if ($u['username'] == $USER->username) {
							$rendered .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $signup['opening_id'] . "\" data-for-signup-id=\"" . $u['signup_id'] . "\" data-for-signup-name=\"" . $u['full_name'] . "\" data-for-sheet-name=\"" . $signup['sheet_name'] . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
						}
					}
					$rendered .= "</span>";
					$rendered .= "</li>";
				}
				elseif ($signup['sheet_flag_private_signups'] && $u['username'] == $USER->username) {
					// private: Users can only see their own signup
					$rendered .= "<li class=\"list-signups list-signup-id-" . $u['signup_id'] . "\">" . $u['full_name'];
					$rendered .= "<span class=\"\">";
					if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
						// TODO - add functionality to link click through
						$rendered .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $signup['opening_id'] . "\" data-for-signup-id=\"" . $u['signup_id'] . "\" data-for-signup-name=\"" . $u['full_name'] . "\" data-for-sheet-name=\"" . $signup['sheet_name'] . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
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
			$rendered = "<div class=\"small wms_indent\"><strong>Sheet:</strong> <a href=\"" . APP_ROOT_PATH . "/app_code/sheets_edit_one.php?sheet=" . $signup['sheet_id'] . "\" class=\"\" title=\"Edit sheet\">" . $signup['sheet_name'] . "</a><br /></div>";
			$rendered .= "<ul class=\"unstyled small\"><li class=\"toggle_opening_details\">";
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

			if ($signup['array_signups']) {
				//util_prePrintR($signup);
				$rendered .= '<li>';
				$rendered .= "<ul class=\"wms-signups\">";
				$signedupUsers = $signup['array_signups'];
				// begin: loop through signed up users
				foreach ($signedupUsers as $u) {
					$rendered .= "<li class=\"list-signups list-signup-id-" . $u['signup_id'] . "\">" . $u['full_name'];
					$rendered .= " <span class=\"toggle_opening_details small\">(" . $u['username'] . ", " . util_datetimeFormatted($u['signup_created_at']) . ")</span> ";
					$rendered .= "<span class=\"\">";
					if (date_format(new DateTime($signup['begin_datetime']), "Y-m-d H:i") > util_currentDateTimeString()) {
						// TODO - add functionality to link click through
						$rendered .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-signup\" data-bb=\"alert_callback\" data-for-opening-id=\"" . $signup['opening_id'] . "\" data-for-signup-id=\"" . $u['signup_id'] . "\" data-for-signup-name=\"" . $u['full_name'] . "\" data-for-sheet-name=\"" . $signup['sheet_name'] . "\" title=\"Cancel signup\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
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
						<div class="tab-container" role="tabpanel" data-example-id="set1">
							<ul id="boxMySignupsHeader" class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active">
									<strong>My Signups</strong>
								</li>
							</ul>
							<div id="boxMySignupsContent" class="tab-content">
								<!-- Begin: My Signups (Content) -->
								<div id="buttons_my_signups">
									<a href="#" id="scroll-to-todayish-my-signups" type="button" class="btn btn-success btn-xs" title="go to next">go to
										next</a>&nbsp;&nbsp;&nbsp;
									<!-- TOGGLE LINK: Show optional details -->
									<a href="#" id="link_for_opening_details_1" type="button" class="btn btn-info btn-xs" title="toggle details">show
										details</a>
								</div>
								<div role="tabpanel" id="tabMySignups" class="tab-pane fade active in" aria-labelledby="tabMySignups">
									<?php
										$USER->cacheMySignups();
										// util_prePrintR($USER->signups_all);

										// PANEL 1: "My Signups..."
										if (count($USER->signups_all) == 0) {
											echo "<div class='bg-warning'>You have not yet signed up for any sheet openings.<br />To sign-up, click on &quot;My Available Openings&quot; (above).</div>";
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
				<div class="col-sm-6">
					<div class="row">
						<div class="tab-container" role="tabpanel" data-example-id="set2">
							<ul id="boxSignupsOnMySheetsHeader" class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active">
									<strong>On My Sheets</strong>
								</li>
							</ul>
							<div id="boxSignupsOnMySheetsContent" class="tab-content">
								<!-- Begin: Signups on my Sheets (Content) -->
								<div id="buttons_on_my_sheets">
									<a href="#" id="scroll-to-todayish-others-signups" type="button" class="btn btn-success btn-xs" title="go to next">go to
										next</a>&nbsp;&nbsp;&nbsp;
									<!-- TOGGLE LINK: Show optional details -->
									<a href="#" id="link_for_opening_details_2" type="button" class="btn btn-info btn-xs" title="toggle details">show
										details</a>
								</div>
								<div role="tabpanel" id="tabOthersSignups" class="tab-pane fade active in" aria-labelledby="tabOthersSignups">
									<?php
										$USER->cacheSignupsOnMySheets();
										// util_prePrintR($USER->signups_on_my_sheets);

										// PANEL 2: "Sign-ups on my Sheets..."
										if (count($USER->signups_on_my_sheets) == 0) {
											echo "<div class='bg-warning'>No one has signed up on your sheets.</div>";
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
