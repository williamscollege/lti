<?php
	// MySQL connection string

	// database (determined by config settings)
	$connString = mysqli_connect(LTI_DB_HOST, LTI_DB_USERNAME, LTI_DB_PASSWORD, LTI_DB_NAME) or
	die("Sorry! You lack proper authentication to the live database.");

