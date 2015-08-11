<?php

	// general utility functions

	function util_genRandomIdString($len = 128) {
		$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#%^&*()-_=+,.<>?~';
		$id   = '';
		for ($i = 0; $i < $len; $i++) {
			$id .= substr($pool, rand(0, strlen($pool) - 1), 1);
		}
		return $id;
	}

	function util_genRandomAlphNumString($len = 128) {
		$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$id   = '';
		for ($i = 0; $i < $len; $i++) {
			$id .= substr($pool, rand(0, strlen($pool) - 1), 1);
		}
		return $id;
	}

	function util_wipeSession() {
		unset($_SESSION['isAuthenticated']);
		unset($_SESSION['fingerprint']);
		unset($_SESSION['userdata']);
		unset($_SESSION['consumer_key']);
		unset($_SESSION['resource_id']);
		unset($_SESSION[APP_STR . '_id']);
		$_COOKIE[APP_STR . '_id'] = "";
		setcookie(APP_STR . "_id", "", time() - 3600); /* set the expiration date to one hour ago */
		return;
	}

	function util_redirectToAppHome($status = "", $msg_key_or_text = '', $log = 0) {
		// ensure value conforms to expectations
		if ($status != "success" && $status != "failure" && $status != "info") {
			# security: ensure status has a valid value
			header('Location: ' . APP_FOLDER . '/index.php');
			exit;
		}

		if ($log > 0) {
			# TODO: Add database log capability
		}

		header('Location: ' . APP_FOLDER . '/index.php?' . $status . '=' . urlencode($msg_key_or_text));
		exit;
	}


	function util_redirectToAppPage($page, $status = "", $msg_key_or_text = '', $log = 0) {
		// ensure value conforms to expectations
		if ($status != "success" && $status != "failure" && $status != "info") {
			# security: ensure status has a valid value
			header('Location: ' . APP_FOLDER . '/index.php?status=failure');
			exit;
		}

		if ($log > 0) {
			# TODO: Add database log capability
		}

		$joiner = '?';
		if (strpos($page, '?') > 0) {
			$joiner = '&';
		}
		header('Location: ' . APP_FOLDER . '/' . $page . $joiner . $status . '=' . urlencode($msg_key_or_text));
		exit;
	}


	function util_redirectToAppHomeWithPrejudice() {
		util_wipeSession();
		util_redirectToAppHome();
	}

	// this section adds and checks a random id string for the browser and does some checking against that ID string.
	// this makes it much harder to spoof sessions
	function util_doIdSecurityCheck() {
		if ((!isset($_COOKIE[APP_STR . '_id'])) || (!$_COOKIE[APP_STR . '_id'])) {
			if (isset($_SESSION[APP_STR . '_id']) && ($_SESSION[APP_STR . '_id'])) { // the session has an APP_STR id, but there was no cookie set for it - highly suspicious
				// TODO: log and/or message?
				util_wipeSession();
				util_redirectToAppPage('error.php?err=301', 'failure', 'msg_lti_failed_authentication');
				exit;
			}
			// set cookie
			$security_id = util_genRandomIdString(300);
			setcookie(APP_STR . '_id', $security_id, time() + 3600);  /* expire in 1 hour */
			$_SESSION[APP_STR . '_id'] = $security_id;
			$_COOKIE[APP_STR . '_id']  = $security_id;

			if (!setcookie(APP_STR . '_id', $security_id)) {
				// COULD NOT SET COOKIE!
				util_wipeSession();
				util_redirectToAppPage('error.php?err=302', 'failure', 'msg_lti_cannot_set_cookie');
				exit;
			}
		}
		elseif ((!isset($_SESSION[APP_STR . '_id'])) || ($_COOKIE[APP_STR . '_id'] != $_SESSION[APP_STR . '_id'])) {
			// there was an appropriately named cookie, but the value doesn't match the one associated with this session
			// TODO: log and/or message?
			util_wipeSession();
			util_redirectToAppPage('error.php?err=303', 'failure', 'msg_lti_failed_authentication');
			exit;
		}
	}

	function util_generateRequestFingerprint() {
		util_doIdSecurityCheck();

		return md5(FINGERPRINT_SALT . $_SESSION[APP_STR . '_id'] .
			(isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 18) : 'nouseragent')
		);
	}


	// a quick handle for a slightly complex condition check
	function util_checkAuthentication() {
		return (isset($_SESSION['isAuthenticated']) && ($_SESSION['isAuthenticated']));
	}


	function util_createDbConnection() {
		//print_r($_SERVER);
		//        TODO: figure out how to handle this for command line scripts (possibly build this directly into the command line header, but still need to resolve test vs live)
		//		if ((array_key_exists('SERVER_NAME',$_SERVER)) && ($_SERVER['SERVER_NAME'] == 'localhost')) {
		if ($_SERVER['SERVER_NAME'] == 'localhost') {
			return new PDO("mysql:host=" . TESTING_DB_SERVER . ";dbname=" . TESTING_DB_NAME . ";port=3306", TESTING_DB_USER, TESTING_DB_PASS);
		}
		return new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);
	}


	# validation routine: trim fxn strips (various types of) whitespace characters from the beginning and end of a string
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

	# Output debug info only when param is TRUE
	function util_debug($obj) {
		// set hardcoding value to: 1 for debugging, 0 for production server
		$debug = 1;
		if ($debug == 1) {
			echo "<pre>";
			print_r($obj);
			echo "</pre>";
		}
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


	/**
	 * @param $ts a time string of the form YYYY-MM-DD HH:MI:SS (i.e. as it comes from MySQL)
	 * @return that datetime formatted per the application's standard style
	 */
	function util_datetimeFormatted($ts) {
		$ts_info = util_processTimeString($ts);
		// return $ts_info['YYYY'] . '/' . $ts_info['MM'] . '/' . $ts_info['DD'] . ' ' . $ts_info['hh'] . ':' . $ts_info['mi'];
		return $ts_info['MM'] . '/' . $ts_info['DD'] . '/' . $ts_info['YYYY'] . ' ' . $ts_info['hh'] . ':' . $ts_info['mi'];
	}

	/**
	 * takes: a time string of the form YYYY-MM-DD HH:MI:SS (i.e. as it comes from MySQL)
	 * returns: a hash with the following keys-
	 * YYYY - the year
	 * Y - the year
	 * MM - the month with 2 characters (leading 0)
	 * M - the month with 1 character if < 10
	 * DD - the day with 2 characters
	 * D - the day with 1 character if < 10
	 * hh - the 24-clock hour with 2 characters
	 * h - the 24-clock hour with 1 character if < 10
	 * hhap - the 12-clock with 2 characters
	 * hap - the 12-clock with 1 character if < 10
	 * ap - AM or PM
	 * mi - the minutes with 2 characters
	 * m - the minutes with 1 character if < 10
	 * ss - the seconds with 2 characters
	 * s - the seconds with 1 character if < 10
	 */
	function util_processTimeString($ts) {
		$parts = preg_split('/[-: ]/', $ts);

		$res = [
			'YYYY' => $parts[0],
			'Y'    => $parts[0],
			'MM'   => $parts[1],
			'M'    => $parts[1],
			'DD'   => $parts[2],
			'D'    => $parts[2],
			'hh'   => $parts[3],
			'h'    => $parts[3],
			'hhap' => $parts[3],
			'hap'  => $parts[3],
			'ap'   => ($parts[3] < 12) ? 'AM' : 'PM',
			'mi'   => $parts[4],
			'm'    => $parts[4],
			'ss'   => $parts[5],
			's'    => $parts[5]
		];

		if ($res['hhap'] > 12) {
			$res['hhap'] -= 12;
		}
		if ($res['hhap'] < 1) {
			$res['hhap'] = '12';
		}
		if ($res['hap'] > 12) {
			$res['hap'] -= 12;
		}
		if ($res['hap'] < 1) {
			$res['hap'] = '12';
		}

		$res['M'] = preg_replace('/^0+/', '', $res['M']);

		$res['D'] = preg_replace('/^0+/', '', $res['D']);

		$res['h'] = preg_replace('/^0+/', '', $res['h']);
		if (!$res['h']) {
			$res['h'] = '0';
		}

		$res['hap'] = preg_replace('/^0+/', '', $res['hap']);
		if (!$res['hap']) {
			$res['hap'] = '0';
		}

		$res['m'] = preg_replace('/^0+/', '', $res['m']);
		if (!$res['m']) {
			$res['m'] = '0';
		}

		$res['s'] = preg_replace('/^0+/', '', $res['s']);
		if (!$res['s']) {
			$res['s'] = '0';
		}

		$res['date'] = $res['Y'] . '/' . $res['M'] . '/' . $res['D'];

		return $res;
	}

	function util_timeRangeString($tstart, $tstop) {
		if (!is_array($tstart)) {
			$tstart = util_processTimeString($tstart);
		}
		if (!is_array($tstop)) {
			$tstop = util_processTimeString($tstop);
		}

		$first_part  = $tstart['date'] . ' ' . $tstart['hap'] . ':' . $tstart['mi'];
		$second_part = '';

		if ($tstart['date'] != $tstop['date']) {
			$first_part .= ' ' . $tstart['ap'];
			$second_part = $tstop['date'] . ' ' . $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}
		elseif ($tstart['ap'] != $tstop['ap']) {
			$first_part .= ' ' . $tstart['ap'];
			$second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}
		else {
			$second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}

		return "$first_part-$second_part";
	}

	function util_displayMessage($type, $key_or_text) {
		$alert_type  = 'alert-info';
		$alert_title = util_lang('alert_info');
		if ($type == 'error') {
			$alert_type  = 'alert-danger';
			$alert_title = util_lang('alert_error');
		}
		else {
			if ($type == 'success') {
				$alert_type  = 'alert-success';
				$alert_title = util_lang('alert_success');
			}
		}

		$msg_text = util_lang($key_or_text);
		if (preg_match('/UNKNOWN LANGUAGE LABEL/', $msg_text)) {
			$msg_text = htmlentities($key_or_text, ENT_QUOTES, 'UTF-8');
		}

		echo "<div class=\"alert alert-dismissible $alert_type\" role=\"alert\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>";
		echo "<h4>$alert_title</h4>";
		echo $msg_text;
		echo "</div>";
	}

	function util_lang($label, $styling = '') {
		global $LANGUAGE, $CUR_LANG_SET;

		$ret = "UNKNOWN LANGUAGE LABEL '$label' FOR LANGUAGE '$CUR_LANG_SET'";

		if (array_key_exists($label, $LANGUAGE[$CUR_LANG_SET])) {
			$ret = $LANGUAGE[$CUR_LANG_SET][$label];
			if ($styling == 'properize') {
				$ret = ucwords($ret);
			}
			elseif ($styling == 'ucfirst') {
				$ret = ucfirst($ret);
			}
		}

		// util_prePrintR($ret);
		return $ret;
	}

	function util_listItemTag($id = '', $class_ar = [], $other_attr_hash = []) {
		$li = '<li';
		if ($id) {
			$li .= " id=\"$id\"";
		}
		if ($class_ar) {
			$li .= " class=\"" . implode(' ', $class_ar) . '"';
		}

		$hash_keys = array_keys($other_attr_hash);
		sort($hash_keys);
		foreach ($hash_keys as $k) {
			$v = $other_attr_hash[$k];
			$li .= " $k=\"$v\"";
		}
		$li .= '>';
		return $li;
	}

	// sanitizes the base name of a file (as opposed to the full path)
	// only allows alphanumeric, underscore, and non-consecutive .
	// extra . are stripped out, others are converted to _
	// NOTE: file names CAN be empty ('')
	function util_sanitizeFileName($fn) {
		$allowed_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_.';

		// echo "fn=$fn;<br />\n";
		while (preg_match('/\\.\\./', $fn)) {
			$fn = preg_replace('/\\.\\./', '', $fn);
			// echo "fn=$fn;<br />\n";
		}
		if (!$fn) {
			return '';
		}

		$fn_chars = str_split($fn);
		// util_prePrintR($fn_chars);
		$cleaned = '';
		foreach ($fn_chars as $fnc) {
			if (strpos($allowed_chars, $fnc) === FALSE) {
				$cleaned .= '_';
			}
			else {
				$cleaned .= $fnc;
			}
		}

		return $cleaned;
	}

	// sanitizes the a file referenced by a path
	function util_sanitizeFileReference($fr) {
		while (preg_match('/\\.\\.\\//', $fr)) {
			$fr = preg_replace('/\\.\\.\\//', '', $fr);
		}

		$fr_parts     = explode('/', $fr);
		$cleaned_fr   = '';
		$part_counter = 0;
		foreach ($fr_parts as $frp) {
			if ($part_counter > 0) {
				$cleaned_fr .= '/';
			}
			$cleaned_fr .= util_sanitizeFileName($frp);
			$part_counter++;
		}

		return $cleaned_fr;
	}

	function util_coordsMapLink($longitude, $latitude, $zoom = 19) {
		if (!is_numeric($longitude)) {
			return util_lang('longitude') . ' ' . util_lang('invalid_value');
		}
		if (!is_numeric($latitude)) {
			return util_lang('latitude') . ' ' . util_lang('invalid_value');
		}
		if (!is_numeric($zoom)) {
			return util_lang('zoom_level') . ' ' . util_lang('invalid_value');
		}
		return "http://maps.google.com/maps?q=$latitude,$longitude+(point)&z=$zoom&ll=$latitude,$longitude";
	}

	function util_startsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	function util_endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return TRUE;
		}

		return (substr($haystack, -$length) === $needle);
	}

	function util_getValueForCheckboxRequestData($fieldName) {
		if (isset($_REQUEST[$fieldName]) && $_REQUEST[$fieldName] == 'on') {
			return 1;
		}
		return 0;
	}

	// create event log. [requires: user_id(int), flag_success(bool), event_action(varchar), event_action_id(int), event_action_target_type(varchar), event_note(varchar), event_dataset(varchar)]
	function util_createEventLog($user_id = 0, $flag_success = FALSE, $event_action = "", $event_action_id = 0, $event_action_target_type = "", $event_note = "", $event_dataset = "", $DB) {
		$eventlog = SUS_EventLog::createNewEventLog($user_id, $flag_success, $event_action, $event_action_id, $event_action_target_type, $event_note, $event_dataset, $DB);
		$eventlog->updateDb();
		if (!$eventlog->matchesDb) {
			$evtlog_error = new SUS_EventLog([
				'DB'                         => $DB
				, 'user_id'                  => $user_id
				, 'flag_success'             => $flag_success
				, 'event_action'             => $event_action
				, 'event_action_id'          => $event_action_id
				, 'event_action_target_type' => $event_action_target_type
				, 'event_note'               => "Could not create event log for this action." . substr($event_note, 0, 1990)    // truncate to avoid exceeding db field limit
				, 'event_dataset'            => substr($event_dataset, 0, 1990)                // truncate to avoid exceeding db field limit
				, 'event_filepath'           => substr($_SERVER["REQUEST_URI"], 0, 990)        // truncate to avoid exceeding db field limit
				, 'user_agent_string'        => substr($_SERVER["HTTP_USER_AGENT"], 0, 990)    // truncate to avoid exceeding db field limit
			]);
			$evtlog_error->updateDb();
		}
		return TRUE;
	}
