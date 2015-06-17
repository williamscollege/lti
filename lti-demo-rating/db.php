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
 * This page provides functions for accessing the database.
 */

  require_once('config.php');
  require_once(dirname(__FILE__) . '/' . LTI_FOLDER . 'LTI_Tool_Provider.php');


###
###  Return a connection to the database, return FALSE if an error occurs
###
  function open_db() {

    try {
      $db = new PDO(DB_NAME, DB_USERNAME, DB_PASSWORD);
    } catch(PDOException $e) {
      $db = FALSE;
      $_SESSION['error_message'] = "Database error {$e->getCode()}: {$e->getMessage()}";
    }

    return $db;

  }


###
###  Check if a table exists
###
  function table_exists($db, $name) {

    $sql = "select 1 from {$name}";
    $query = $db->prepare($sql);
    return $query->execute() !== FALSE;

  }


###
###  Create any missing database tables (only for MySQL and SQLite databases)
###
  function init_db($db) {

    $db_type = '';
    $pos = strpos(DB_NAME, ':');
    if ($pos !== FALSE) {
      $db_type = strtolower(substr(DB_NAME, 0,$pos));
    }

    $ok = TRUE;
    $prefix = DB_TABLENAME_PREFIX;
    if (($db_type == 'mysql') || ($db_type == 'sqlite')) {

      if (!table_exists($db, $prefix . LTI_Data_Connector::CONSUMER_TABLE_NAME)) {
        $sql = "CREATE TABLE {$prefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' (' .
               'consumer_key varchar(50) NOT NULL, ' .
               'name varchar(50) NOT NULL, ' .
               'secret varchar(255) NOT NULL, ' .
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
        $ok = $db->exec($sql) !== FALSE;
      }

      if ($ok && !table_exists($db, $prefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME)) {
        $sql = "CREATE TABLE {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' (' .
               'consumer_key varchar(50) NOT NULL, ' .
               'context_id varchar(50) NOT NULL, ' .
               'lti_context_id varchar(255) DEFAULT NULL, ' .
               'lti_resource_id varchar(255) DEFAULT NULL, ' .
               'title varchar(255) NOT NULL, ' .
               'settings text, ' .
               'primary_consumer_key varchar(50) DEFAULT NULL, ' .
               'primary_context_id varchar(50) DEFAULT NULL, ' .
               'share_approved tinyint(1) DEFAULT NULL, ' .
               'created datetime NOT NULL, ' .
               'updated datetime NOT NULL, ' .
               'PRIMARY KEY (consumer_key, context_id))';
        $ok = $db->exec($sql) !== FALSE;
        if ($ok) {
          $sql = "ALTER TABLE {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
                 "ADD CONSTRAINT {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . '_' .
                 LTI_Data_Connector::CONSUMER_TABLE_NAME . '_FK1 FOREIGN KEY (consumer_key) ' .
                 "REFERENCES {$prefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' (consumer_key)';
          $ok = $db->exec($sql) !== FALSE;
        }
        if ($ok) {
          $sql = "ALTER TABLE {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
                 "ADD CONSTRAINT {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . '_' .
                 LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . '_FK1 FOREIGN KEY (primary_consumer_key, primary_context_id) ' .
                 "REFERENCES {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' (consumer_key, context_id)';
          $ok = $db->exec($sql) !== FALSE;
        }
      }

      if ($ok && !table_exists($db, $prefix . LTI_Data_Connector::USER_TABLE_NAME)) {
        $sql = "CREATE TABLE {$prefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' (' .
               'consumer_key varchar(50) NOT NULL, ' .
               'context_id varchar(50) NOT NULL, ' .
               'user_id varchar(50) NOT NULL, ' .
               'lti_result_sourcedid varchar(255) NOT NULL, ' .
               'created datetime NOT NULL, ' .
               'updated datetime NOT NULL, ' .
               'PRIMARY KEY (consumer_key, context_id, user_id))';
        $ok = $db->exec($sql) !== FALSE;
        if ($ok) {
          $sql = "ALTER TABLE {$prefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
                 "ADD CONSTRAINT {$prefix}" . LTI_Data_Connector::USER_TABLE_NAME . '_' .
                 LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . '_FK1 FOREIGN KEY (consumer_key, context_id) ' .
                 "REFERENCES {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' (consumer_key, context_id)';
          $ok = $db->exec($sql) !== FALSE;
        }
      }

      if ($ok && !table_exists($db, $prefix . LTI_Data_Connector::NONCE_TABLE_NAME)) {
        $sql = "CREATE TABLE {$prefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' (' .
               'consumer_key varchar(50) NOT NULL, ' .
               'value varchar(32) NOT NULL, ' .
               'expires datetime NOT NULL, ' .
               'PRIMARY KEY (consumer_key, value))';
        $ok = $db->exec($sql) !== FALSE;
        if ($ok) {
          $sql = "ALTER TABLE {$prefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' ' .
                 "ADD CONSTRAINT {$prefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . '_' .
                 LTI_Data_Connector::CONSUMER_TABLE_NAME . '_FK1 FOREIGN KEY (consumer_key) ' .
                 "REFERENCES {$prefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' (consumer_key)';
          $ok = $db->exec($sql) !== FALSE;
        }
      }

      if ($ok && !table_exists($db, $prefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME)) {
        $sql = "CREATE TABLE {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' (' .
               'share_key_id varchar(32) NOT NULL, ' .
               'primary_consumer_key varchar(50) NOT NULL, ' .
               'primary_context_id varchar(50) NOT NULL, ' .
               'auto_approve tinyint(1) NOT NULL, ' .
               'expires datetime NOT NULL, ' .
               'PRIMARY KEY (share_key_id))';
        $ok = $db->exec($sql) !== FALSE;
        if ($ok) {
          $sql = "ALTER TABLE {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
                 "ADD CONSTRAINT {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . '_' .
                 LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . '_FK1 FOREIGN KEY (primary_consumer_key, primary_context_id) ' .
                 "REFERENCES {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' (consumer_key, context_id)';
          $ok = $db->exec($sql) !== FALSE;
        }
      }

      if ($ok && !table_exists($db, "{$prefix}item")) {
// Adjust for different syntax of autoincrement columns
        if ($db_type == 'sqlite') {
          $sql = "CREATE TABLE {$prefix}item (" .
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
        } else {
          $sql = "CREATE TABLE {$prefix}item (" .
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
          $sql = "ALTER TABLE {$prefix}item " .
                 "ADD CONSTRAINT {$prefix}item_" .
                 LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . '_FK1 FOREIGN KEY (consumer_key, resource_id) ' .
                 "REFERENCES {$prefix}" . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' (consumer_key, context_id) ' .
                 'ON UPDATE CASCADE';
          $ok = $db->exec($sql) !== FALSE;
        }
      }

      if ($ok && !table_exists($db, "{$prefix}rating")) {
        $sql = "CREATE TABLE {$prefix}rating (" .
               'item_id int(11) NOT NULL, ' .
               'consumer_key varchar(50) NOT NULL, ' .
               'user_id varchar(50) NOT NULL, ' .
               'rating decimal(10,2) NOT NULL, ' .
               'PRIMARY KEY (item_id,consumer_key,user_id))';
        $ok = $db->exec($sql) !== FALSE;
        if ($ok) {
          $sql = "ALTER TABLE {$prefix}rating " .
                 "ADD CONSTRAINT {$prefix}rating_item_FK1 FOREIGN KEY (item_id) " .
                 "REFERENCES {$prefix}item (item_id)";
          $ok = $db->exec($sql) !== FALSE;
        }
      }

    } else {

      $ok = TRUE;  // always return TRUE for other database types on the assumption that the tables have been created manually

    }

    return $ok;

  }

?>
