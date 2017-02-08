<?php
	/*
	 *  rating - Rating: an example LTI tool provider
	 *  Copyright (C) 2015  Stephen P Vickers
	 *
	 *  This program is free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 2 of the License, or
	 *  (at your option) any later version.
	 *
	 *  This program is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  You should have received a copy of the GNU General Public License along
	 *  with this program; if not, write to the Free Software Foundation, Inc.,
	 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
	 *
	 *  Contact: stephen@spvsoftwareproducts.com
	 *
	 *  Version history:
	 *    1.0.00   2-Jan-13  Initial release
	 *    1.0.01  17-Jan-13  Minor update
	 *    1.1.00   5-Jun-13  Added Outcomes service option
	 *    1.2.00  20-May-15  Changed to use class method overrides for handling LTI requests
	 *                       Added support for Content-Item message
	*/

	/*
	 * This page manages the definition of tool consumer records.  A tool consumer record is required to
	 * enable each VLE to securely connect to this application.
	 *
	 * *** IMPORTANT ***
	 * Access to this page should be restricted to prevent unauthorised access to the configuration of tool
	 * consumers (for example, using an entry in an Apache .htaccess file); access to all other pages is
	 * authorised by LTI.
	 * ***           ***
	*/

	/*
	 * File refactored by: David Keiser-Clark, Williams College (removed heredoc's, added bootstrap framework, added application variables)
	*/

	require_once(dirname(__FILE__) . '/lti_lib.php');

	// Initialise session and database
	$db = NULL;
	$ok = init($db, FALSE);
	// Initialise parameters
	$key = '';
	if ($ok) {
		// Create LTI Tool Provider instance
		$data_connector = LTI_Data_Connector::getDataConnector(LTI_DB_TABLENAME_PREFIX, $db);
		$tool           = new LTI_Tool_Provider($data_connector);
		// Check for consumer key and action parameters
		$action = '';
		if (isset($_REQUEST['key'])) {
			$key = $_REQUEST['key'];
		}
		if (isset($_REQUEST['do'])) {
			$action = $_REQUEST['do'];
		}

		// Process add consumer action
		if ($action == 'add') {
			$update_consumer          = new LTI_Tool_Consumer($key, $data_connector);
			$update_consumer->name    = $_POST['name'];
			$update_consumer->secret  = $_POST['secret'];
			$update_consumer->enabled = isset($_POST['enabled']);
			$date                     = $_POST['enable_from'];
			if (empty($date)) {
				$update_consumer->enable_from = NULL;
			}
			else {
				$update_consumer->enable_from = strtotime($date);
			}
			$date = $_POST['enable_until'];
			if (empty($date)) {
				$update_consumer->enable_until = NULL;
			}
			else {
				$update_consumer->enable_until = strtotime($date);
			}
			$update_consumer->protected = isset($_POST['protected']);
			// Ensure all required fields have been provided
			if (!empty($_POST['key']) && !empty($_POST['name']) && !empty($_POST['secret'])) {
				if ($update_consumer->save()) {
					$_SESSION['message'] = 'The consumer has been saved.';
				}
				else {
					$_SESSION['error_message'] = 'Unable to save the consumer; please check the data and try again.';
				}
				header('Location: ' . APP_ROOT_PATH . '/app_code/lti_manage_tool_consumers.php');
				exit;
			}

			// Process delete consumer action
		}
		else {
			if ($action == 'delete') {
				$consumer = new LTI_Tool_Consumer($key, $data_connector);
				if ($consumer->delete()) {
					$_SESSION['message'] = 'The consumer has been deleted.';
				}
				else {
					$_SESSION['error_message'] = 'Unable to delete the consumer; please try again.';
				}
				header('Location: ' . APP_ROOT_PATH . '/app_code/lti_manage_tool_consumers.php');
				exit;

			}
			else {
				// Initialise an empty tool consumer instance
				$update_consumer = new LTI_Tool_Consumer(NULL, $data_connector);
			}
		}

		// Fetch a list of existing tool consumer records
		$consumers = $tool->getConsumers();
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo LTI_APP_NAME; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo LTI_APP_NAME; ?>">
	<meta name="author" content="<?php echo LANG_AUTHOR_NAME; ?>">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo PATH_JQUERYUI_CSS; ?>" />
	<link rel="stylesheet" href="<?php echo APP_ROOT_PATH; ?>/css/wms-custom.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERYUI_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<!-- local JS -->
	<script src="<?php echo APP_ROOT_PATH; ?>/js/util.js"></script>
	<!-- Favicons -->
	<link rel="shortcut icon" href="<?php echo APP_ROOT_PATH; ?>/img/institution-favicon.ico" />
</head>
<body>
<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<div class="page-header">
				<h1><?php echo LTI_APP_NAME; ?></h1>
				<h5><?php echo LANG_INSTITUTION_NAME; ?>: Manage LTI tool consumers (LTI = Learning Tools Interoperability)</h5>

				<div id="breadCrumbs" class="small"><?php require_once(dirname(__FILE__) . '/../include/breadcrumbs.php'); ?></div>
			</div>

			<?php
				// Display warning message if access does not appear to have been restricted
				if (!(isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['REMOTE_USER']) && isset($_SERVER['PHP_AUTH_PW']))) {
					echo "<p class='text-danger'><strong>This page should be restricted to application administrators only.</strong></p>";
				}

				// Check for any messages to be displayed
				if (isset($_SESSION['error_message'])) {
					echo "<p style=\"font-weight: bold; color: #f00;\">ERROR: " . $_SESSION['error_message'] . "</p>";
					unset($_SESSION['error_message']);
				}

				if (isset($_SESSION['message'])) {
					echo "<p style=\"font-weight: bold; color: #00f;\">" . $_SESSION['message'] . "</p>";
					unset($_SESSION['message']);
				}

				// Display table of existing tool consumer records
				if ($ok) {
				if (count($consumers) <= 0) {
					echo "<p>No consumers have been added yet.</p>";
				}
				else {
					?>
					<table class="table table-condensed table-bordered table-hover">
						<thead>
						<tr class="bg-info">
							<th>Name</th>
							<th>Key</th>
							<th>Version</th>
							<th>Available?</th>
							<th>Protected?</th>
							<th title="dd-mm-yyyy">Last access</th>
							<th>Options</th>
						</tr>
						</thead>
						<tbody>
						<?php
							foreach ($consumers as $consumer) {
								$trkey = urlencode($consumer->getKey());
								if ($key == $consumer->getKey()) {
									$update_consumer = $consumer;
								}
								if (!$consumer->getIsAvailable()) {
									$available      = 'ban-circle';
									$available_alt  = 'Not available';
									$display_status = 'bg-danger';
								}
								else {
									$available      = 'ok';
									$available_alt  = 'Available';
									$display_status = '';
								}
								if ($consumer->protected) {
									$protected     = 'ok';
									$protected_alt = 'Protected';
								}
								else {
									$protected     = 'ban-circle';
									$protected_alt = 'Not protected';
								}
								if (is_null($consumer->last_access)) {
									$last = 'None';
								}
								else {
									$last = date('j-m-Y', $consumer->last_access);
								}
								?>
								<tr class="<?php echo $display_status; ?>">
									<td>
										<img src="<?php echo APP_ROOT_PATH; ?>/img/institution-logo-16.png" alt="<?php echo LANG_INSTITUTION_NAME; ?>" title="<?php echo LANG_INSTITUTION_NAME; ?>" />&nbsp;<?php echo $consumer->name; ?>
									</td>
									<td><?php echo $consumer->getKey(); ?></td>
									<td><span title="<?php echo $consumer->consumer_guid; ?>"><?php echo $consumer->consumer_version; ?></span></td>
									<td>
										<i class="glyphicon glyphicon-<?php echo $available; ?>" title="<?php echo $available_alt; ?>"></i>
									</td>
									<td>
										<i class="glyphicon glyphicon-<?php echo $protected; ?>" title="<?php echo $protected_alt; ?>"></i>
									</td>
									<td title="dd-mm-yyyy"><?php echo $last; ?></td>
									<td>
										<a href="<?php echo APP_ROOT_PATH; ?>/app_code/lti_manage_tool_consumers.php?key=<?php echo $trkey; ?>#edit" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-pencil"></i>&nbsp;Edit</a>&nbsp;&nbsp;
										<a href="<?php echo APP_ROOT_PATH; ?>/app_code/lti_manage_tool_consumers.php?do=delete&amp;key=<?php echo $trkey; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete consumer; are you sure?');"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</a>
									</td>
								</tr>
								<?php
							}
						?>
						</tbody>
					</table>
					<?php
				}

				// Display form for adding/editing a tool consumer
				if (isset($update_consumer->created)) {
					$mode = 'Update';
					$type = ' disabled="disabled"';
					$key1 = '';
					$key2 = 'key';
				}
				else {
					$mode = 'Add new';
					$type = '';
					$key1 = 'key';
					$key2 = '';
				}
				$name   = htmlentities($update_consumer->name);
				$key    = htmlentities($update_consumer->getKey());
				$secret = htmlentities($update_consumer->secret);
				if ($update_consumer->enabled) {
					$enabled = ' checked="checked"';
				}
				else {
					$enabled = '';
				}
				$enable_from = '';
				if (!is_null($update_consumer->enable_from)) {
					$enable_from = date('j-m-Y H:i', $update_consumer->enable_from);
				}
				$enable_until = '';
				if (!is_null($update_consumer->enable_until)) {
					$enable_until = date('j-m-Y H:i', $update_consumer->enable_until);
				}
				if ($update_consumer->protected) {
					$protected = ' checked="checked"';
				}
				else {
					$protected = '';
				}
			?>
			<h2><br /><a name="edit"></a><?php echo $mode; ?> consumer</h2>

			<form method="post" action="<?php echo APP_ROOT_PATH; ?>/app_code/lti_manage_tool_consumers.php" role="form" class="form-horizontal">
				<input type="hidden" name="do" value="add" />
				<input type="hidden" name="<?php echo $key2; ?>" value="<?php echo $key; ?>" />


				<div class="form-group">
					<label class="col-sm-2 control-label" for="name">Name *</label>

					<div class="col-sm-5">
						<input type="text" id="name" name="name" class="form-control input-sm" placeholder="Name" maxlength="50" value="<?php echo $name; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="<?php echo $key1; ?>">Key *</label>

					<div class=" col-sm-5">

						<input type="text" id="<?php echo $key1; ?>" name="<?php echo $key1; ?>" class="form-control input-sm" placeholder="Key" maxlength="50" value="<?php echo $key; ?>" <?php echo $type; ?> />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="secret">Secret *</label>

					<div class=" col-sm-5">
						<input type="text" id="secret" name="secret" class="form-control input-sm" placeholder="Secret" maxlength="200" value="<?php echo $secret; ?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-2 text-right">
						<label for="enabled">Enabled?</label>
					</div>
					<div class=" col-sm-5">
						<input type="checkbox" id="enabled" name="enabled" class="input" value="1" <?php echo $enabled; ?> />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="enable_from">Enable from</label>

					<div class=" col-sm-5">
						<input type="text" id="enable_from" name="enable_from" class="form-control input-sm" placeholder="dd-mm-yyyy hh:mm" maxlength="25" value="<?php echo $enable_from; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="enable_until">Enable until</label>

					<div class=" col-sm-5">
						<input type="text" id="enable_until" name="enable_until" class="form-control input-sm" placeholder="dd-mm-yyyy hh:mm" maxlength="25" value="<?php echo $enable_until; ?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-2 text-right">
						<label for="protected">Protected?</label>
					</div>
					<div class=" col-sm-5">
						<input type="checkbox" id="protected" name="protected" class="input" value="1" <?php echo $protected; ?> />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="btn_submit"></label>

					<div class=" col-sm-5">
						<p>
							<input type="submit" id="btn_submit" name="btn_submit" class="btn btn-primary" value="<?php echo $mode; ?> consumer" />
							<?php
								if (isset($update_consumer->created)) {
									echo "&nbsp;<input type=\"reset\" value=\"Cancel\" class=\"btn btn-link\" onclick=\"location.href='" . APP_ROOT_PATH . "/app_code/lti_manage_tool_consumers.php'\" />";
								}
							?>
						</p>
					</div>
				</div>

				<?php
					// close the big conditional statement ($ok)
					}
				?>
			</form>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
	<?php require_once(dirname(__FILE__) . '/../include/foot.php'); ?>
</div>
<!-- /.container -->
</body>
</html>
