<?php
	require_once('../app_setup.php');
	//$pageTitle = ucfirst(util_lang('my_sheets'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {

		util_prePrintR($_POST);

		$openingSheetID         = $_REQUEST["openingSheetID"];
		$openingID              = $_REQUEST["openingID"];
		$openingDateStart       = $_REQUEST["openingDateStart"];
		$openingTimeMode        = $_REQUEST["openingTimeMode"];
		$openingName            = $_REQUEST["openingName"];
		$openingDescription     = $_REQUEST["openingDescription"];
		$openingAdminNotes      = $_REQUEST["openingAdminNotes"];
		$openingLocation        = $_REQUEST["openingLocation"];

		$openingBeginTimeHour   = $_REQUEST["openingBeginTimeHour"];
		$openingBeginTimeMinute = $_REQUEST["openingBeginTimeMinute"];
		$openingBeginTime_AMPM  = $_REQUEST["openingBeginTime_AMPM"];

		$openingNumOpenings     = $_REQUEST["openingNumOpeningsInTimeRange"];

		// these are valid is $openingTimeMode is time range
		$openingEndTimeHour        = $_REQUEST["openingEndTimeHour"];
		$openingEndTimeMinute      = $_REQUEST["openingEndTimeMinute"];
		$openingEndTimeMinute_AMPM = $_REQUEST["openingEndTimeMinute_AMPM"];

		// these are valid is $openingTimeMode is duration
		$openingDurationEachOpening = $_REQUEST["openingDurationEachOpening"];

		$openingNumSignupsPerOpening = $_REQUEST["openingNumSignupsPerOpening"];
		$openingRepeatRate           = $_REQUEST["openingRepeatRate"];
		//		$repeat_dow_sun                = $_REQUEST["repeat_dow_sun"];
		//		$repeat_dow_mon                = $_REQUEST["repeat_dow_mon"];
		//		$repeat_dow_tue                = $_REQUEST["repeat_dow_tue"];
		//		$repeat_dow_wed                = $_REQUEST["repeat_dow_wed"];
		//		$repeat_dow_thu                = $_REQUEST["repeat_dow_thu"];
		//		$repeat_dow_fri                = $_REQUEST["repeat_dow_fri"];
		//		$repeat_dow_sat                = $_REQUEST["repeat_dow_sat"];
		//		$repeat_dom_1                  = $_REQUEST["repeat_dom_1"];
		//		// TODO - note missing days
		//		$repeat_dom_31                 = $_REQUEST["repeat_dom_31"];
		$openingUntilDate = $_REQUEST["openingUntilDate"];


		// ensure start, count, duration style of opening specification...
		$openingBeginTimeMinute = ($openingBeginTimeMinute<10?'0':'').$openingBeginTimeMinute;
		$beginDateTime = DateTime::createFromFormat('Y-m-d g:i a',"$openingDateStart $openingBeginTimeHour:$openingBeginTimeMinute $openingBeginTime_AMPM");
		if ($openingTimeMode == 'time_range') {
			// calc duration of each opening
			$openingEndTimeMinute = ($openingEndTimeMinute<10?'0':'').$openingEndTimeMinute;
			$endDateTime = DateTime::createFromFormat('Y-m-d g:i a',"$openingDateStart $openingEndTimeHour:$openingEndTimeMinute $openingEndTimeMinute_AMPM");
			// handle case where the range spans midnight
			if (($openingBeginTime_AMPM == 'pm') && ($openingEndTimeMinute_AMPM == 'am')) {
				$endDateTime->modify('+1 day');
			}
			$total_time_range = date_diff( $beginDateTime , $endDateTime, true);
			$time_range_minutes = $total_time_range->format('%h')*60 + $total_time_range->format('%i');
			$openingDurationEachOpening = $time_range_minutes / $openingNumOpenings;
		}
		// at this point the opening specification is al;ways valid as start at X, do Y openings of Z minutes each

		// check repetition radio value
		// if no repeat, then end date = $openingDateStart
		// else end date = $openingUntilDate

		// from begin date to end date:
		//   if current day is 'valid', then create openings on that day

		// NEED: way of looping through days from begin date to end date
		// NEED: validation algo for each of the repeat radio choices

		echo "<pre>\n";

		$repeatBeginDate = DateTime::createFromFormat('Y-m-d',$openingDateStart);
		$repeatEndDate = DateTime::createFromFormat('Y-m-d',$openingDateStart);
		if ($openingRepeatRate == 2  ||  $openingRepeatRate == 3) {
			$repeatEndDate = DateTime::createFromFormat('m/d/Y',$openingUntilDate);
		}

		echo $repeatBeginDate->format('Y-m-d')."\n";
		echo $repeatEndDate->format('Y-m-d')."\n";

		// 1. generate/find a unique opening group id
		$opening_group_id = 'uniquify this';
		$currentOpeningDate = clone $repeatBeginDate;
		while ($currentOpeningDate <= $repeatEndDate) {
			echo "current date in loop: ".$currentOpeningDate->format('Y-m-d')."\n";
			//   if current day is 'valid', then create openings on that day
//			if (/* NEED: validation algo for each of the repeat radio choices*/) {
			if (true) {
				$baseOpeningDateTime = DateTime::createFromFormat('Y-m-d g:i a',$currentOpeningDate->format('Y-m-d')." $openingBeginTimeHour:$openingBeginTimeMinute $openingBeginTime_AMPM");
				// iterate for number of openings, creating a new one at each step
				for ($i = 0; $i < $openingNumOpenings; $i++) {
					// create the opening form the parameters specified in the form
					// save it
					$newOpeningDateTimeBegin = clone $baseOpeningDateTime;
					$newOpeningDateTimeBegin->modify('+'.$i*$openingDurationEachOpening.' minute');
					$newOpeningDateTimeEnd = clone $baseOpeningDateTime;
					$newOpeningDateTimeEnd->modify('+'.($i+1)*$openingDurationEachOpening.' minute');

//				)->add(new DateInterval('PT'.$i*$openingDurationEachOpening.'M'));
//					$newOpeningDateTimeEnd = (clone $baseOpeningDateTime)->add(new DateInterval('PT'.($i+1)*$openingDurationEachOpening.'M'));

					echo $newOpeningDateTimeBegin->format('Y-m-d h:i').' - '.$newOpeningDateTimeEnd->format('Y-m-d h:i')."\n";
				}
			}

			$currentOpeningDate->modify('+1 day');
		}



		/*

				if ($sheetIsDataIncoming) {
					// use cases:
					// 1) postback for brand new sheet (record not yet in db)
					// 2) postback for edited sheet (record exists in db)

					if (isset($_REQUEST["sheet"])) {
						// populate fields based on DB record
						$s = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
					}
					else {
						// create new sheet
						$s = SUS_Sheet::createNewSheet($USER->user_id, $DB);
					}

					// util_prePrintR($_REQUEST); // debugging

					$s->updated_at               = date("Y-m-d H:i:s");
					$s->owner_user_id            = $USER->user_id;
					$s->sheetgroup_id            = $_REQUEST["selectSheetgroupID"];
					$s->name                     = $_REQUEST["inputSheetName"];
					$s->description              = $_REQUEST["textSheetDescription"];
					$s->type                     = "timeblocks"; // hardcode this data as possible hook for future use/modification
					$s->date_opens               = date_format(new DateTime($_REQUEST["inputSheetDateStart"] . " 00:00:00"), "Y-m-d H:i:s");
					$s->date_closes              = date_format(new DateTime($_REQUEST["inputSheetDateEnd"] . " 23:59:59"), "Y-m-d H:i:s");
					$s->max_total_user_signups   = $_REQUEST["selectMaxTotalSignups"];
					$s->max_pending_user_signups = $_REQUEST["selectMaxPendingSignups"];
					//$s->flag_alert_owner_change   = $_REQUEST[""];
					$s->flag_alert_owner_signup   = util_getValueForCheckboxRequestData('checkAlertOwnerSignup');
					$s->flag_alert_owner_imminent = util_getValueForCheckboxRequestData('checkAlertOwnerImminent');
					//$s->flag_alert_admin_change   = $_REQUEST[""];
					$s->flag_alert_admin_signup   = util_getValueForCheckboxRequestData('checkAlertAdminSignup');
					$s->flag_alert_admin_imminent = util_getValueForCheckboxRequestData('checkAlertAdminImminent');

					if (!$s->matchesDb) {
						$s->updateDb();
					}
				}
				else {
					if (isset($_REQUEST["sheet"])) {
						// use cases:
						// 1) requested to edit existing sheet from link on another page (record exists in db)
						$sheetIsDataIncoming = TRUE;
						$s                   = SUS_Sheet::getOneFromDb(['sheet_id' => $_REQUEST["sheet"]], $DB);
					}
				}*/

	}