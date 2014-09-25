<?php
	/*
	 *  rating - Rating: an example LTI tool provider
	 *  Copyright (C) 2013  Stephen P Vickers
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
	*/

	/*
	 * This page processes a launch request from an LTI tool consumer.
	 */

	require_once('lib.php');

	// Cancel any existing session
	session_name(SESSION_NAME);
	session_start();
	$_SESSION = array();
	session_destroy();

	// Initialise database
	$db = NULL;
	init($db);

	# Modification needed for local development work
	# init($db, FALSE);

	$data_connector = LTI_Data_Connector::getDataConnector(DB_TABLENAME_PREFIX, $db);
	$tool = new LTI_Tool_Provider('doLaunch', $data_connector);
	$tool->setParameterConstraint('oauth_consumer_key', TRUE, 50);
	$tool->setParameterConstraint('resource_link_id', TRUE, 50);
	$tool->setParameterConstraint('user_id', TRUE, 50);
	$tool->setParameterConstraint('roles', TRUE);
	$tool->execute();

	exit;

	###
	### Callback function to process a valid launch request.
	###
	function doLaunch($tool_provider) {

		// Check the user has an appropriate role
		if ($tool_provider->user->isLearner() || $tool_provider->user->isStaff()) {

			// Initialise the user session
			$_SESSION['consumer_key']      = $tool_provider->consumer->getKey();
			$_SESSION['resource_id']       = $tool_provider->resource_link->getId();
			$_SESSION['user_consumer_key'] = $tool_provider->user->getResourceLink()->getConsumer()->getKey();
			$_SESSION['user_id']           = $tool_provider->user->getId();
			$_SESSION['isStudent']         = $tool_provider->user->isLearner();

			// Redirect the user to display the list of items for the resource link
			return getAppUrl();

		}
		else {

			$tool_provider->reason = 'Invalid role.';
			return FALSE;

		}

	}

?>
