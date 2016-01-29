<?php
	/***********************************************
	 ** Project:    Upload Avatar Image Files to Canvas
	 ** Author:     Williams College, OIT, David Keiser-Clark
	 ** Purpose:    Upload Avatar Image Files to Canvas' AWS Cloud using curl calls
	 ** Requirements:
	 **  - Requires admin token to make curl requests against Canvas LMS API
	 **  - Must enable write-access to "logs/" folder
	 **  - Lock down parent releases folder to only allow administrator to access/view/run files
	 **  - delay execution to not exceed Canvas limit of 3000 API requests per hour (http://www.instructure.com/policies/api-policy)
	 **  - extend the typical "max_execution_time" to require as much time as the script requires (without timing out)
	 **  - Run daily using cron job
	 ** Current features:
	 **  - fetch all local Dashboard users where flag_is_set_avatar_image = 0 (these users have not yet had their avatar profile image uploaded to AWS)
	 **  - confirm that each user still lacks an avatar profile image in AWS Cloud environment
	 **  - if user lacks avatar image: check if image exists on local server and temporarily copy image to public facing directory
	 **  - attempt to post user image using individual curl calls
	 **  - determine if curl POST was success or failure
	 **  - show script start and end times to help document that this script is keeping within Canvas number of API requests/hour
	 **  - remove all images from temporary public facing directory
	 **  - report: Log Summary output to database
	 ** Dependencies:
	 **  - Install: Apache, PHP 5.2 (or higher)
	 **  - Enable PHP modules: PDO, mysqli, curl, mbyte, dom
	 ***********************************************/

	# Extend default script timeout to be unlimited (typically default is 300 seconds, from php.ini settings)
	ini_set('MAX_EXECUTION_TIME', -1);
	ini_set('MAX_INPUT_TIME', -1);
	if (ob_get_level() == 0) {
		ob_start();
	}

	require_once(dirname(__FILE__) . '/dashboard_institution.cfg.php');
	require_once(dirname(__FILE__) . '/dashboard_connDB.php');
	require_once(dirname(__FILE__) . '/dashboard_util.php');
	require_once(dirname(__FILE__) . '/dashboard_curl_functions.php');


	#------------------------------------------------#
	# Security: Prevent web access to this file
	#------------------------------------------------#
	if (array_key_exists('SERVER_NAME', $_SERVER)) {
		exit;        // prevent script from running as a web application
	}
	else {
		// script ran via server commandline, not as web application
		$str_action_path_simple = dirname(__FILE__) . "/" . basename(__FILE__);
		$flag_is_cron_job       = 1; // TRUE
	}


	#------------------------------------------------#
	# IMPORTANT STEPS TO REMEMBER
	#------------------------------------------------#
	# Run PHP file: (1) daily from server via cron job
	# Set and show debugging browser output (on=TRUE, off=FALSE)
	$debug = FALSE;
	# Set flag for whether to overwrite, or skip overwritting, pre-existing AWS Cloud images (Overwrite=TRUE, do not overwrite = FALSE)
	$boolOverwriteCloudImage = FALSE; // careful, setting this to TRUE will overwrite pre-existing AWS Cloud images (ie a custom image that the user uploaded)


	#------------------------------------------------#
	# Constants: Initialize counters
	#------------------------------------------------#
	$str_project_name            = "Commandline: Upload Avatar Image Files to Canvas";
	$str_event_action            = "upload_avatars_to_canvas_aws_cloud";
	$str_log_path_simple         = 'n/a';
	$image_path_copy_from        = "/srv/www/lighttpd/images-glow-live2/";
	$image_path_copy_to          = "/var/www/lighttpd/images-glow-temp/";
	$arrayLocalUsers             = array();
	$boolValidResult             = TRUE;
	$strUIDsAvatarAdded          = "";
	$strUIDsUpdated              = "";
	$strUIDsErrors               = "";
	$intCountUploadStatusPending = 0;
	$intCountUploadStatusReady   = 0;
	$intCountUploadedAvatar      = 0;
	$intCountCurlAPIRequests     = 0;
	$intCountAvatarExists        = 0;
	$intCountNeedsUpdate         = 0;
	$intCountAdds                = 0;
	$intCountEdits               = 0;
	$intCountRemoves             = 0;
	$intCountSkips               = 0;
	$intCountErrors              = 0;

	# Save initial values for "LOG SUMMARY"
	$beginDateTime       = date('YmdHis');
	$beginDateTimePretty = date('Y-m-d H:i:s');


	#------------------------------------------------#
	# SQL Purpose: Fetch simple count of users that already have cloud based avatars
	#------------------------------------------------#
	$queryItems = "
		SELECT *
		FROM `dashboard_users`
		WHERE
			`flag_is_set_avatar_image` = TRUE
		AND `flag_delete` = FALSE
	";
	if ($debug) {
		echo "<hr /><pre>queryItems = " . $queryItems . "</pre>";
	}
	$resultsItems = mysqli_query($connString, $queryItems) or
	die(mysqli_error($connString));

	# Store value
	$intCountAvatarExists = mysqli_num_rows($resultsItems);
	if ($debug) {
		echo "<hr />intCountAvatarExists = " . $intCountAvatarExists . "<br />";
	}


	#------------------------------------------------#
	# SQL Purpose: fetch all local `dashboard_users`
	# requirement: flag_is_set_avatar_image = 0 (not set)
	# requirement: flag_delete = 0 (active)
	#------------------------------------------------#
	$queryLocalUsers = "
		SELECT *
		FROM `dashboard_users`
		WHERE
			`flag_is_set_avatar_image` = FALSE
		AND `flag_delete` = FALSE
		ORDER BY `canvas_user_id` ASC;
	";
	$resultsLocalUsers = mysqli_query($connString, $queryLocalUsers) or
	die(mysqli_error($connString));

	# Store all in permanent array
	while ($usr = mysqli_fetch_assoc($resultsLocalUsers)) {
		array_push($arrayLocalUsers, $usr);
	}
	if ($debug) {
		echo "<hr/>arrayLocalUsers:<br />";
		echo "count of arrayLocalUsers: " . count($arrayLocalUsers) . "<br />";
		echo "(example: arrayLocalUsers[0][\"canvas_user_id\"] is: " . $arrayLocalUsers[0]["canvas_user_id"] . ")<br />";
		util_prePrintR($arrayLocalUsers);
		echo "<hr/>";
	}


	#------------------------------------------------#
	# Iterate all Local Users
	#------------------------------------------------#
	foreach ($arrayLocalUsers as $local_usr) {

		// reset flag for each user
		$boolValidResult = TRUE;

		if ($debug) {
			if ($intCountCurlAPIRequests == 4) {
				// for testing, set a tiny max limiter for quick tests (total users = approx 6600)
				echo "<br />DEBUGGING NOTE: Script forced to stop after curl request # " . $intCountCurlAPIRequests . " completed.";
				break;
			}
		}

		#------------------------------------------------#
		# Step 1: Fetch "Avatar Options" for this user (skip users that already have uploaded a cloud based avatar image)
		#------------------------------------------------#
		$arrayCurlResult = curlFetchUserAvatarOptions(
			$local_usr["canvas_user_id"],
			$apiPathPrefix = "api/v1/users/self/",
			$apiPathEndpoint = "avatars?as_user_id="
		);

		# increment counter
		$intCountCurlAPIRequests += 1;

		if ($debug) {
			echo "<hr />Step 1: curlFetchUserAvatarOptions for user: " . $local_usr["canvas_user_id"] . ", returned:<br />";
			util_prePrintR($arrayCurlResult);
		}

		# iterate through each of the many profile images associated with this user's account
		foreach ($arrayCurlResult as $item) {
			# check for curl error
			if ($item == "errors" || $item == "unauthorized") {
				$boolValidResult = FALSE;

				# increment counter
				$intCountErrors += 1;

				# Store list
				$strUIDsErrors .= empty($strUIDsErrors) ? $local_usr["canvas_user_id"] . ": curl error: " . $item : ", " . $local_usr["canvas_user_id"] . ": curl error: " . $item;

				if ($debug) {
					echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Error and skipped: curl reported error<br />";
				}
			}

			if ($boolValidResult) {
				# does this user already have a valid cloud based avatar?
				if ($boolOverwriteCloudImage == FALSE AND isset($item["thumbnail_url"])) {
					# a valid thumbnail_url already exists for this user; edit flag to avoid updating this user's avatar
					if ($debug) {
						echo "<br />Live cloud based avatar already exists for canvas_user_id " . $local_usr["canvas_user_id"] . ". Skipping to next user.<br />";
					}

					#------------------------------------------------#
					# SQL Purpose: Curl was successful. Update Dashboard local db to reflect this action has been completed
					# requirement: `flag_is_set_avatar_image` = 1 (set)
					#------------------------------------------------#
					$queryEditLocalUser = "
						UPDATE
							`dashboard_users`
						SET
							`flag_is_set_avatar_image` = TRUE
						WHERE
							`canvas_user_id` = " . $local_usr["canvas_user_id"] . "
					";

					if ($debug) {
						echo "<pre>queryEditLocalUser = " . $queryEditLocalUser . "</pre>";
					}
					else {
						$resultsEditLocalUser = mysqli_query($connString, $queryEditLocalUser) or
						die(mysqli_error($connString));
					}

					# increment counter
					$intCountEdits += 1;

					$boolValidResult = FALSE;

					# Store list
					$strUIDsUpdated .= empty($strUIDsUpdated) ? $local_usr["canvas_user_id"] : ", " . $local_usr["canvas_user_id"];

					if ($debug) {
						echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - Edited and skipped: User already has cloud based avatar<br />";
					}
				}
			}
		} // end: iterate through each of the many profile images associated with this user's account

		if ($boolValidResult) {
			#------------------------------------------------#
			# Check if image file exists that corresponds to this user's username
			#------------------------------------------------#
			if (file_exists($image_path_copy_from . $local_usr["username"] . ".jpg")) {

				# copy file to public web directory
				$file_copy = copy($image_path_copy_from . $local_usr["username"] . ".jpg", $image_path_copy_to . $local_usr["canvas_user_id"] . ".jpg");

				#------------------------------------------------#
				# Step 2: Upload Image via POST by HTTPS as no avatar image currently exists for this user (image must be publicly accessible/viewable)
				#------------------------------------------------#
				$jsonUploadImageToCloud = curlUploadImageToCloud(
					$local_usr["canvas_user_id"],
					$apiPathPrefix = "api/v1/users/self/",
					$apiPathEndpoint = "files?as_user_id="
				);

				if ($debug) {
					echo "<br />Step 2: jsonUploadImageToCloud for user: " . $local_usr["canvas_user_id"] . ", returned:<br />";
					util_prePrintR($jsonUploadImageToCloud);
				}

				# increment counter
				$intCountCurlAPIRequests += 1;

				# check status of upload (this step is optional, requires more time, but ensures more predictable results
				if ($jsonUploadImageToCloud["upload_status"] == "pending") {
					$jsonUploadStatus = array();

					#------------------------------------------------#
					# Step 3: Check status of upload (MUST use entire 'status_url' (including file number and opaque string) from previous curl post)
					#------------------------------------------------#
					$jsonUploadStatus = curlUploadStatus(
						$apiFullPath = $jsonUploadImageToCloud["status_url"]
					);

					if ($debug) {
						echo "<br />Step 3: status_url is: "; //= " . $jsonUploadImageToCloud["status_url"];
						util_prePrintR($jsonUploadStatus);
					}

					# increment counter
					$intCountCurlAPIRequests += 1;

					# define flag for "pause" status for current user
					$boolDoPause = TRUE;

					# Wait Condition: Continue only after a successful response is returned from Canvas
					while ($jsonUploadStatus["upload_status"] == "pending") {
						$jsonUploadStatus = curlUploadStatus(
							$apiFullPath = $jsonUploadImageToCloud["status_url"]
						);

						if ($debug) {
							echo "<br />Step 3: Wait Condition: 'upload_status' "; //= " . $jsonUploadStatus["upload_status"]; // debugging
							util_prePrintR($jsonUploadStatus);
						}

						# increment counters
						$intCountCurlAPIRequests += 1;
						$intCountUploadStatusPending += 1;

						# delay execution to enable Canvas to resolve file upload
						# delay execution to not exceed Canvas limit of 3000 API requests per hour (http://www.instructure.com/policies/api-policy)
						# flush output to screen, set boolean variable to now be FALSE
						$boolDoPause = util_sleepFlushContent();
					}

					# upload_status = "ready"
					if ($boolDoPause) {
						# delay execution a minimum of time per user
						# flush output to screen, set boolean variable to now be FALSE
						$boolDoPause = util_sleepFlushContent();
					}

					if ($debug) {
						echo "<br />Step 3: jsonUploadStatus, returned:<br />";
						util_prePrintR($jsonUploadStatus);
					}

					# increment counter
					$intCountUploadStatusReady += 1;

					#------------------------------------------------#
					# Step 4: Fetch "Avatar Options" for this user (GET 'opaque_token' for the just-uploaded 'profile_pic.jpg')
					#------------------------------------------------#
					$jsonUserAvatarListToken = curlFetchUserAvatarOptions(
						$local_usr["canvas_user_id"],
						$apiPathPrefix = "api/v1/users/self/",
						$apiPathEndpoint = "avatars?as_user_id="
					);

					if ($debug) {
						echo "<br />STEP 4: jsonUserAvatarListToken (get Opaque Token), returned:";
						util_prePrintR($jsonUserAvatarListToken);
						echo "<br />STEP 4: Wanting to see value of retrieved opaqueToken on next line!!!<br />";
					}

					# increment counter
					$intCountCurlAPIRequests += 1;

					$opaqueToken = "";
					foreach ($jsonUserAvatarListToken as $file) {
						if (isset($file["thumbnail_url"]) && $file["display_name"] == "profile_pic.jpg") {
							# fetch the token from the newly uploaded image
							$opaqueToken = $file["token"];

							if ($debug) {
								echo "<br />STEP 4: opaqueToken for user #" . $local_usr["canvas_user_id"] . " newly uploaded avatar: is " . $opaqueToken . "<br />";
							}
						}
					}

					#------------------------------------------------#
					# Step 5: Use the retrieved 'opaque_token' to "Update User Settings" (Set new avatar image by using 'opaque_token' for 'profile_pic.jpg')
					#------------------------------------------------#
					$jsonConfirmImageUpload = curlConfirmImageUpload(
						$local_usr["canvas_user_id"],
						$apiPathPrefix = "api/v1/users/self/",
						$apiPathEndpoint = "?as_user_id=",
						$opaqueToken
					);

					if ($debug) {
						echo "<br />STEP 5: Confirm Image Upload process (send the previously retrieved Opaque Token)";
						util_prePrintR($jsonConfirmImageUpload);
						echo "\n" . $local_usr["canvas_user_id"] . " - Uploaded new avatar image file<br />\n";
					}

					#------------------------------------------------#
					# SQL Purpose: Curl was successful. Update Dashboard local db to reflect this action has been completed
					# requirement: `flag_is_set_avatar_image` = 1 (set)
					#------------------------------------------------#
					$queryEditLocalUser = "
						UPDATE
							`dashboard_users`
						SET
							`flag_is_set_avatar_image` = TRUE
						WHERE
							`canvas_user_id` = " . $local_usr["canvas_user_id"] . "
					";

					if ($debug) {
						echo "<pre>queryEditLocalUser = " . $queryEditLocalUser . "</pre>";
					}
					else {
						$resultsEditLocalUser = mysqli_query($connString, $queryEditLocalUser) or
						die(mysqli_error($connString));
					}

					# Store list
					$strUIDsAvatarAdded .= empty($strUIDsAvatarAdded) ? $local_usr["canvas_user_id"] : ", " . $local_usr["canvas_user_id"];

					# increment counter
					$intCountCurlAPIRequests += 1;
					$intCountUploadedAvatar += 1;
				}
			}
			else {
				$boolValidResult = FALSE;

				# increment counter
				$intCountSkips += 1;

				if ($debug) {
					echo $local_usr["canvas_user_id"] . " - " . $local_usr["sortable_name"] . " - File did not exist on server (" . $local_usr["username"] . ".jpg" . ")<br />";
				}
			}
		}


	} // end: iterate all Local Users

	#------------------------------------------------#
	# Remove all temporary files from "images-glow-temp" directory
	#------------------------------------------------#
	array_map('unlink', glob($image_path_copy_to . "*.jpg"));


	#------------------------------------------------#
	# Report: LOG SUMMARY
	#------------------------------------------------#
	// formatting
	if ($debug) {
		echo "<br /><hr />\n";
	}

	# store values
	$endDateTime         = date('YmdHis');
	$endDateTimePretty   = date('Y-m-d H:i:s');
	$intCountNeedsUpdate = count($arrayLocalUsers);

	$finalReport = array();
	array_push($finalReport, "Date begin: " . $beginDateTimePretty);
	array_push($finalReport, "Date end: " . $endDateTimePretty);
	array_push($finalReport, "Duration: " . convertSecondsToHMSFormat(strtotime($endDateTime) - strtotime($beginDateTime)) . " (hh:mm:ss)");
	array_push($finalReport, "Curl API Requests: " . $intCountCurlAPIRequests);
	array_push($finalReport, "Count: Canvas users without avatar: " . $intCountNeedsUpdate);
	array_push($finalReport, "Count: Canvas users upload_status=pending (file uploading: system busy): " . $intCountUploadStatusPending);
	array_push($finalReport, "Count: Canvas users upload_status=ready (file uploaded: awaiting token): " . $intCountUploadStatusReady);
	array_push($finalReport, "Count: Canvas users upload_status=confirmed (file uploaded: completed!): " . $intCountUploadedAvatar);
	array_push($finalReport, "Count: Canvas users skipped (pre-existing avatar): " . $intCountAvatarExists);
	array_push($finalReport, "Count: Canvas users skipped (error): " . $intCountErrors);
	array_push($finalReport, "Count: Updated Dashboard (Canvas had newer data): " . $intCountEdits);
	array_push($finalReport, "List Canvas UIDs: Added avatars: " . $strUIDsAvatarAdded);
	array_push($finalReport, "List Canvas UIDs: User updated own avatar: " . $strUIDsUpdated);
	array_push($finalReport, "List Canvas UIDs: Curl reported errors: " . $strUIDsErrors);
	array_push($finalReport, "Archived file: " . $str_log_path_simple);
	array_push($finalReport, "Project: " . $str_project_name);

	# Stringify for browser, output to txt file
	$firstTimeFlag          = TRUE;
	$str_event_dataset_full = "";

	foreach ($finalReport as $obj) {
		if ($firstTimeFlag) {
			# formatting (first iteration)
			if ($debug) {
				echo "LOG SUMMARY<br />";
			}

			# formatting: first row of db entry will be bolded for later web use
			$str_event_dataset_full .= "<strong>" . $obj . "</strong><br />";
		}
		else {
			$str_event_dataset_full .= $obj . "<br />";
		}

		# reset flag
		$firstTimeFlag = FALSE;
	}

	if ($debug) {
		echo "<hr />\n\n";
		echo $str_event_dataset_full;
	}


	#------------------------------------------------#
	# Record Event Log
	#------------------------------------------------#
	// set values dynamically
	$str_event_dataset_brief = $intCountUploadedAvatar . " uploaded, " . $intCountEdits . " edited, " . $intCountErrors . " errors";

	create_eventlog(
		$connString,
		$debug,
		mysqli_real_escape_string($connString, $str_event_action),
		mysqli_real_escape_string($connString, $str_log_path_simple),
		mysqli_real_escape_string($connString, $str_action_path_simple),
		$intCountNeedsUpdate,
		$intCountUploadedAvatar,
		$intCountEdits,
		$intCountRemoves,
		$intCountSkips,
		$intCountErrors,
		mysqli_real_escape_string($connString, $str_event_dataset_brief),
		mysqli_real_escape_string($connString, $str_event_dataset_full),
		$flag_success = ($intCountErrors == 0) ? 1 : 0,
		$flag_is_cron_job
	);

	// final script status
	echo "done!";

	#------------------------------------------------#
	# End: Avoid hitting the default script timeout of 300 or 720 seconds (depending on default php.ini settings)
	#------------------------------------------------#
	ob_end_flush();
