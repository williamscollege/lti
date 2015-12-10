<?php
	/***********************************************
	 ** Project:    "Bulk Push Avatar Image Files" using publicly available HTTPS image file sources
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ***********************************************/


	# Extend default script timeout to be unlimited (typically default is 300 seconds, from php.ini settings)
	ini_set('MAX_EXECUTION_TIME', -1);
	ini_set('MAX_INPUT_TIME', -1);
	if (ob_get_level() == 0) {
		ob_start();
	}

	# Set timezone to keep php from complaining
	date_default_timezone_set('America/New_York');

	# Store value for "Final status report"
	$startDateTime       = date('YmdHis');
	$startDateTimePretty = date('Y-m-d H:i:s');

	# Create new log file (including datetime stamp) as archival record
	$newFileName = "/logs/" . date("Ymd-His") . "-log-report.txt";
	$myLogFile = fopen(".." . $newFileName, "w") or die("Unable to open file!");

	# 21,600 seconds = 6 hours
	// ... do long running stuff
	for ($i = 0; $i < 4; $i++) {

		$txt = "<br>" . $i . " seconds";

		# create output for browser
		echo $txt;
		# write to log file
		fwrite($myLogFile, $txt);

		# delay execution
		sleep(1);

		# flush output to browser
		ob_flush(); // flush (send) the output buffer; to flush the ob output buffers, you will have to call both ob_flush() and flush()
		flush(); // flush system output buffer; to flush the ob output buffers, you will have to call both ob_flush() and flush()
		set_time_limit(0); // restarts the timeout counter from zero
	}

	# FAKE VALUES for testing output, below
	$intCountCurlAPIRequests     = 44;
	$intCountUsers               = 4;
	$intCountErrors              = 1;
	$intCountAvatarExists        = 2;
	$intCountUploadStatusPending = 0;
	$intCountUploadStatusReady   = 4;
	$intCountUploadedAvatar      = 4;

	# Store value for "Final status report"
	$endDateTime       = date('YmdHis');
	$endDateTimePretty = date('Y-m-d H:i:s');

	# Final status report
	$finalReport = array();
	array_push($finalReport, "Project: 'Bulk Push Avatar Image Files'");
	array_push($finalReport, "Date started: " . $startDateTimePretty);
	array_push($finalReport, "Date ended: " . $endDateTimePretty);
	array_push($finalReport, "Duration (hh:mm:ss): " . convertSecondsToHMSFormat(strtotime($endDateTime) - strtotime($startDateTime)));
	array_push($finalReport, "Curl API Requests: " . $intCountCurlAPIRequests);
	array_push($finalReport, "User Count: " . $intCountUsers);
	array_push($finalReport, "Users skipped (error): " . $intCountErrors);
	array_push($finalReport, "Users skipped (pre-existing avatar): " . $intCountAvatarExists);
	array_push($finalReport, "Users upload_status = 'pending': " . $intCountUploadStatusPending . " (waiting, system busy)");
	array_push($finalReport, "Users upload_status = 'ready': " . $intCountUploadStatusReady . " (should match 'confirmed')");
	array_push($finalReport, "Users upload_status = 'confirmed': " . $intCountUploadedAvatar . " (files uploaded)");
	array_push($finalReport, "Archived file: " . $newFileName);

	# Output array to browser and txt file
	$firstTimeFlag = TRUE;
	foreach ($finalReport as $obj) {
		if ($firstTimeFlag) {
			# formatting (first iteration)
			echo "<br /><hr />FINAL STATUS REPORT<br /><br />";
			fwrite($myLogFile, "\n\n------------------------------\nFINAL STATUS REPORT\n\n");
		}
		echo $obj . "<br />";
		fwrite($myLogFile, $obj . "\n");

		# reset flag
		$firstTimeFlag = FALSE;
	}
	# formatting (last iteration)
	echo "<hr />";
	fwrite($myLogFile, "\n------------------------------\n\n");


	# Close log file
	fclose($myLogFile);

	# End: Avoid hitting the default script timeout of 300 or 720 seconds (depending on default php.ini settings)
	ob_end_flush();


	# this would go in a util.php file...
	# Convert seconds to Hour:Minute:Second format
	function convertSecondsToHMSFormat($seconds) {
		$t = round($seconds);
		return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
	}