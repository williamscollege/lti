<?php
	/***********************************************
	 ** Project:    Monitor Williams SIS Imports into Canvas LMS
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ***********************************************/


	#------------------------------------------------#
	# Helper functions
	#------------------------------------------------#

	# Wait Condition and Flush Content to browser
	function util_sleepFlushContent() {
		# pause, then flush output to screen
		sleep(5); // delay execution (wait for Canvas to resolve file upload, and try not to exceed Canvas requested limit of 3,000 api hits / hour)
		ob_flush(); // flush (send) the output buffer; to flush the ob output buffers, you will have to call both ob_flush() and flush()
		flush(); // flush system output buffer; to flush the ob output buffers, you will have to call both ob_flush() and flush()
		set_time_limit(0); // restarts the timeout counter from zero

		# set boolDoPause variable to be FALSE
		return FALSE;
	}


	# Convert seconds to Hour:Minute:Second format
	function convertSecondsToHMSFormat($seconds) {
		$t = round($seconds);
		return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
	}


	# Validation routine: trim fxn strips (various types of) whitespace characters from the beginning and end of a string
	function util_quoteSmart($value) {
		// stripslashes — Un-quotes a quoted string
		// trim — Strip whitespace (or other characters) from the beginning and end of a string
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
			$value = trim($value);
		}
		return $value;
	}


	# Output an object wrapped with HTML PRE tags for pretty output
	function util_prePrintR($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
		return TRUE;
	}

	# returns a string thats the current date and time, in format YYYY/MM/SS HH:MI
	function util_currentDateTimeString() {
		return date('Y-m-d H:i');
	}

	function util_currentDateTimeString_asMySQL() {
		return date('Y-m-d H:i:s');
	}

	function util_dateTimeObject_asMySQL($dt) {
		return $dt->format('Y-m-d H:i:s');
	}

	function util_convert_UTC_string_to_date_object($utc) {
		// echo "utc=" . $utc;

		// create DateTime object and explicitly set timezone as UTC to match expected UTC string value
		$dt = new DateTime($utc, new DateTimeZone('UTC'));

		// convert DateTimeZone to convert UTC value to local time value
		$dt->setTimeZone(new DateTimeZone("America/New_York"));

		//util_prePrintR($dt); exit;
		return util_dateTimeObject_asMySQL($dt);
	}

	function create_eventlog(
		$connString,
		$debug,
		$str_event_action = "",
		$str_log_path_simple = "",
		$str_action_path_simple = "",
		$items = 0,
		$adds = 0,
		$edits = 0,
		$removes = 0,
		$skips = 0,
		$errors = 0,
		$str_event_dataset_brief = "",
		$str_event_dataset_full = "",
		$flag_success = 0,
		$flag_is_cron_job = 0
	) {
		#------------------------------------------------#
		# Record Event Log
		#------------------------------------------------#
		$queryEventLog = "
			INSERT INTO
				`dashboard_eventlogs`
				(
					`event_action`
					, `event_datetime`
					, `event_log_filepath`
					, `event_action_filepath`
					, `num_items`
					, `num_adds`
					, `num_edits`
					, `num_removes`
					, `num_skips`
					, `num_errors`
					, `event_dataset_brief`
					, `event_dataset_full`
					, `flag_success`
					, `flag_cron_job`
				)
				VALUES
				(
					'" . mysqli_real_escape_string($connString, $str_event_action) . "'
					, now()
					, '" . mysqli_real_escape_string($connString, $str_log_path_simple) . "'
					, '" . mysqli_real_escape_string($connString, $str_action_path_simple) . "'
					, " . $items . "
					, " . $adds . "
					, " . $edits . "
					, " . $removes . "
					, " . $skips . "
					, " . $errors . "
					, '" . mysqli_real_escape_string($connString, $str_event_dataset_brief) . "'
					, '" . mysqli_real_escape_string($connString, $str_event_dataset_full) . "'
					, $flag_success
					, $flag_is_cron_job
				)
		";

		if ($debug) {
			echo "<pre>queryEventLog = " . $queryEventLog . "</pre>";
		}
		else {
			$resultsEventLog = mysqli_query($connString, $queryEventLog) or
			die(mysqli_error($connString));
		}
	}
