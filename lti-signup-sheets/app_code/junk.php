<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_sheets'));
	require_once('../app_head.php');

	$s = SUS_Sheet::getOneFromDb(['sheet_id' => 601], $DB);


?>

<a href="#" class="addOpeningLink" data-toggle="modal" data-target="#modal-edit-opening" title="Create openings"><i class="glyphicon glyphicon-plus"></i></a>
<div id="list-opening-id-856" class="list-opening" data-location="Sarengetti" data-end_datetime="2015-02-24 14:30:00" data-begin_datetime="2015-02-24 08:15:00" data-admin_comment="admin here" data-max_signups="5" data-description="and stuff" data-name="Rhinos Like to Roar" data-opening_group_id="856" data-sheet_id="601" data-flag_delete="0" data-updated_at="2015-02-11 15:15:27" data-created_at="2015-02-11 15:15:27" data-opening_id="856">
	<a class="sus-edit-opening" data-toggle="modal" data-target="#modal-edit-opening" title="Edit opening" href="#"><i class="glyphicon glyphicon-wrench"></i></a>
</div>


<!-- Bootstrap Modal: Calendar Edit Opening -->
<form action="../app_code/opening_proc.php" id="frmEditOpening" name="frmEditOpening" class="form-horizontal" role="form" method="post">
	<input type="hidden" id="openingID" name="openingID" value="" />

	<div id="modal-edit-opening" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="openingLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header bg-info">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
					</button>
					<h4 id="openingLabel" class="modal-title">Edit opening</h4>
				</div>
				<div class="modal-body">
					<!-- total col-sm per row should = 12 -->
					<div class="container">
						<!-- START COLUMN ONE -->
						<div class="row col-sm-8 small">
							<div class="col-sm-9">
								<div class="form-group form-group-sm">
									<label for="openingName" class="col-sm-3 control-label">Name</label>

									<div class="col-sm-9">
										<input type="text" id="openingName" name="openingName" class="form-control" placeholder="Opening name (optional)" value="" />
									</div>
								</div>
								<div class="form-group form-group-sm">
									<label for="openingDescription" class="col-sm-3 control-label">Description</label>

									<div class="col-sm-9">
										<textarea id="openingDescription" name="openingDescription" class="form-control" placeholder="Opening description (optional)" rows="1"></textarea>
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
										<input type="text" id="openingLocation" name="openingLocation" class="form-control" placeholder="Opening location (optional)" value="" />
									</div>
								</div>
								<div class="form-group form-group-sm">
									<label for="openingDateStart" class="col-sm-3 control-label">On</label>

									<div class="col-sm-9">
										<input type="text" id="openingDateStart" name="openingDateStart" class="form-inline" placeholder="mm/dd/yyyy" maxlength="10" value="" />
									</div>
								</div>

								<!-- end optional_opening_fields -->
								<div class="form-group form-group-sm">
									<label for="openingBeginTimeHour" class="col-sm-3 control-label">From</label>

									<div class="col-sm-9">
										<!-- START 'HOURS' -->
										<select id="openingBeginTimeHour" name="openingBeginTimeHour">
											<option value="1">1</option>
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
											<option value="0">00</option>
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
											<option value="pm">pm</option>
										</select>
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
											<select id="openingEndTimeMinute" name="openingEndTimeMinute">
												<option value="0">00</option>
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
												<option value="pm">pm</option>
											</select>
										</div>
									</div>
								</div>

								<div class="form-group form-group-sm">
									<label for="openingNumSignupsPerOpening" class="col-sm-3 control-label">&nbsp;Maximum Signups</label>

									<div class="col-sm-9">
										<select id="openingNumSignupsPerOpening" name="openingNumSignupsPerOpening">
											<option value="-1">unlimited</option>
											<option value="1">1</option>
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
									<label for="btnOpeningSubmit" class="col-sm-3 control-label">&nbsp;</label>

									<div class="col-sm-9">
										<button type="submit" id="btnOpeningSubmit" class="btn btn-success btn" data-loading-text="Saving...">Save</button>
										<button type="reset" id="btnOpeningCancel" class="btn btn-default btn-link btn-cancel" data-dismiss="modal">Cancel
										</button>
									</div>
								</div>
							</div>
						</div>

						<!-- START COLUMN TWO -->
						<div class="row col-sm-4">
							col 2
						</div>
					</div>
				</div>

				<div class="modal-footer">
					&nbsp;some footer here
				</div>
			</div>
		</div>
	</div>
</form>
<!-- /Bootstrap Modal: Calendar Edit Opening -->

<script type="text/javascript" src="../js/calendar_opening.js"></script>