<?php
	/***********************************************
	 ** Project:    "Dashboard for Automating Canvas Maintenance"
	 ** Author:      Williams College, OIT, David Keiser-Clark
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

