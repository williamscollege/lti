<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_signups'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		/* --------------------------- */
		/* START OLD CODE */
		/* --------------------------- */

		if (!verify_in_signup_sheets()) {
			die("not in signup_sheets");
		}

		// DESIGN NOTE: the page content section is organized as two tabbed areas:
		//    1. all the signups the user has made, as a list, ordered from oldest to newest, one's before the present are hidden
		//    2. all signups by others on sheets the user owns or admins
		//include_once 'cal_lib.php';

		$DEBUG = 1;

		$now = ymd();
		?>
		<style type="text/css">
			@import url("tab.css");
		</style>
		<script type="text/javascript" src="js/tabber.js"></script>

		<div id="sus_user_notify"></div>

		<div id="my_signups_page">
			<div class="tabber">
				<div class="tabbertab" title="I've Signed Up For">
					<?php
						$my_signups = getSignupsBySignee($USER->id, $now);

						echo "<div id=\"toggle_my_past_display\">Show Past Signups</div>\n";

						echo "<div id=\"my_signups\">\n";

						echo "<div class=\"my_in_past\">\n";
						echo "fetching past signups...";
						echo "</div>\n"; // end my_in_past

						if (!$my_signups) {
							echo "You have no signups. Check Available Openings to see the sheets on which you can sign up.";
						}
						else {
							generateMySignupsList($my_signups);
						}
						echo "</div>\n"; // end my_signups
					?>
				</div>
				<div class="tabbertab" title="Signups On My Sheets">
					<?php
						$signups_for_me = getSignupsForSheetsOf($USER->id, $USER->username, $now);

						echo "<div id=\"toggle_for_me_past_display\">Show Past Signups</div>\n";

						echo "<div id=\"signups_for_me\">\n";

						echo "<div class=\"for_me_in_past\">\n";
						echo "fetching past signups...";
						echo "</div>\n"; // end for_me_in_past

						if (!$signups_for_me) {
							echo "There are no signups on sheets owned or administrated by you.";
						}
						else {
							generateSignupsForMeList($signups_for_me);
						}
						echo "</div>\n"; // end my_signups
					?>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		$(document).ready(function () {
			var toggle_text_my_future = "Hide Past Signups";
			var toggle_text_my_cur = "Show Past Signups";
			var toggle_text_for_me_future = "Hide Past Signups";
			var toggle_text_for_me_cur = "Show Past Signups";
			var toggle_text_holding = "";

			var my_past_fetch_state = "";
			var for_me_past_fetch_state = "";

			$("#toggle_my_past_display").click(function (evt) {
				// toggling is causing problems, switching to explicit show and hide
				toggle_text_holding = toggle_text_my_cur;
				toggle_text_my_cur = toggle_text_my_future;
				toggle_text_my_future = toggle_text_holding;
				$("#toggle_my_past_display").html(toggle_text_my_cur);

				if (toggle_text_my_cur == "Hide Past Signups") {
					$(".my_in_past").show();
				}
				else {
					$(".my_in_past").hide();
				}

				// if shown and data not yet fetched, go get it
				if ((toggle_text_my_cur == "Hide Past Signups")
					&& ( my_past_fetch_state == "")) {
					$.ajax({
						url: 'fetch_past_signups_ajax.php',
						//url: 'blahblah.php',
						cache: false,
						data: {
							contextid: <?php echo $context->id;?>,
							action: "fetchmypast",
							actionsource: "my_signups"
						},
						error: function (theRequest, textStatus, errorThrown) {
							notifyUser("FETCH FAILED!<br/>error connecting to the server", false);
						},
						success: function (data, textStatus) {
							if (data.match(/^SUCCESS/)) {
								$(".my_in_past").html(data.substring(7));
								$(".my_in_past").show();
								my_past_fetch_state = "fetched";


								$("#my_signups .my_in_past .sheet_name").hover(
									function () {
										try {
											var d_id = "#sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
											$(d_id).css("top", $(this).position().top + $(this).height());
											$(d_id).css("left", $(this).position().left + 48);
										}
										catch (err) {
											alert("failure:" + err);
										}
									},
									function () {
										try {
											var d_id = "#sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
											$(d_id).css("left", "-999px");
										}
										catch (err) {
											$(".sheet_details").css("left", "-999px");
										}
									}
								);

							}
							else {
								notifyUser("FETCH FAILED!<br/>" + data, false);
							}
						}
					});
				}

			});

			$("#toggle_for_me_past_display").click(function (evt) {
				//$(".for_me_in_past").toggle();
				toggle_text_holding = toggle_text_for_me_cur;
				toggle_text_for_me_cur = toggle_text_for_me_future;
				toggle_text_for_me_future = toggle_text_holding;
				$("#toggle_for_me_past_display").html(toggle_text_for_me_cur);

				if (toggle_text_for_me_cur == "Hide Past Signups") {
					$(".for_me_in_past").show();
				}
				else {
					$(".for_me_in_past").hide();
				}

				// if shown and data not yet fetched, go get it
				if ((toggle_text_for_me_cur == "Hide Past Signups")
					&& ( for_me_past_fetch_state == "")) {
					$.ajax({
						url: 'fetch_past_signups_ajax.php',
						//url: 'blahblah.php',
						cache: false,
						data: {
							contextid: <?php echo $context->id;?>,
							action: "fetchformepast",
							actionsource: "my_signups"
						},
						error: function (theRequest, textStatus, errorThrown) {
							notifyUser("FETCH FAILED!<br/>error connecting to the server", false);
						},
						success: function (data, textStatus) {
							if (data.match(/^SUCCESS/)) {
								$(".for_me_in_past").html(data.substring(7));
								$(".for_me_in_past").show();
								for_me_past_fetch_state = "fetched";

								$("#signups_for_me .for_me_in_past .sheet_name").hover(
									function () {
										try {
											var d_id = "#for_sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
											$(d_id).css("top", $(this).position().top + $(this).height());
											$(d_id).css("left", $(this).position().left + 48);
										}
										catch (err) {
											alert("failure:" + err);
										}
									},
									function () {
										try {
											var d_id = "#for_sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
											$(d_id).css("left", "-999px");
										}
										catch (err) {
											$(".sheet_details").css("left", "-999px");
										}
									}
								);

								$(".for_me_in_past .sus_user_fullname").hover(
									function () {
										try {
											var d_id = ".signup_detail_info_" + $(this).parent().attr("for_signup");
											$(d_id).css("top", $(this).position().top + $(this).height());
											$(d_id).css("left", $(this).position().left + 48);
										}
										catch (err) {
											alert("failure:" + err);
										}
									},
									function () {
										try {
											var d_id = ".signup_detail_info_" + $(this).parent().attr("for_signup");
											$(d_id).css("left", "-999px");
										}
										catch (err) {
											$(".sheet_details").css("left", "-999px");
										}
									}
								);


							}
							else {
								notifyUser("FETCH FAILED!<br/>" + data, false);
							}
						}
					});
				}

			});

			$("#my_signups .sheet_name").hover(
				function () {
					try {
						var d_id = "#sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
						//alert("d_id is " + d_id);
						//$(d_id).css("top",$(this).position().top);
						//$(d_id).css("left",$(this).position().left + $(this).width() + 4);
						$(d_id).css("top", $(this).position().top + $(this).height());
						$(d_id).css("left", $(this).position().left + 48);
					}
					catch (err) {
						alert("failure:" + err);
					}
				},
				function () {
					try {
						var d_id = "#sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
						//alert("oon_id is " + oon_id);
						$(d_id).css("left", "-999px");
					}
					catch (err) {
						$(".sheet_details").css("left", "-999px");
					}
				}
			);

			$("#signups_for_me .sheet_name").hover(
				function () {
					try {
						var d_id = "#for_sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
						$(d_id).css("top", $(this).position().top + $(this).height());
						$(d_id).css("left", $(this).position().left + 48);
					}
					catch (err) {
						alert("failure:" + err);
					}
				},
				function () {
					try {
						var d_id = "#for_sheet_details_for_" + $(this).attr("for_sheet") + "_" + $(this).attr("for_opening");
						$(d_id).css("left", "-999px");
					}
					catch (err) {
						$(".sheet_details").css("left", "-999px");
					}
				}
			);


			$(".sus_user_fullname").hover(
				function () {
					try {
						var d_id = ".signup_detail_info_" + $(this).parent().attr("for_signup");
						//alert("d_id is " + d_id);
						//$(d_id).css("top",$(this).position().top);
						//$(d_id).css("left",$(this).position().left + $(this).width() + 4);
						$(d_id).css("top", $(this).position().top + $(this).height());
						$(d_id).css("left", $(this).position().left + 48);
						//$(this).css("color","#C0652C");
						//$(this).css("border-color","#C0652C");
					}
					catch (err) {
						alert("failure:" + err);
					}
				},
				function () {
					try {
						var d_id = ".signup_detail_info_" + $(this).parent().attr("for_signup");
						//alert("oon_id is " + oon_id);
						$(d_id).css("left", "-999px");
						//$(this).css("color","#000");
						//$(this).css("border-color","#000");
					}
					catch (err) {
						$(".sheet_details").css("left", "-999px");
					}
				}
			);


			$(".remove_signup_link").click(function (evt) {
				$("#sus_user_notify").stop(true, true);
				//alert("evt target is "+evt.target); // DEBUGGING
				//$(evt.target).parent().css("border","1px dashed blue"); // DEBUGING
				var sh = $(evt.target).attr("for_sheet");
				var opg = $(evt.target).attr("for_opening");
				var su = $(evt.target).attr("for_signup");
				$.ajax({
					url: 'handle_signups_ajax.php',
					//url: 'blahblah.php',
					cache: false,
					data: {
						contextid: <?php echo $context->id;?>,
						sheet: sh,
						opening: opg,
						signup: su,
						action: "removesignup",
						actionsource: "my_signups"
					},
					error: function (theRequest, textStatus, errorThrown) {
						notifyUser("SAVE FAILED!<br/>error connecting to the server", false);
					},
					success: function (data, textStatus) {
						if (data.match(/^SUCCESS/)) {
							notifyUser("Signup removed");
							// TODO: update info on the page to reflect that signup
							$(".signup_" + su).hide();
							//$(".opening_"+opg).replaceWith(data.substring(7));
							//$(".opening_"+opg+" .opening_signup_link").click(function(evt)
							//{
							//  handleSignupClick(evt);
							//});
							//$(".opening_"+opg+" .remove_signup_link").click(function(evt)
							//{
							//  handleRemoveSignupClick(evt);
							//});
						}
						else {
							notifyUser("REMOVE ABORTED!<br/>" + data, false);
						}
					}
				});
				// consume the event here
				evt.stopPropagation();
			});


			$("#sus_user_notify").css("left", 700);
			$("#sus_user_notify").css("top", 100);

		});
		</script>
		<?php
		/* --------------------------- */
		/* END OLD CODE */
		/* --------------------------- */
	}

	require_once('../foot.php');
