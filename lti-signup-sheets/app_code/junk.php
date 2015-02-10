<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_sheets'));
	require_once('../app_head.php');
?>

<a href="#" class="addOpeningLink" data-toggle="modal" data-target="#modal-create-opening" title="Create openings"><i class="glyphicon glyphicon-plus"></i></a>

<!-- Bootstrap Modal: Calendar Create Opening -->
<form action="../ajax_actions/ajax_actions.php" id="frmOpening" name="frmOpening" class="form-horizontal" role="form" method="post">
	<div id="modal-create-opening" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="openingLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-info">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
					</button>
					<h4 id="openingLabel" class="modal-title">Creating openings on 12/23/2014</h4>
				</div>
				<div class="modal-body">
					<!-- TOGGLE LINK: Show Optional Fields -->
					<a href="#" id="link_show_optional_opening_fields" class="small" title="Show optional fields">Show optional fields</a>

					<div class="optional_opening_fields">
						<!-- TOGGLE LINK: Hide Optional Fields -->
						<a href="#" id="link_hide_optional_opening_fields" class="small" title="Hide optional fields">Hide optional fields</a>

						<div class="form-group form-group-sm">
							<label for="openingName" class="col-sm-3 control-label">Name</label>

							<div class="col-sm-9">
								<input type="text" id="openingName" name="openingName" class="form-control" placeholder="Opening name" value="" />
							</div>
						</div>
						<div class="form-group form-group-sm">
							<label for="openingDescription" class="col-sm-3 control-label">Description</label>

							<div class="col-sm-9">
								<textarea id="openingDescription" name="openingDescription" class="form-control" placeholder="Opening description" rows="1"></textarea>
							</div>
						</div>
						<div class="form-group form-group-sm">
							<label for="openingAdminNotes" class="col-sm-3 control-label">Admin&nbsp;Notes</label>

							<div class="col-sm-9">
								<textarea id="openingAdminNotes" name="openingAdminNotes" class="form-control" placeholder="Only the sheet admin can see these notes" rows="1"></textarea>
							</div>
						</div>
						<div class="form-group form-group-sm">
							<label for="openingLocation" class="col-sm-3 control-label">Location</label>

							<div class="col-sm-9">
								<input type="text" id="openingLocation" name="openingLocation" class="form-control" placeholder="Opening location" value="" />
							</div>
						</div>
					</div>
					<!-- end optional_opening_fields -->
					<div class="form-group form-group-sm">
						<label for="openingBeginTimeHour" class="col-sm-3 control-label">From</label>

						<div class="col-sm-9">
							<!-- START 'HOURS' -->
							<select id="openingBeginTimeHour" name="openingBeginTimeHour">
								<option value="1" selected="selected">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
								<option value="11">11</option>
								<option value="12">12</option>
							</select>:
							<!-- START 'MINUTES' -->
							<select id="openingBeginTimeMinute" name="openingBeginTimeMinute">
								<option value="0" selected="selected">00</option>
								<option value="5">05</option>
								<option value="10">10</option>
								<option value="15">15</option>
								<option value="20">20</option>
								<option value="25">25</option>
								<option value="30">30</option>
								<option value="35">35</option>
								<option value="40">40</option>
								<option value="45">45</option>
								<option value="50">50</option>
								<option value="55">55</option>
							</select>
							<!-- START 'AM/PM' -->
							<select id="openingBeginTime_AMPM" name="openingBeginTime_AMPM">
								<option value="am">am</option>
								<option value="pm" selected="selected">pm</option>
							</select>

							<!-- TOGGLE LINKS: Openings by duration / time-range -->
							<a href="#" id="link_hide_time_range" class="openings_by_time_range small" title="Switch to openings by duration">Switch to
								duration</a>
							<a href="#" id="link_hide_duration" class="openings_by_duration small" title="Switch to openings by time range">Switch to time
								range</a>
						</div>
					</div>
					<div class="form-group form-group-sm">
						<label for="openingEndTimeHour" class="col-sm-3 control-label">To</label>

						<div class="col-sm-9">
							<!-- TOGGLED RESULT: openings by time range -->
							<div class="openings_by_time_range">
								<!-- START 'HOURS' -->
								<select id="openingEndTimeHour" name="openingEndTimeHour">
									<option value="1">1</option>
									<option value="2" selected="selected">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>:
								<!-- START 'MINUTES' -->
								<select id="openingEndTimeMinute" name="openingEndTimeMinute">
									<option value="0" selected="selected">00</option>
									<option value="5">05</option>
									<option value="10">10</option>
									<option value="15">15</option>
									<option value="20">20</option>
									<option value="25">25</option>
									<option value="30">30</option>
									<option value="35">35</option>
									<option value="40">40</option>
									<option value="45">45</option>
									<option value="50">50</option>
									<option value="55">55</option>
								</select>
								<!-- START 'AM/PM' -->
								<select id="openingEndTimeMinute_AMPM" name="openingEndTimeMinute_AMPM">
									<option value="am">am</option>
									<option value="pm" selected="selected">pm</option>
								</select>
							</div>
							<!-- TOGGLED RESULT: openings by duration -->
							<div class="openings_by_duration">
								<select id="openingDurationEachOpening" name="openingDurationEachOpening">
									<option value="5" selected="selected">5</option>
									<option value="10">10</option>
									<option value="15">15</option>
									<option value="20">20</option>
									<option value="25">25</option>
									<option value="30">30</option>
									<option value="35">35</option>
									<option value="40">40</option>
									<option value="45">45</option>
									<option value="50">50</option>
									<option value="55">55</option>
									<option value="60">60</option>
									<option value="65">65</option>
									<option value="70">70</option>
									<option value="75">75</option>
									<option value="80">80</option>
									<option value="85">85</option>
									<option value="90">90</option>
								</select> minutes
							</div>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<label for="openingNumOpenings" class="col-sm-3 control-label">#&nbsp;Openings</label>

						<div class="col-sm-9">
							<select id="openingNumOpenings" name="openingNumOpenings">
								<option value="1" selected="selected">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
								<option value="11">11</option>
								<option value="12">12</option>
								<option value="13">13</option>
								<option value="14">14</option>
								<option value="15">15</option>
								<option value="16">16</option>
								<option value="17">17</option>
								<option value="18">18</option>
								<option value="19">19</option>
								<option value="20">20</option>
								<option value="21">21</option>
								<option value="22">22</option>
								<option value="23">23</option>
								<option value="24">24</option>
							</select>
						</div>
					</div>
					<div class="form-group form-group-sm">
						<label for="openingNumSignupsPerOpening" class="col-sm-3 control-label">&nbsp;Signups/Opening</label>

						<div class="col-sm-9">
							<select id="openingNumSignupsPerOpening" name="openingNumSignupsPerOpening">
								<option value="-1">unlimited</option>
								<option value="1" selected="selected">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
								<option value="11">11</option>
								<option value="12">12</option>
								<option value="13">13</option>
								<option value="14">14</option>
								<option value="15">15</option>
								<option value="16">16</option>
								<option value="17">17</option>
								<option value="18">18</option>
								<option value="19">19</option>
								<option value="20">20</option>
								<option value="21">21</option>
								<option value="22">22</option>
								<option value="23">23</option>
								<option value="24">24</option>
								<option value="25">25</option>
								<option value="26">26</option>
								<option value="27">27</option>
								<option value="28">28</option>
								<option value="29">29</option>
								<option value="30">30</option>
							</select>
						</div>
					</div>
					<div class="form-group form-group-sm">
						<label for="openingRepeaterControls" class="col-sm-3 control-label">Repeating?</label>

						<div class="col-sm-9">
							<div id="openingRepeaterControls">

								<div id="chooseRepeatType">
									<div class="radio">
										<label for="radioOpeningRepeatRate1">
											<input id="radioOpeningRepeatRate1" name="openingRepeatRate" value="1" checked="checked" type="radio" /> Only on
											2014-12-23
										</label>
									</div>
									<div class="radio">
										<label for="radioOpeningRepeatRate2">
											<input id="radioOpeningRepeatRate2" name="openingRepeatRate" value="2" type="radio" /> Repeat on days of the week
										</label>
									</div>
									<div class="radio">
										<label for="radioOpeningRepeatRate3">
											<input id="radioOpeningRepeatRate3" name="openingRepeatRate" value="3" type="radio" /> Repeat on days of the month
										</label>
									</div>
								</div>

								<div id="repeatWeekdayChooser">
									<input name="repeat_dow_sun" id="repeat_dow_sun" value="0" type="hidden" />
									<input name="repeat_dow_mon" id="repeat_dow_mon" value="0" type="hidden" />
									<input name="repeat_dow_tue" id="repeat_dow_tue" value="0" type="hidden" />
									<input name="repeat_dow_wed" id="repeat_dow_wed" value="0" type="hidden" />
									<input name="repeat_dow_thu" id="repeat_dow_thu" value="0" type="hidden" />
									<input name="repeat_dow_fri" id="repeat_dow_fri" value="0" type="hidden" />
									<input name="repeat_dow_sat" id="repeat_dow_sat" value="0" type="hidden" />
									<input id="btn_mon" value="MON" class="toggler_dow btn btn-default btn-xs" type="button" />
									<input id="btn_tue" value="TUE" class="toggler_dow btn btn-default btn-xs" type="button" />
									<input id="btn_wed" value="WED" class="toggler_dow btn btn-default btn-xs" type="button" />
									<input id="btn_thu" value="THU" class="toggler_dow btn btn-success btn-xs" type="button" />
									<input id="btn_fri" value="FRI" class="toggler_dow btn btn-default btn-xs" type="button" /><br />
									<input id="btn_sat" value="SAT" class="toggler_dow btn btn-default btn-xs" type="button" />
									<input id="btn_sun" value="SUN" class="toggler_dow btn btn-default btn-xs" type="button" />
								</div>

								<div id="repeatMonthdayChooser">
									<input name="repeat_dom_1" id="repeat_dom_1" value="0" type="hidden" />
									<input id="btn_dom_1" value="1" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_2" id="repeat_dom_2" value="0" type="hidden" />
									<input id="btn_dom_2" value="2" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_3" id="repeat_dom_3" value="0" type="hidden" />
									<input id="btn_dom_3" value="3" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_4" id="repeat_dom_4" value="0" type="hidden" />
									<input id="btn_dom_4" value="4" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_5" id="repeat_dom_5" value="0" type="hidden" />
									<input id="btn_dom_5" value="5" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_6" id="repeat_dom_6" value="0" type="hidden" />
									<input id="btn_dom_6" value="6" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_7" id="repeat_dom_7" value="0" type="hidden" />
									<input id="btn_dom_7" value="7" class="toggler_dom btn btn-default btn-xs" type="button" />
									<br />
									<input name="repeat_dom_8" id="repeat_dom_8" value="0" type="hidden" />
									<input id="btn_dom_8" value="8" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_9" id="repeat_dom_9" value="0" type="hidden" />
									<input id="btn_dom_9" value="9" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_10" id="repeat_dom_10" value="1" type="hidden" />
									<input id="btn_dom_10" value="10" class="toggler_dom btn btn-success btn-xs" type="button" />
									<input name="repeat_dom_11" id="repeat_dom_11" value="1" type="hidden" />
									<input id="btn_dom_11" value="11" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_12" id="repeat_dom_12" value="0" type="hidden" />
									<input id="btn_dom_12" value="12" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_13" id="repeat_dom_13" value="0" type="hidden" />
									<input id="btn_dom_13" value="13" class="toggler_dom btn btn-success btn-xs" type="button" />
									<input name="repeat_dom_14" id="repeat_dom_14" value="0" type="hidden" />
									<input id="btn_dom_14" value="14" class="toggler_dom btn btn-default btn-xs" type="button" />
									<br />
									<input name="repeat_dom_15" id="repeat_dom_15" value="0" type="hidden" />
									<input id="btn_dom_15" value="15" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_16" id="repeat_dom_16" value="0" type="hidden" />
									<input id="btn_dom_16" value="16" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_17" id="repeat_dom_17" value="0" type="hidden" />
									<input id="btn_dom_17" value="17" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_18" id="repeat_dom_18" value="1" type="hidden" />
									<input id="btn_dom_18" value="18" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_19" id="repeat_dom_19" value="0" type="hidden" />
									<input id="btn_dom_19" value="19" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_20" id="repeat_dom_20" value="0" type="hidden" />
									<input id="btn_dom_20" value="20" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_21" id="repeat_dom_21" value="0" type="hidden" />
									<input id="btn_dom_21" value="21" class="toggler_dom btn btn-default btn-xs" type="button" />
									<br />
									<input name="repeat_dom_22" id="repeat_dom_22" value="0" type="hidden" />
									<input id="btn_dom_22" value="22" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_23" id="repeat_dom_23" value="0" type="hidden" />
									<input id="btn_dom_23" value="23" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_24" id="repeat_dom_24" value="0" type="hidden" />
									<input id="btn_dom_24" value="24" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_25" id="repeat_dom_25" value="0" type="hidden" />
									<input id="btn_dom_25" value="25" class="toggler_dom btn btn-success btn-xs" type="button" />
									<input name="repeat_dom_26" id="repeat_dom_26" value="0" type="hidden" />
									<input id="btn_dom_26" value="26" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_27" id="repeat_dom_27" value="0" type="hidden" />
									<input id="btn_dom_27" value="27" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_28" id="repeat_dom_28" value="0" type="hidden" />
									<input id="btn_dom_28" value="28" class="toggler_dom btn btn-default btn-xs" type="button" />
									<br />
									<input name="repeat_dom_29" id="repeat_dom_29" value="0" type="hidden" />
									<input id="btn_dom_29" value="29" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_30" id="repeat_dom_30" value="0" type="hidden" />
									<input id="btn_dom_30" value="30" class="toggler_dom btn btn-default btn-xs" type="button" />
									<input name="repeat_dom_31" id="repeat_dom_31" value="0" type="hidden" />
									<input id="btn_dom_31" value="31" class="toggler_dom btn btn-default btn-xs" type="button" />
								</div>

								<div id="repeatUntilDate">
									<label for="openingUntilDate" style="font-weight: normal; border-radius: 0;">
										<strong>until</strong> <input type="text" id="openingUntilDate" name="openingUntilDate" class="form-inline" placeholder="mm/dd/yyyy" maxlength="10" value="02/03/2015" />
										<!--value="<?php /*echo $s ? date_format(new DateTime($s->date_closes), "m/d/Y") : ''; */?>" />-->
									</label>
								</div>
							</div>
							<!-- end openingRepeaterControls -->
						</div>
					</div>


				</div>
				<div class="modal-footer">
					<button type="submit" id="btnOpeningSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save</button>
					<button type="reset" id="btnOpeningCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
<!-- /Bootstrap Modal: Calendar Create Opening -->

<script type="text/javascript" src="../js/calendar_opening.js"></script>