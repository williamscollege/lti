<?php
	require_once(dirname(__FILE__) . '/../app_setup.php');
	$pageTitle = '';
	require_once(dirname(__FILE__) . '/../app_head.php');


	if ($IS_AUTHENTICATED) {
		// util_prePrintR($_POST); exit;

		// processing for either creating 'NEW' opening(s) or editing an existing opening_id
		if ((isset($_REQUEST["new_OpeningID"])) && ($_REQUEST["new_OpeningID"] == "NEW")) {
			// Create New Opening
			$openingSheetID     = htmlentities((isset($_REQUEST["new_SheetID"])) ? $_REQUEST["new_SheetID"] : 0);
			$openingID          = htmlentities((isset($_REQUEST["new_OpeningID"])) ? $_REQUEST["new_OpeningID"] : 0);
			$openingDateBegin   = htmlentities((isset($_REQUEST["new_OpeningDateBegin"])) ? $_REQUEST["new_OpeningDateBegin"] : 0); // current format: 2015-02-24
			$openingTimeMode    = htmlentities((isset($_REQUEST["new_OpeningTimeMode"])) ? $_REQUEST["new_OpeningTimeMode"] : 0);
			$openingName        = htmlentities((isset($_REQUEST["new_OpeningName"])) ? util_quoteSmart($_REQUEST["new_OpeningName"]) : 0);
			$openingDescription = htmlentities((isset($_REQUEST["new_OpeningDescription"])) ? util_quoteSmart($_REQUEST["new_OpeningDescription"]) : 0);
			$openingAdminNotes  = htmlentities((isset($_REQUEST["new_OpeningAdminNotes"])) ? util_quoteSmart($_REQUEST["new_OpeningAdminNotes"]) : 0);
			$openingLocation    = htmlentities((isset($_REQUEST["new_OpeningLocation"])) ? util_quoteSmart($_REQUEST["new_OpeningLocation"]) : 0);
			$openingNumOpenings = htmlentities((isset($_REQUEST["new_OpeningNumOpenings"])) ? $_REQUEST["new_OpeningNumOpenings"] : 0);

			$openingBeginTimeHour   = htmlentities((isset($_REQUEST["new_OpeningBeginTimeHour"])) ? $_REQUEST["new_OpeningBeginTimeHour"] : 0);
			$openingBeginTimeMinute = htmlentities((isset($_REQUEST["new_OpeningBeginTimeMinute"])) ? $_REQUEST["new_OpeningBeginTimeMinute"] : 0);
			$openingBeginTime_AMPM  = htmlentities((isset($_REQUEST["new_OpeningBeginTime_AMPM"])) ? $_REQUEST["new_OpeningBeginTime_AMPM"] : 0);

			// these are valid if $openingTimeMode is 'time_range'
			$openingEndTimeHour        = htmlentities((isset($_REQUEST["new_OpeningEndTimeHour"])) ? $_REQUEST["new_OpeningEndTimeHour"] : 0);
			$openingEndTimeMinute      = htmlentities((isset($_REQUEST["new_OpeningEndTimeMinute"])) ? $_REQUEST["new_OpeningEndTimeMinute"] : 0);
			$openingEndTimeMinute_AMPM = htmlentities((isset($_REQUEST["new_OpeningEndTimeMinute_AMPM"])) ? $_REQUEST["new_OpeningEndTimeMinute_AMPM"] : 0);

			// these are valid if $openingTimeMode is 'duration'
			$openingDurationEachOpening = htmlentities((isset($_REQUEST["new_OpeningDurationEachOpening"])) ? $_REQUEST["new_OpeningDurationEachOpening"] : 0);

			// is opening repeating?
			$openingRepeatRate           = htmlentities((isset($_REQUEST["new_OpeningRepeatRate"])) ? $_REQUEST["new_OpeningRepeatRate"] : 0);
			$openingNumSignupsPerOpening = htmlentities((isset($_REQUEST["new_OpeningNumSignupsPerOpening"])) ? $_REQUEST["new_OpeningNumSignupsPerOpening"] : 0);
			$openingUntilDate            = htmlentities((isset($_REQUEST["new_OpeningUntilDate"])) ? $_REQUEST["new_OpeningUntilDate"] : 0);
			// notes: DOW = repeat_dow_sun, repeat_dow_mon, repeat_dow_tue, ...
			// notes: DOM = repeat_dom_1, repeat_dom_2, ..., repeat_dom_10, ..., repeat_dom_31
		}
		elseif ((isset($_REQUEST["edit_OpeningID"])) && (is_numeric($_REQUEST["edit_OpeningID"])) && ($_REQUEST["edit_OpeningID"] > 0)) {
			// Edit Existing Opening
			$openingSheetID = htmlentities(((isset($_REQUEST["edit_SheetID"])) && (is_numeric($_REQUEST["edit_SheetID"]))) ? $_REQUEST["edit_SheetID"] : 0);
			$openingID      = htmlentities((isset($_REQUEST["edit_OpeningID"])) ? $_REQUEST["edit_OpeningID"] : 0);

			// reformat $openingDateBegin to match expected format
			$openingDateBegin = htmlentities((isset($_REQUEST["edit_OpeningDateBegin"])) ? $_REQUEST["edit_OpeningDateBegin"] : 0); // current format: 02/24/2015
			$reformatDateAry  = explode("/", $openingDateBegin);
			$openingDateBegin = $reformatDateAry[2] . '-' . $reformatDateAry[0] . '-' . $reformatDateAry[1]; // current format: 2015-02-24

			$openingTimeMode    = 'time_range'; // HARDCODED VALUE
			$openingName        = htmlentities((isset($_REQUEST["edit_OpeningName"])) ? util_quoteSmart($_REQUEST["edit_OpeningName"]) : 0);
			$openingDescription = htmlentities((isset($_REQUEST["edit_OpeningDescription"])) ? util_quoteSmart($_REQUEST["edit_OpeningDescription"]) : 0);
			$openingAdminNotes  = htmlentities((isset($_REQUEST["edit_OpeningAdminNotes"])) ? util_quoteSmart($_REQUEST["edit_OpeningAdminNotes"]) : 0);
			$openingLocation    = htmlentities((isset($_REQUEST["edit_OpeningLocation"])) ? util_quoteSmart($_REQUEST["edit_OpeningLocation"]) : 0);
			$openingNumOpenings = 1; // HARDCODED VALUE

			$openingBeginTimeHour   = htmlentities((isset($_REQUEST["edit_OpeningBeginTimeHour"])) ? $_REQUEST["edit_OpeningBeginTimeHour"] : 0);
			$openingBeginTimeMinute = htmlentities((isset($_REQUEST["edit_OpeningBeginTimeMinute"])) ? $_REQUEST["edit_OpeningBeginTimeMinute"] : 0);
			$openingBeginTime_AMPM  = htmlentities((isset($_REQUEST["edit_OpeningBeginTime_AMPM"])) ? $_REQUEST["edit_OpeningBeginTime_AMPM"] : 0);

			// these are valid if $openingTimeMode is 'time_range'
			$openingEndTimeHour        = htmlentities((isset($_REQUEST["edit_OpeningEndTimeHour"])) ? $_REQUEST["edit_OpeningEndTimeHour"] : 0);
			$openingEndTimeMinute      = htmlentities((isset($_REQUEST["edit_OpeningEndTimeMinute"])) ? $_REQUEST["edit_OpeningEndTimeMinute"] : 0);
			$openingEndTimeMinute_AMPM = htmlentities((isset($_REQUEST["edit_OpeningEndTimeMinute_AMPM"])) ? $_REQUEST["edit_OpeningEndTimeMinute_AMPM"] : 0);

			// these are valid if $openingTimeMode is 'duration'
			$openingDurationEachOpening = ''; // this value not used in edit mode

			// is opening repeating?
			$openingRepeatRate           = 1; // HARDCODED VALUE
			$openingNumSignupsPerOpening = htmlentities((isset($_REQUEST["edit_OpeningNumSignupsPerOpening"])) ? $_REQUEST["edit_OpeningNumSignupsPerOpening"] : 0);
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


		// TODO - Conflict Avoidance (validation)
		// Problems adding openings:
		// 2015-02-11 11:37 AM to 5:15 PM :: conflicts with another opening on this sheet
		// http://oldglow.williams.edu/blocks/signup_sheets/sheet_create_openings_process.php
		// example: http://oldglow.williams.edu/blocks/signup_sheets/?contextid=2&action=editsheet&sheet=1311&sheetgroup=2077


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
		// at this point the opening specification is always valid as begin at X, do Y openings of Z minutes each


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
			$sheetEndDate = DateTime::createFromFormat('Y-m-d', substr($sheet->end_date, 0, 10));
			$repeatEndDate   = DateTime::createFromFormat('m/d/Y', $openingUntilDate);
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
				for ($baseDom = 1; $baseDom < 35; $baseDom++) {
					if ($_REQUEST["repeat_dom_$baseDom"]) {
						// create array of repeating month days
						array_push($validation_for_repetition, $baseDom);
					}
				}
			}
		}

		// 1. generate/find a unique opening group id
		$opening_group_id = 0;

		// loop through days from begin date to end date
		$currentOpeningDate = clone $repeatBeginDate;
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
						// create the opening form the parameters specified in the form, then save it
						// create new Opening using factory function
						$newOpening = SUS_Opening::createNewOpening($openingSheetID, $DB);

						// round any datetime seconds to nearest minute to avoid error
						$newOpeningDateTimeBegin = clone $baseOpeningDateTime;
						$newOpeningDateTimeBegin->modify('+' . round($i * $openingDurationEachOpening) . ' minute');
						$newOpeningDateTimeEnd = clone $baseOpeningDateTime;
						$newOpeningDateTimeEnd->modify('+' . round(($i + 1) * $openingDurationEachOpening) . ' minute');
						// echo $newOpeningDateTimeBegin->format('Y-m-d h:i') . ' - ' . $newOpeningDateTimeEnd->format('Y-m-d h:i') . "\n";

						$newOpening->opening_group_id = $opening_group_id;
						$newOpening->name             = $openingName;
						$newOpening->description      = $openingDescription;
						$newOpening->max_signups      = $openingNumSignupsPerOpening;
						$newOpening->admin_comment    = $openingAdminNotes;
						$newOpening->begin_datetime   = util_dateTimeObject_asMySQL($newOpeningDateTimeBegin);
						$newOpening->end_datetime     = util_dateTimeObject_asMySQL($newOpeningDateTimeEnd);
						$newOpening->location         = $openingLocation;

						// util_prePrintR($newOpening);

						// save the new opening
						$newOpening->updateDb();

						if (!$opening_group_id) {
							$opening_group_id             = $newOpening->opening_id;
							$newOpening->opening_group_id = $opening_group_id;
							$newOpening->updateDb();
						}
					}
					elseif (isset($openingID) && $openingID > 0) {
						// fetch this opening_id from the DB, update it, then save it
						$editOpening = SUS_Opening::getOneFromDb(['opening_id' => $openingID], $DB);

						if (!$editOpening->matchesDb) {
							// error: matching record does not exist
							util_displayMessage('error', 'Error: No matching Opening record found. Attempt to edit opening record failed.');
							require_once(dirname(__FILE__) . '/../foot.php');
							exit;
						}

						// round any datetime seconds to nearest minute to avoid error
						$editOpeningDateTimeBegin = clone $baseOpeningDateTime;
						$editOpeningDateTimeBegin->modify('+' . round($i * $openingDurationEachOpening) . ' minute');
						$editOpeningDateTimeEnd = clone $baseOpeningDateTime;
						$editOpeningDateTimeEnd->modify('+' . round(($i + 1) * $openingDurationEachOpening) . ' minute');
						// echo $editOpeningDateTimeBegin->format('Y-m-d h:i') . ' - ' . $editOpeningDateTimeEnd->format('Y-m-d h:i') . "\n";
						$editOpening->name           = $openingName;
						$editOpening->description    = $openingDescription;
						$editOpening->max_signups    = $openingNumSignupsPerOpening;
						$editOpening->admin_comment  = $openingAdminNotes;
						$editOpening->begin_datetime = util_dateTimeObject_asMySQL($editOpeningDateTimeBegin);
						$editOpening->end_datetime   = util_dateTimeObject_asMySQL($editOpeningDateTimeEnd);
						$editOpening->location       = $openingLocation;
						$editOpening->updated_at     = util_currentDateTimeString_asMySQL();

						// util_prePrintR($editOpening);

						// save the new opening
						$editOpening->updateDb();
					}
				}
			}

			// iterator: reset date
			$currentOpeningDate->modify('+1 day');
		}

		// redirect
		header('Location: ' . APP_FOLDER . '/app_code/sheets_edit_one.php?sheet=' . $openingSheetID);
	}