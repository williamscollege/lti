<?php
	require_once('../app_setup.php');
	$pageTitle = '';
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {

//		util_prePrintR($_POST);

		$openingSheetID     = $_REQUEST["openingSheetID"];
		$openingID          = $_REQUEST["openingID"];
		$openingDateStart   = $_REQUEST["openingDateStart"];
		$openingTimeMode    = $_REQUEST["openingTimeMode"];
		$openingName        = $_REQUEST["openingName"];
		$openingDescription = $_REQUEST["openingDescription"];
		$openingAdminNotes  = $_REQUEST["openingAdminNotes"];
		$openingLocation    = $_REQUEST["openingLocation"];

		$openingBeginTimeHour   = $_REQUEST["openingBeginTimeHour"];
		$openingBeginTimeMinute = $_REQUEST["openingBeginTimeMinute"];
		$openingBeginTime_AMPM  = $_REQUEST["openingBeginTime_AMPM"];

		$openingNumOpenings = $_REQUEST["openingNumOpenings"];

		// these are valid is $openingTimeMode is time range
		$openingEndTimeHour        = $_REQUEST["openingEndTimeHour"];
		$openingEndTimeMinute      = $_REQUEST["openingEndTimeMinute"];
		$openingEndTimeMinute_AMPM = $_REQUEST["openingEndTimeMinute_AMPM"];

		// these are valid is $openingTimeMode is duration
		$openingDurationEachOpening = $_REQUEST["openingDurationEachOpening"];

		$openingNumSignupsPerOpening = $_REQUEST["openingNumSignupsPerOpening"];
		$openingRepeatRate           = $_REQUEST["openingRepeatRate"];

		// DOW: repeat_dow_sun, repeat_dow_mon, repeat_dow_tue, etc.
		// DOM: repeat_dom_1, repeat_dom_2, repeat_dom_10, repeat_dom_31

		$openingUntilDate = $_REQUEST["openingUntilDate"];


		// ensure start, count, duration style of opening specification...
		$openingBeginTimeMinute = ($openingBeginTimeMinute < 10 ? '0' : '') . $openingBeginTimeMinute;
		$beginDateTime          = DateTime::createFromFormat('Y-m-d g:i a', "$openingDateStart $openingBeginTimeHour:$openingBeginTimeMinute $openingBeginTime_AMPM");

		// note: default condition is 'duration'
		if ($openingTimeMode == 'time_range') {
			// calc duration of each opening
			$openingEndTimeMinute = ($openingEndTimeMinute < 10 ? '0' : '') . $openingEndTimeMinute;
			$endDateTime          = DateTime::createFromFormat('Y-m-d g:i a', "$openingDateStart $openingEndTimeHour:$openingEndTimeMinute $openingEndTimeMinute_AMPM");
			// handle case where the range spans midnight
			if (($openingBeginTime_AMPM == 'pm') && ($openingEndTimeMinute_AMPM == 'am')) {
				$endDateTime->modify('+1 day');
			}
			$total_time_range           = date_diff($beginDateTime, $endDateTime, TRUE);
			$time_range_minutes         = $total_time_range->format('%h') * 60 + $total_time_range->format('%i');
			$openingDurationEachOpening = $time_range_minutes / $openingNumOpenings;
		}
		// at this point the opening specification is always valid as start at X, do Y openings of Z minutes each


		// if current day is 'valid', then create openings on that day, else update $repeatEndDate later
		$repeatBeginDate = DateTime::createFromFormat('Y-m-d', $openingDateStart);
		$repeatEndDate   = DateTime::createFromFormat('Y-m-d', $openingDateStart);

		// check repetition radio value
		// if no repeat, then end date = $openingDateStart, else end date = $openingUntilDate
		$validation_for_repetition = [];
		if ($openingRepeatRate == 2 || $openingRepeatRate == 3) {
			$repeatEndDate = DateTime::createFromFormat('m/d/Y', $openingUntilDate);
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
					// create the opening form the parameters specified in the form, then save it
					// create new Opening using factory function
					$newOpening = SUS_Opening::createNewOpening($openingSheetID, $DB);

					// round resultant to maintain format strictly in minutes
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
			}

			// iterator: reset date
			$currentOpeningDate->modify('+1 day');
		}

		// redirect
		header('Location: ' . APP_FOLDER . '/app_code/edit_sheet.php?sheet=' . $openingSheetID);

	}