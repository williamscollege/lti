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
	 * This page provides functions for accessing the database.
	 */

	require_once('config.php');
	require_once(dirname(__FILE__) . '/' . LTI_FOLDER . 'LTI_Tool_Provider.php');

	# Modification needed for local development work
	# require_once(dirname(__FILE__) . '\\' . LTI_FOLDER . '\LTI_Tool_Provider.php');
	# echo dirname(__FILE__) . '\\' . LTI_FOLDER . '\LTI_Tool_Provider.php';

	###
	###  Return a connection to the database, return FALSE if an error occurs
	###
	function open_db() {

		try {
			$db = new PDO(LTI_DB_NAME, LTI_DB_USERNAME, LTI_DB_PASSWORD);
		}
		catch (PDOException $e) {
			$db                        = FALSE;
			$_SESSION['error_message'] = "Database error {$e->getCode()}: {$e->getMessage()}";
		}

		return $db;

	}


	###
	###  Create any missing database tables (only for MySQL and SQLite databases)
	###
	function init_db($db) {

		$db_type = '';
		$pos     = strpos(LTI_DB_NAME, ':');
		if ($pos !== FALSE) {
			$db_type = strtolower(substr(LTI_DB_NAME, 0, $pos));
		}

		$prefix = LTI_DB_TABLENAME_PREFIX;
		if (($db_type == 'mysql') || ($db_type == 'sqlite')) {

			// Adjust for different syntax of autoincrement columns
			if ($db_type == 'sqlite') {
				$sql = "CREATE TABLE IF NOT EXISTS {$prefix}item (" .
					'item_id INTEGER PRIMARY KEY AUTOINCREMENT, ' .
					'consumer_key varchar(50) NOT NULL, ' .
					'resource_id varchar(50) NOT NULL, ' .
					'item_title varchar(200) NOT NULL, ' .
					'item_text text, ' .
					'item_url varchar(200) DEFAULT NULL, ' .
					'max_rating int(2) NOT NULL DEFAULT \'5\', ' .
					'step int(1) NOT NULL DEFAULT \'1\', ' .
					'visible tinyint(1) NOT NULL DEFAULT \'0\', ' .
					'sequence int(3) NOT NULL DEFAULT \'0\', ' .
					'created datetime NOT NULL, ' .
					'updated datetime NOT NULL)';
			}
			else {
				$sql = "CREATE TABLE IF NOT EXISTS {$prefix}item (" .
					"item_id int(11) NOT NULL AUTO_INCREMENT," .
					'consumer_key varchar(50) NOT NULL, ' .
					'resource_id varchar(50) NOT NULL, ' .
					'item_title varchar(200) NOT NULL, ' .
					'item_text text, ' .
					'item_url varchar(200) DEFAULT NULL, ' .
					'max_rating int(2) NOT NULL DEFAULT \'5\', ' .
					'step int(1) NOT NULL DEFAULT \'1\', ' .
					'visible tinyint(1) NOT NULL DEFAULT \'0\', ' .
					'sequence int(3) NOT NULL DEFAULT \'0\', ' .
					'created datetime NOT NULL, ' .
					'updated datetime NOT NULL, ' .
					'PRIMARY KEY (item_id))';
			}
			$ok = $db->exec($sql) !== FALSE;

			if ($ok) {
				$sql = "CREATE TABLE IF NOT EXISTS {$prefix}rating (" .
					'item_id int(11) NOT NULL, ' .
					'consumer_key varchar(50) NOT NULL, ' .
					'user_id varchar(50) NOT NULL, ' .
					'rating decimal(10,2) NOT NULL, ' .
					'PRIMARY KEY (item_id,consumer_key,user_id))';
				$ok  = $db->exec($sql) !== FALSE;
			}

			if ($ok) {
				$sql = "CREATE TABLE IF NOT EXISTS {$prefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' (' .
					'consumer_key varchar(50) NOT NULL, ' .
					'name varchar(45) NOT NULL, ' .
					'secret varchar(32) NOT NULL, ' .
					'lti_version varchar(12) DEFAULT NULL, ' .
					'consumer_name varchar(255) DEFAULT NULL, ' .
					'consumer_version varchar(255) DEFAULT NULL, ' .
					'consumer_guid varchar(255) DEFAULT NULL, ' .
					'css_path varchar(255) DEFAULT NULL, ' .
					'protected tinyint(1) NOT NULL, ' .
					'enabled tinyint(1) NOT NULL, ' .
					'enable_from datetime DEFAULT NULL, ' .
					'enable_until datetime DEFAULT NULL, ' .
					'last_access date DEFAULT NULL, ' .
					'created datetime NOT NULL, ' .
					'updated datetime NOT NULL, ' .
					'PRIMARY KEY (consumer_key))';
				$ok  = $db->exec($sql) !== FALSE;
			}

			if ($ok) {
				$sql = "CREATE TABLE  IF NOT EXISTS {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' (' .
					'consumer_key varchar(50) NOT NULL, ' .
					'context_id varchar(50) NOT NULL, ' .
					'lti_context_id varchar(50) DEFAULT NULL, ' .
					'lti_resource_id varchar(50) DEFAULT NULL, ' .
					'title varchar(255) NOT NULL, ' .
					'settings text, ' .
					'primary_consumer_key varchar(50) DEFAULT NULL, ' .
					'primary_context_id varchar(50) DEFAULT NULL, ' .
					'share_approved tinyint(1) DEFAULT NULL, ' .
					'created datetime NOT NULL, ' .
					'updated datetime NOT NULL, ' .
					'PRIMARY KEY (consumer_key, context_id))';
				$ok  = $db->exec($sql) !== FALSE;
			}

			if ($ok) {
				$sql = "CREATE TABLE  IF NOT EXISTS {$prefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' (' .
					'consumer_key varchar(50) NOT NULL, ' .
					'context_id varchar(50) NOT NULL, ' .
					'user_id varchar(50) NOT NULL, ' .
					'lti_result_sourcedid varchar(255) NOT NULL, ' .
					'created datetime NOT NULL, ' .
					'updated datetime NOT NULL, ' .
					'PRIMARY KEY (consumer_key, context_id, user_id))';
				$ok  = $db->exec($sql) !== FALSE;
			}

			if ($ok) {
				$sql = "CREATE TABLE  IF NOT EXISTS {$prefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' (' .
					'consumer_key varchar(50) NOT NULL, ' .
					'value varchar(32) NOT NULL, ' .
					'expires datetime NOT NULL, ' .
					'PRIMARY KEY (consumer_key, value))';
				$ok  = $db->exec($sql) !== FALSE;
			}

			if ($ok) {
				$sql = "CREATE TABLE  IF NOT EXISTS {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' (' .
					'share_key_id varchar(32) NOT NULL, ' .
					'primary_consumer_key varchar(50) NOT NULL, ' .
					'primary_context_id varchar(50) NOT NULL, ' .
					'auto_approve tinyint(1) NOT NULL, ' .
					'expires datetime NOT NULL, ' .
					'PRIMARY KEY (share_key_id))';
				$ok  = $db->exec($sql) !== FALSE;
			}

		}
		else {

			$ok = TRUE; // always return TRUE for other database types

		}

		return $ok;

	}
