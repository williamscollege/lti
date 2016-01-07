<?php
	// MySQL connection string

	// default database (determined by config settings)
	$connString = mysqli_connect(LTI_DB_HOST, LTI_DB_USERNAME, LTI_DB_PASSWORD, LTI_DB_NAME) or
	die("Sorry! You lack proper authentication to the live database.");

	// moodle specific database (determined by config settings)
	$moodle_connString = mysqli_connect(MOODLE_DB_HOST, MOODLE_DB_USERNAME, MOODLE_DB_PASSWORD, MOODLE_DB_NAME) or
	die("Sorry! You lack proper authentication to the live Moodle database.");
