<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = '';
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {
		//	util_prePrintR($_POST); exit;

		// processing for either creating 'NEW' opening(s) or editing an existing opening_id
		if ((isset($_REQUEST["new_OpeningID"])) && ($_REQUEST["new_OpeningID"] == "NEW")) {
			// Create New Opening
			$openingSheetID     = isset($_REQUEST["new_SheetID"]) ? $_REQUEST["new_SheetID"] : 0;
			$openingID          = isset($_REQUEST["new_OpeningID"]) ? $_REQUEST["new_OpeningID"] : 0;
			$openingDateBegin   = isset($_REQUEST["new_OpeningDateBegin"]) ? $_REQUEST["new_OpeningDateBegin"] : 0; // current format: 2015-02-24
			$openingTimeMode    = isset($_REQUEST["new_OpeningTimeMode"]) ? $_REQUEST["new_OpeningTimeMode"] : 0;
			$openingName        = isset($_REQUEST["new_OpeningName"]) ? util_quoteSmart($_REQUEST["new_OpeningName"]) : 0;
			$openingDescription = isset($_REQUEST["new_OpeningDescription"]) ? util_quoteSmart($_REQUEST["new_OpeningDescription"]) : 0;
			$openingAdminNotes  = isset($_REQUEST["new_OpeningAdminNotes"]) ? util_quoteSmart($_REQUEST["new_OpeningAdminNotes"]) : 0;
			$openingLocation    = isset($_REQUEST["new_OpeningLocation"]) ? util_quoteSmart($_REQUEST["new_OpeningLocation"]) : 0;
			$openingNumOpenings = isset($_REQUEST["new_OpeningNumOpenings"]) ? $_REQUEST["new_OpeningNumOpenings"] : 0;

			$openingBeginTimeHour   = isset($_REQUEST["new_OpeningBeginTimeHour"]) ? $_REQUEST["new_OpeningBeginTimeHour"] : 0;
			$openingBeginTimeMinute = isset($_REQUEST["new_OpeningBeginTimeMinute"]) ? $_REQUEST["new_OpeningBeginTimeMinute"] : 0;
			$openingBeginTime_AMPM  = isset($_REQUEST["new_OpeningBeginTime_AMPM"]) ? $_REQUEST["new_OpeningBeginTime_AMPM"] : 0;

			// these are valid if $openingTimeMode is 'time_range'
			$openingEndTimeHour        = isset($_REQUEST["new_OpeningEndTimeHour"]) ? $_REQUEST["new_OpeningEndTimeHour"] : 0;
			$openingEndTimeMinute      = isset($_REQUEST["new_OpeningEndTimeMinute"]) ? $_REQUEST["new_OpeningEndTimeMinute"] : 0;
			$openingEndTimeMinute_AMPM = isset($_REQUEST["new_OpeningEndTimeMinute_AMPM"]) ? $_REQUEST["new_OpeningEndTimeMinute_AMPM"] : 0;

			// these are valid if $openingTimeMode is 'duration'
			$openingDurationEachOpening = isset($_REQUEST["new_OpeningDurationEachOpening"]) ? $_REQUEST["new_OpeningDurationEachOpening"] : 0;

			// is opening repeating?
			$openingRepeatRate           = isset($_REQUEST["new_OpeningRepeatRate"]) ? $_REQUEST["new_OpeningRepeatRate"] : 0;
			$openingNumSignupsPerOpening = isset($_REQUEST["new_OpeningNumSignupsPerOpening"]) ? $_REQUEST["new_OpeningNumSignupsPerOpening"] : 0;
			$openingUntilDate            = isset($_REQUEST["new_OpeningUntilDate"]) ? $_REQUEST["new_OpeningUntilDate"] : 0;
			// notes: DOW = repeat_dow_sun, repeat_dow_mon, repeat_dow_tue, ...
			// notes: DOM = repeat_dom_1, repeat_dom_2, ..., repeat_dom_10, ..., repeat_dom_31
		}
		elseif ((isset($_REQUEST["edit_OpeningID"])) && (is_numeric($_REQUEST["edit_OpeningID"])) && ($_REQUEST["edit_OpeningID"] > 0)) {
			// Edit Existing Opening
			$openingSheetID = (isset($_REQUEST["edit_SheetID"])) && (is_numeric($_REQUEST["edit_SheetID"])) ? $_REQUEST["edit_SheetID"] : 0;
			$openingID      = isset($_REQUEST["edit_OpeningID"]) ? $_REQUEST["edit_OpeningID"] : 0;

			// reformat $openingDateBegin to match expected format
			$openingDateBegin = isset($_REQUEST["edit_OpeningDateBegin"]) ? $_REQUEST["edit_OpeningDateBegin"] : 0; // current format: 02/24/2015
			$reformatDateAry  = explode("/", $openingDateBegin);
			$openingDateBegin = $reformatDateAry[2] . '-' . $reformatDateAry[0] . '-' . $reformatDateAry[1]; // current format: 2015-02-24

			$openingTimeMode    = 'time_range'; // HARDCODED VALUE
			$openingName        = isset($_REQUEST["edit_OpeningName"]) ? util_quoteSmart($_REQUEST["edit_OpeningName"]) : 0;
			$openingDescription = isset($_REQUEST["edit_OpeningDescription"]) ? util_quoteSmart($_REQUEST["edit_OpeningDescription"]) : 0;
			$openingAdminNotes  = isset($_REQUEST["edit_OpeningAdminNotes"]) ? util_quoteSmart($_REQUEST["edit_OpeningAdminNotes"]) : 0;
			$openingLocation    = isset($_REQUEST["edit_OpeningLocation"]) ? util_quoteSmart($_REQUEST["edit_OpeningLocation"]) : 0;
			$openingNumOpenings = 1; // HARDCODED VALUE

			$openingBeginTimeHour   = isset($_REQUEST["edit_OpeningBeginTimeHour"]) ? $_REQUEST["edit_OpeningBeginTimeHour"] : 0;
			$openingBeginTimeMinute = isset($_REQUEST["edit_OpeningBeginTimeMinute"]) ? $_REQUEST["edit_OpeningBeginTimeMinute"] : 0;
			$openingBeginTime_AMPM  = isset($_REQUEST["edit_OpeningBeginTime_AMPM"]) ? $_REQUEST["edit_OpeningBeginTime_AMPM"] : 0;

			// these are valid if $openingTimeMode is 'time_range'
			$openingEndTimeHour        = isset($_REQUEST["edit_OpeningEndTimeHour"]) ? $_REQUEST["edit_OpeningEndTimeHour"] : 0;
			$openingEndTimeMinute      = isset($_REQUEST["edit_OpeningEndTimeMinute"]) ? $_REQUEST["edit_OpeningEndTimeMinute"] : 0;
			$openingEndTimeMinute_AMPM = isset($_REQUEST["edit_OpeningEndTimeMinute_AMPM"]) ? $_REQUEST["edit_OpeningEndTimeMinute_AMPM"] : 0;

			// these are valid if $openingTimeMode is 'duration'
			$openingDurationEachOpening = ''; // this value not used in edit mode

			// is opening repeating?
			$openingRepeatRate           = 1; // HARDCODED VALUE
			$openingNumSignupsPerOpening = isset($_REQUEST["edit_OpeningNumSignupsPerOpening"]) ? $_REQUEST["edit_OpeningNumSignupsPerOpening"] : 0;
			$openingUntilDate            = ''; // this value not used in edit mode
		}
		else {
			// error: invalid $openingID value
			util_displayMessage('error', 'Request failed. You appear to be attempting to do something other than create or edit an opening.');
			require_once(dirname(__FILE__) . '/../foot.php');
			exit;
		}


		// construct valid formats: ensure begin, count, duration style of opening specification...
		$openingBeginTimeMinute = ($openingBeginTimeMinute < 10 ? '0' : '') . $openingBeginTimeMinute;
		$beginDateTime          = DateTime::createFromFormat('Y-m-d g:i a', "$openingDateBegin $openingBeginTimeHour:$openingBeginTimeMinute $openingBeginTime_AMPM");

		// note: default condition is 'duration'
		if ($openingTimeMode == 'time_range') {
			// calc duration of each opening
			$openingEndTimeMinute = ($openingEndTimeMinute < 10 ? '0' : '') . $openingEndTimeMinute;
			$endDateTime          = DateTime::createFromFormat('Y-m-d g:i a', "$openingDateBegin $openingEndTimeHour:$openingEndTimeMinute $openingEndTimeMinute_AMPM");
			// handle case where the range spans midnight
			if (($openingBeginTime_AMPM == 'pm') && ($openingEndTimeMinute_AMPM == 'am')) {
				$endDateTime->modify('+1 day');
			}
			$total_time_range   = date_diff($beginDateTime, $endDateTime, TRUE);
			$time_range_minutes = $total_time_range->format('%h') * 60 + $total_time_range->format('%i');
			// only new openings may set value for 'openingNumOpenings' (editing opening ignores this)
			$openingDurationEachOpening = $time_range_minutes / $openingNumOpenings;
		}
		// at this point the opening specification is always valid to begin at X, do Y openings of Z minutes each


		// if current day is 'valid', then create openings on that day, else update $repeatEndDate later
		$repeatBeginDate = DateTime::createFromFormat('Y-m-d', $openingDateBegin);
		$repeatEndDate   = DateTime::createFromFormat('Y-m-d', $openingDateBegin);

		// check repetition radio value
		// if no repeat, then end date = $openingDateBegin, else end date = $openingUntilDate
		$validation_for_repetition = [];
		if ($openingRepeatRate == 2 || $openingRepeatRate == 3) {

			// constrain $openingUntilDate date to $sheetEndDate date
			$sheet = SUS_Sheet::getOneFromDb(['sheet_id' => $openingSheetID], $DB);
			//$sheetEndDate =  DateTime::createFromFormat('Y-m-d g:i:s', $sheet->end_date);
			$sheetEndDate  = DateTime::createFromFormat('Y-m-d', substr($sheet->end_date, 0, 10));
			$repeatEndDate = DateTime::createFromFormat('m/d/Y', $openingUntilDate);
			if ($sheetEndDate < $repeatEndDate) {
				$repeatEndDate = $sheetEndDate;
			}

			if ($openingRepeatRate == 2) {
				foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $baseDow) {
					if ($_REQUEST["repeat_dow_$baseDow"]) {
						// create array of repeating week days
						array_push($validation_for_repetition, $baseDow);
					}
				}
			}
			elseif ($openingRepeatRate == 3) {
				for ($baseDom = 1; $baseDom < 32; $baseDom++) {
					if ($_REQUEST["repeat_dom_$baseDom"]) {
						// create array of repeating month days
						array_push($validation_for_repetition, $baseDom);
					}
				}
			}
		}

		// 1. generate/find a unique opening group id
		$opening_group_id = 0;

		// conflict avoidance: get all preexisting openings as array for this sheet
		$conflicts_ary            = [];
		$preexisting_openings_ary = SUS_Opening::getAllFromDb(['sheet_id' => $openingSheetID, 'end_datetime >=' => util_dateTimeObject_asMySQL($beginDateTime)], $DB);

		// constrain all datetime to trim seconds
		foreach ($preexisting_openings_ary as $preexisting) {
			$preexisting->begin_datetime = preg_replace('/:\\d\\d$/', ':00', $preexisting->begin_datetime);
			$preexisting->end_datetime   = preg_replace('/:\\d\\d$/', ':00', $preexisting->end_datetime);
		}

		// loop through days from begin date to end date
		$currentOpeningDate = clone $repeatBeginDate;

		$evt_action    = "";
		$evt_action_id = 0;
		$evt_note      = "";
		$log_add_success = [];
		while ($currentOpeningDate <= $repeatEndDate) {
			// validation algorithm for each of the repeat radio choices
			// if current day is 'valid', then create openings on that day
			if (($openingRepeatRate == 1) ||
				(($openingRepeatRate == 2) && (in_array(strtolower($currentOpeningDate->format('D')), $validation_for_repetition))) ||
				(($openingRepeatRate == 3) && (in_array($currentOpeningDate->format('j'), $validation_for_repetition)))
			) {
				$baseOpeningDateTime = DateTime::createFromFormat('Y-m-d g:i a', $currentOpeningDate->format('Y-m-d') . " $openingBeginTimeHour:$openingBeginTimeMinute $openingBeginTime_AMPM");

				// iterate for number of openings, creating a new one at each step
				for ($i = 0; $i < $openingNumOpenings; $i++) {
					// Are we creating a NEW opening or editing an preexisting opening?
					if ($openingID == 'NEW') {
						// CREATING NEW OPENING

						// round any datetime seconds to nearest minute to avoid error
						$newOpeningDateTimeBegin = clone $baseOpeningDateTime;
						$newOpeningDateTimeBegin->modify('+' . round($i * $openingDurationEachOpening) . ' minute');
						$newOpeningDateTimeBegin_str_Ymd_his = util_dateTimeObject_asMySQL($newOpeningDateTimeBegin);
						$newOpeningDateTimeEnd               = clone $baseOpeningDateTime;
						$newOpeningDateTimeEnd->modify('+' . round(($i + 1) * $openingDurationEachOpening) . ' minute');
						$newOpeningDateTimeEnd_str_Ymd_his = util_dateTimeObject_asMySQL($newOpeningDateTimeEnd);

						// conflict avoidance: allow any non-conflicting openings to be created. List blocked openings (conflicts) for user.
						$isConflict = FALSE;
						foreach ($preexisting_openings_ary as $preexisting) {
							if ($newOpeningDateTimeBegin_str_Ymd_his < $preexisting->end_datetime &&
								$newOpeningDateTimeEnd_str_Ymd_his > $preexisting->begin_datetime
							) {
								$needle = "attempted [" . $newOpeningDateTimeBegin->format('m/d/Y h:i A') . " - " . $newOpeningDateTimeEnd->format('m/d/Y h:i A') . "] conflicts with preexisting [" . util_datetimeFormatted($preexisting->begin_datetime) . " - " . util_datetimeFormatted($preexisting->end_datetime) . "]";
								if (!in_array($needle, $conflicts_ary)) {
									array_push($conflicts_ary, $needle);
								}

								$isConflict = TRUE;
								break;
							}
						}

						if (!$isConflict) {
							// create the opening form the parameters specified in the form, then save it
							// create new Opening using factory function
							$newOpening = SUS_Opening::createNewOpening($openingSheetID, $DB);

							$newOpening->opening_group_id = $opening_group_id;
							$newOpening->name             = $openingName;
							$newOpening->description      = $openingDescription;
							$newOpening->max_signups      = $openingNumSignupsPerOpening;
							$newOpening->begin_datetime   = util_dateTimeObject_asMySQL($newOpeningDateTimeBegin);
							$newOpening->end_datetime     = util_dateTimeObject_asMySQL($newOpeningDateTimeEnd);
							$newOpening->location         = $openingLocation;
							$newOpening->admin_comment    = $openingAdminNotes;

							// util_prePrintR($newOpening);

							// save the opening
							$newOpening->updateDb();

							// save for subsequent event log
							$evt_action    = "createNewOpening";
							$evt_action_id = $newOpening->sheet_id;
							$evt_note      = "successfully added openings: ";
							array_push($log_add_success, $newOpening->opening_id);

							if (!$opening_group_id) {
								$opening_group_id             = $newOpening->opening_id;
								$newOpening->opening_group_id = $opening_group_id;
								$newOpening->updateDb();

								// create event log. [requires: user_id(int), flag_success(bool), event_action(varchar), event_action_id(int), event_action_target_type(varchar), event_note(varchar), event_dataset(varchar)]
								util_createEventLog($USER->user_id, TRUE, "set initial opening_group_id", $newOpening->sheet_id, "sheet_id", "set opening_group_id = " . $newOpening->opening_group_id, print_r(json_encode($_REQUEST), TRUE), $DB);
							}
						}
					}
					elseif (isset($openingID) && $openingID > 0) {
						// EDIT PREEXISTING OPENING

						// fetch this opening_id from the DB, update it, then save it
						$editOpening = SUS_Opening::getOneFromDb(['opening_id' => $openingID], $DB);

						if (!$editOpening->matchesDb) {
							// error: matching record does not exist
							util_displayMessage('error', 'Error: No matching Opening record found. Attempt to edit opening record failed.');
							require_once(dirname(__FILE__) . '/../foot.php');

							// create event log. [requires: user_id(int), flag_success(bool), event_action(varchar), event_action_id(int), event_action_target_type(varchar), event_note(varchar), event_dataset(varchar)]
							$evt_action    = "editOpening";
							$evt_action_id = $openingID;
							$evt_note      = "Error: No matching Opening record found. Attempt to edit opening record failed";
							util_createEventLog($USER->user_id, FALSE, $evt_action, $evt_action_id, "opening_id", $evt_note, print_r(json_encode($_REQUEST), TRUE), $DB);
							exit;
						}

						// round any datetime seconds to nearest minute to avoid error
						$editOpeningDateTimeBegin = clone $baseOpeningDateTime;
						$editOpeningDateTimeBegin->modify('+' . round($i * $openingDurationEachOpening) . ' minute');
						$editOpeningDateTimeBegin_str_Ymd_his = util_dateTimeObject_asMySQL($editOpeningDateTimeBegin);
						$editOpeningDateTimeEnd               = clone $baseOpeningDateTime;
						$editOpeningDateTimeEnd->modify('+' . round(($i + 1) * $openingDurationEachOpening) . ' minute');
						$editOpeningDateTimeEnd_str_Ymd_his = util_dateTimeObject_asMySQL($editOpeningDateTimeEnd);

						// conflict avoidance: allow any non-conflicting openings to be edited. List blocked openings (conflicts) for user.
						$isConflict = FALSE;
						foreach ($preexisting_openings_ary as $preexisting) {
							if ($editOpeningDateTimeBegin_str_Ymd_his < $preexisting->end_datetime &&
								$editOpeningDateTimeEnd_str_Ymd_his > $preexisting->begin_datetime &&
								$openingID != $preexisting->opening_id
							) {
								$needle = "attempted [" . $editOpeningDateTimeBegin->format('m/d/Y h:i A') . " - " . $editOpeningDateTimeEnd->format('m/d/Y h:i A') . "] conflicts with preexisting [" . util_datetimeFormatted($preexisting->begin_datetime) . " - " . util_datetimeFormatted($preexisting->end_datetime) . "]";
								if (!in_array($needle, $conflicts_ary)) {
									array_push($conflicts_ary, $needle);
								}

								$isConflict = TRUE;
								break;
							}
						}

						if (!$isConflict) {

							$editOpening->name           = $openingName;
							$editOpening->description    = $openingDescription;
							$editOpening->max_signups    = $openingNumSignupsPerOpening;
							$editOpening->begin_datetime = util_dateTimeObject_asMySQL($editOpeningDateTimeBegin);
							$editOpening->end_datetime   = util_dateTimeObject_asMySQL($editOpeningDateTimeEnd);
							$editOpening->location       = $openingLocation;
							$editOpening->admin_comment  = $openingAdminNotes;
							$editOpening->updated_at     = util_currentDateTimeString_asMySQL();

							// util_prePrintR($editOpening);

							// save the opening
							$editOpening->updateDb();

							// save for subsequent event log
							$evt_action    = "editOpening";
							$evt_action_id = $editOpening->sheet_id;
							$evt_note      = "successfully edited opening: ";
							array_push($log_add_success, $editOpening->opening_id);
						}
					}
				}
			}

			// iterator: reset date
			$currentOpeningDate->modify('+1 day');
		}

		// create event log. [requires: user_id(int), flag_success(bool), event_action(varchar), event_action_id(int), event_action_target_type(varchar), event_note(varchar), event_dataset(varchar)]
		$evt_note .= implode(", ", $log_add_success);
		// only for debugging a possible bug that i cannot replicate...
		//	if(!$evt_action) {$evt_action = "dud1";}
		//	if(!$evt_action_id) {$evt_action = "dud2";}
		//	if(!$evt_note) {$evt_action = "dud3";} else{$evt_note .= implode(", ", $log_add_success);}
		util_createEventLog($USER->user_id, TRUE, $evt_action, $evt_action_id, "sheet_id", $evt_note, print_r(json_encode($_REQUEST), TRUE), $DB);

		// package the conflicts into a urlencoded string for display on resultant page
		if ($conflicts_ary) {
			$plural_string = '';
			if (count($conflicts_ary) > 1) {
				$plural_string = 's';
			}
			$conflicts_string = "<strong>No action taken on the following conflict" . $plural_string . ":</strong><br /><ul type=\"1\">";
			foreach ($conflicts_ary as $conflict) {
				$conflicts_string .= "<li>" . htmlentities($conflict, ENT_QUOTES, 'UTF-8') . "</li>";
			}
			$conflicts_string .= "</ul><br />Any other requests were successfully completed.";
			$conflicts_string = urlencode($conflicts_string);

			// redirect with conflicts param
			header('Location: ' . APP_FOLDER . '/app_code/sheets_edit_one.php?sheet=' . htmlentities($openingSheetID, ENT_QUOTES, 'UTF-8') . '&conflicts=' . $conflicts_string);
		}
		else {
			// redirect without conflicts param
			// TODO ? could put a temporary alert message on screen showing success (similar to 'conflicts' failure msg above)
			header('Location: ' . APP_FOLDER . '/app_code/sheets_edit_one.php?sheet=' . htmlentities($openingSheetID, ENT_QUOTES, 'UTF-8'));
		}


	}