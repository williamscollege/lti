<?php
	require_once('../app_setup.php');
	//$pageTitle = ucfirst(util_lang('my_sheets'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {

		util_prePrintR($_POST);

		$openingSheetID                = $_REQUEST["openingSheetID"];
		$openingID                     = $_REQUEST["openingID"];
		$openingDateStart              = $_REQUEST["openingDateStart"];
		$openingName                   = $_REQUEST["openingName"];
		$openingDescription            = $_REQUEST["openingDescription"];
		$openingAdminNotes             = $_REQUEST["openingAdminNotes"];
		$openingLocation               = $_REQUEST["openingLocation"];
		$openingBeginTimeHour          = $_REQUEST["openingBeginTimeHour"];
		$openingBeginTimeMinute        = $_REQUEST["openingBeginTimeMinute"];
		$openingBeginTime_AMPM         = $_REQUEST["openingBeginTime_AMPM"];
		$openingEndTimeHour            = $_REQUEST["openingEndTimeHour"];
		$openingEndTimeMinute          = $_REQUEST["openingEndTimeMinute"];
		$openingEndTimeMinute_AMPM     = $_REQUEST["openingEndTimeMinute_AMPM"];
		$openingDurationEachOpening    = $_REQUEST["openingDurationEachOpening"];
		$openingNumOpeningsInTimeRange = $_REQUEST["openingNumOpeningsInTimeRange"];
		$openingNumSignupsPerOpening   = $_REQUEST["openingNumSignupsPerOpening"];
		$openingRepeatRate             = $_REQUEST["openingRepeatRate"];
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
		$openingUntilDate              = $_REQUEST["openingUntilDate"];

//		$opening


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