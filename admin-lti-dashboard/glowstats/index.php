<?php
	/***********************************************
	 ** Project:    Glowstats
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:
	 ** - View all available Glow LMS statistics and analytics
	 ** - Versions: Canvas 2013-present, Moodle 2010-2013, Blackboard 2003-2010
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, dom
	 ***********************************************/

	require_once(dirname(__FILE__) . '/../institution.cfg.php');

	header("Location: " . APP_ROOT_PATH . "/glowstats/moodle-courses-per-semester.php");
