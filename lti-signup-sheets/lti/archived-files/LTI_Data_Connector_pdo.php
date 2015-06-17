<?php
/**
 * LTI_Tool_Provider - PHP class to include in an external tool to handle connections with an LTI 1 compliant tool consumer
 * Copyright (C) 2014  Stephen P Vickers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Contact: stephen@spvsoftwareproducts.com
 *
 * Version history:
 *   2.0.00  30-Jun-12  Initial release
 *   2.1.00   3-Jul-12  Added fields to tool consumer: consumer_guid, protected, last_access
 *   2.2.00  16-Oct-12
 *   2.3.00   2-Jan-13  Updated Context to Resource_Link in method names
 *                      Settings values now saved as JSON
 *   2.3.01   2-Feb-13
 *   2.3.02  18-Feb-13
 *   2.3.03   5-Jun-13
 *   2.3.04  13-Aug-13
 *   2.3.05  29-Jul-14  Added support for date and time formats
 *                      Added support of Oracle PDO driver
 *   2.3.06   5-Aug-14
*/

###
###  Class to represent a PDO LTI Data Connector
###

class LTI_Data_Connector_PDO extends LTI_Data_Connector {

  private $db = NULL;
  private $dbTableNamePrefix = '';

###
#    Class constructor
###
  function __construct($db, $dbTableNamePrefix = '') {

    $this->db = $db;
    $this->dbTableNamePrefix = $dbTableNamePrefix;
    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'oci') {
      $this->date_format = 'd-M-Y';
    }

  }


###
###  LTI_Tool_Consumer methods
###

###
#    Load the tool consumer from the database
###
  public function Tool_Consumer_load($consumer) {

    $sql = 'SELECT name, secret, lti_version, consumer_name, consumer_version, consumer_guid, css_path, protected, enabled, enable_from, enable_until, last_access, created, updated ' .
           'FROM ' .$this->dbTableNamePrefix . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' ' .
           'WHERE consumer_key = :key';
    $query = $this->db->prepare($sql);
    $key = $consumer->getKey();
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $ok = $query->execute();

    if ($ok) {
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $ok = ($row !== FALSE);
    }

    if ($ok) {
      $row = array_change_key_case($row);
      $consumer->name = $row['name'];
      $consumer->secret = $row['secret'];;
      $consumer->lti_version = $row['lti_version'];
      $consumer->consumer_name = $row['consumer_name'];
      $consumer->consumer_version = $row['consumer_version'];
      $consumer->consumer_guid = $row['consumer_guid'];
      $consumer->css_path = $row['css_path'];
      $consumer->protected = ($row['protected'] == 1);
      $consumer->enabled = ($row['enabled'] == 1);
      $consumer->enable_from = NULL;
      if (!is_null($row['enable_from'])) {
        $consumer->enable_from = strtotime($row['enable_from']);
      }
      $consumer->enable_until = NULL;
      if (!is_null($row['enable_until'])) {
        $consumer->enable_until = strtotime($row['enable_until']);
      }
      $consumer->last_access = NULL;
      if (!is_null($row['last_access'])) {
        $consumer->last_access = strtotime($row['last_access']);
      }
      $consumer->created = strtotime($row['created']);
      $consumer->updated = strtotime($row['updated']);
    }

    return $ok;

  }

###
#    Save the tool consumer to the database
###
  public function Tool_Consumer_save($consumer) {

    if ($consumer->protected) {
      $protected = 1;
    } else {
      $protected = 0;
    }
    if ($consumer->enabled) {
      $enabled = 1;
    } else {
      $enabled = 0;
    }
    $time = time();
    $now = date("{$this->date_format} {$this->time_format}", $time);
    $from = NULL;
    if (!is_null($consumer->enable_from)) {
      $from = date("{$this->date_format} {$this->time_format}", $consumer->enable_from);
    }
    $until = NULL;
    if (!is_null($consumer->enable_until)) {
      $until = date("{$this->date_format} {$this->time_format}", $consumer->enable_until);
    }
    $last = NULL;
    if (!is_null($consumer->last_access)) {
      $last = date($this->date_format, $consumer->last_access);
    }
    $key = $consumer->getKey();
    if (is_null($consumer->created)) {
      $sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' ' .
             '(consumer_key, name, secret, lti_version, consumer_name, consumer_version, consumer_guid, css_path, protected, enabled, enable_from, enable_until, last_access, created, updated) ' .
             'VALUES (:key, :name, :secret, :lti_version, :consumer_name, :consumer_version, :consumer_guid, :css_path, ' .
             ':protected, :enabled, :enable_from, :enable_until, :last_access, :created, :updated)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('name', $consumer->name, PDO::PARAM_STR);
      $query->bindValue('secret', $consumer->secret, PDO::PARAM_STR);
      $query->bindValue('lti_version', $consumer->lti_version, PDO::PARAM_STR);
      $query->bindValue('consumer_name', $consumer->consumer_name, PDO::PARAM_STR);
      $query->bindValue('consumer_version', $consumer->consumer_version, PDO::PARAM_STR);
      $query->bindValue('consumer_guid', $consumer->consumer_guid, PDO::PARAM_STR);
      $query->bindValue('css_path', $consumer->css_path, PDO::PARAM_STR);
      $query->bindValue('protected', $protected, PDO::PARAM_INT);
      $query->bindValue('enabled', $enabled, PDO::PARAM_INT);
      $query->bindValue('enable_from', $from, PDO::PARAM_STR);
      $query->bindValue('enable_until', $until, PDO::PARAM_STR);
      $query->bindValue('last_access', $last, PDO::PARAM_STR);
      $query->bindValue('created', $now, PDO::PARAM_STR);
      $query->bindValue('updated', $now, PDO::PARAM_STR);
    } else {
      $sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' ' .
             'SET name = :name, secret = :secret, lti_version = :lti_version, ' .
             'consumer_name = :consumer_name, consumer_version = :consumer_version, consumer_guid = :consumer_guid, css_path = :css_path, ' .
             'protected = :protected, enabled = :enabled, enable_from = :enable_from, enable_until = :enable_until, last_access = :last_access, updated = :updated ' .
             'WHERE consumer_key = :key';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('name', $consumer->name, PDO::PARAM_STR);
      $query->bindValue('secret', $consumer->secret, PDO::PARAM_STR);
      $query->bindValue('lti_version', $consumer->lti_version, PDO::PARAM_STR);
      $query->bindValue('consumer_name', $consumer->consumer_name, PDO::PARAM_STR);
      $query->bindValue('consumer_version', $consumer->consumer_version, PDO::PARAM_STR);
      $query->bindValue('consumer_guid', $consumer->consumer_guid, PDO::PARAM_STR);
      $query->bindValue('css_path', $consumer->css_path, PDO::PARAM_STR);
      $query->bindValue('protected', $protected, PDO::PARAM_INT);
      $query->bindValue('enabled', $enabled, PDO::PARAM_INT);
      $query->bindValue('enable_from', $from, PDO::PARAM_STR);
      $query->bindValue('enable_until', $until, PDO::PARAM_STR);
      $query->bindValue('last_access', $last, PDO::PARAM_STR);
      $query->bindValue('updated', $now, PDO::PARAM_STR);
    }
    $ok = $query->execute();
    if ($ok) {
      if (is_null($consumer->created)) {
        $consumer->created = $time;
      }
      $consumer->updated = $time;
    }

    return $ok;

  }

###
#    Delete the tool consumer from the database
###
  public function Tool_Consumer_delete($consumer) {

    $key = $consumer->getKey();
// Delete any nonce values for this consumer
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::NONCE_TABLE_NAME . ' WHERE consumer_key = :key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->execute();

// Delete any outstanding share keys for resource links for this consumer
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' WHERE primary_consumer_key = :key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->execute();

// Delete any users in resource links for this consumer
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' WHERE consumer_key = :key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->execute();

// Update any resource links for which this consumer is acting as a primary resource link
    $sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
           'SET primary_consumer_key = NULL, primary_context_id = NULL, share_approved = NULL ' .
           'WHERE primary_consumer_key = :key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->execute();

// Delete any resource links for this consumer
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' WHERE consumer_key = :key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->execute();

// Delete consumer
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' WHERE consumer_key = :key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $ok = $query->execute();

    if ($ok) {
      $consumer->initialise();
    }

    return $ok;

  }

###
#    Load all tool consumers from the database
###
  public function Tool_Consumer_list() {

    $consumers = array();

    $sql = 'SELECT consumer_key, name, secret, lti_version, consumer_name, consumer_version, consumer_guid, css_path, ' .
           'protected, enabled, enable_from, enable_until, last_access, created, updated ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' ' .
           'ORDER BY name';
    $query = $this->db->prepare($sql);
    $ok = ($query !== FALSE);

    if ($ok) {
      $ok = $query->execute();
    }
    if ($ok) {
      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $row = array_change_key_case($row);
        $consumer = new LTI_Tool_Consumer($row['consumer_key'], $this);
        $consumer->name = $row['name'];
        $consumer->secret = $row['secret'];;
        $consumer->lti_version = $row['lti_version'];
        $consumer->consumer_name = $row['consumer_name'];
        $consumer->consumer_version = $row['consumer_version'];
        $consumer->consumer_guid = $row['consumer_guid'];
        $consumer->css_path = $row['css_path'];
        $consumer->protected = ($row['protected'] == 1);
        $consumer->enabled = ($row['enabled'] == 1);
        $consumer->enable_from = NULL;
        if (!is_null($row['enable_from'])) {
          $consumer->enable_from = strtotime($row['enable_from']);
        }
        $consumer->enable_until = NULL;
        if (!is_null($row['enable_until'])) {
          $consumer->enable_until = strtotime($row['enable_until']);
        }
        $consumer->last_access = NULL;
        if (!is_null($row['last_access'])) {
          $consumer->last_access = strtotime($row['last_access']);
        }
        $consumer->created = strtotime($row['created']);
        $consumer->updated = strtotime($row['updated']);
        $consumers[] = $consumer;
      }
    }

    return $consumers;

  }

###
###  LTI_Resource_Link methods
###

###
#    Load the resource link from the database
###
  public function Resource_Link_load($resource_link) {

    $key = $resource_link->getKey();
    $id = $resource_link->getId();
    $sql = 'SELECT consumer_key, context_id, lti_context_id, lti_resource_id, title, settings, ' .
           'primary_consumer_key, primary_context_id, share_approved, created, updated ' .
           'FROM ' .$this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
           'WHERE (consumer_key = :key) AND (context_id = :id)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $ok = $query->execute();
    if ($ok) {
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $ok = ($row !== FALSE);
    }

    if ($ok) {
      $row = array_change_key_case($row);
      $resource_link->lti_context_id = $row['lti_context_id'];
      $resource_link->lti_resource_link_id = $row['lti_resource_id'];
      $resource_link->title = $row['title'];
      if (is_string($row['settings'])) {
        $resource_link->settings = json_decode($row['settings'], TRUE);
        if (!is_array($resource_link->settings)) {
          $resource_link->settings = unserialize($row['settings']);  // check for old serialized setting
        }
        if (!is_array($resource_link->settings)) {
          $resource_link->settings = array();
        }
      } else {
        $resource_link->settings = array();
      }
      $resource_link->primary_consumer_key = $row['primary_consumer_key'];
      $resource_link->primary_resource_link_id = $row['primary_context_id'];
      $resource_link->share_approved = (is_null($row['share_approved'])) ? NULL : ($row['share_approved'] == 1);
      $resource_link->created = strtotime($row['created']);
      $resource_link->updated = strtotime($row['updated']);
    }

    return $ok;

  }

###
#    Save the resource link to the database
###
  public function Resource_Link_save($resource_link) {

    $time = time();
    $now = date("{$this->date_format} {$this->time_format}", $time);
    $settingsValue = json_encode($resource_link->settings);
    $key = $resource_link->getKey();
    $id = $resource_link->getId();
    if (is_null($resource_link->created)) {
      $sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
             '(consumer_key, context_id, lti_context_id, lti_resource_id, title, settings, ' .
             'primary_consumer_key, primary_context_id, share_approved, created, updated) ' .
             'VALUES (:key, :id, :lti_context_id, :lti_resource_id, :title, :settings, ' .
             ':primary_consumer_key, :primary_context_id, :share_approved, :created, :updated)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('id', $id, PDO::PARAM_STR);
      $query->bindValue('lti_context_id', $resource_link->lti_context_id, PDO::PARAM_STR);
      $query->bindValue('lti_resource_id', $resource_link->lti_resource_id, PDO::PARAM_STR);
      $query->bindValue('title', $resource_link->title, PDO::PARAM_STR);
      $query->bindValue('settings', $settingsValue, PDO::PARAM_STR);
      $query->bindValue('primary_consumer_key', $resource_link->primary_consumer_key, PDO::PARAM_STR);
      $query->bindValue('primary_context_id', $resource_link->primary_resource_link_id, PDO::PARAM_STR);
      $query->bindValue('share_approved', $resource_link->share_approved, PDO::PARAM_INT);
      $query->bindValue('created', $now, PDO::PARAM_STR);
      $query->bindValue('updated', $now, PDO::PARAM_STR);
    } else {
      $sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
             'SET lti_context_id = :lti_context_id, lti_resource_id = :lti_resource_id, title = :title, settings = :settings, ' .
             'primary_consumer_key = :primary_consumer_key, primary_context_id = :primary_context_id, share_approved = :share_approved, updated = :updated ' .
             'WHERE (consumer_key = :key) AND (context_id = :id)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('id', $id, PDO::PARAM_STR);
      $query->bindValue('lti_context_id', $resource_link->lti_context_id, PDO::PARAM_STR);
      $query->bindValue('lti_resource_id', $resource_link->lti_resource_id, PDO::PARAM_STR);
      $query->bindValue('title', $resource_link->title, PDO::PARAM_STR);
      $query->bindValue('settings', $settingsValue, PDO::PARAM_STR);
      $query->bindValue('primary_consumer_key', $resource_link->primary_consumer_key, PDO::PARAM_STR);
      $query->bindValue('primary_context_id', $resource_link->primary_resource_link_id, PDO::PARAM_STR);
      $query->bindValue('share_approved', $resource_link->share_approved, PDO::PARAM_INT);
      $query->bindValue('updated', $now, PDO::PARAM_STR);
    }
    $ok = $query->execute();
    if ($ok) {
      if (is_null($resource_link->created)) {
        $resource_link->created = $time;
      }
      $resource_link->updated = $time;
    }

    return $ok;

  }

###
#    Delete the resource link from the database
###
  public function Resource_Link_delete($resource_link) {

    $key = $resource_link->getKey();
    $id = $resource_link->getId();
// Delete any outstanding share keys for resource links for this consumer
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
           'WHERE (primary_consumer_key = :key) AND (primary_context_id = :id)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $ok = $query->execute();

// Delete users
    if ($ok) {
      $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
             'WHERE (consumer_key = :key) AND (context_id = :id)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('id', $id, PDO::PARAM_STR);
      $ok = $query->execute();
    }

// Update any resource links for which this is the primary resource link
    if ($ok) {
      $sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
             'SET primary_consumer_key = NULL, primary_context_id = NULL ' .
             'WHERE (primary_consumer_key = :key) AND (primary_context_id = :id)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('id', $id, PDO::PARAM_STR);
      $ok = $query->execute();
    }

// Delete resource link
    if ($ok) {
      $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
             'WHERE (consumer_key = :key) AND (context_id = :id)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $key, PDO::PARAM_STR);
      $query->bindValue('id', $id, PDO::PARAM_STR);
      $ok = $query->execute();
    }

    if ($ok) {
      $resource_link->initialise();
    }

    return $ok;

  }

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
###
  public function Resource_Link_getUserResultSourcedIDs($resource_link, $local_only, $id_scope) {

    $users = array();

    if ($local_only) {
      $sql = 'SELECT u.consumer_key, u.context_id, u.user_id, u.lti_result_sourcedid ' .
             'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' u ' .
             'INNER JOIN ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' c ' .
             'ON u.consumer_key = c.consumer_key AND u.context_id = c.context_id ' .
             'WHERE (c.consumer_key = :key) AND (c.context_id = :id) AND (c.primary_consumer_key IS NULL) AND (c.primary_context_id IS NULL)';
    } else {
      $sql = 'SELECT u.consumer_key, u.context_id, u.user_id, u.lti_result_sourcedid ' .
             'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' u ' .
             'INNER JOIN ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' c ' .
             'ON u.consumer_key = c.consumer_key AND u.context_id = c.context_id ' .
             'WHERE ((c.consumer_key = :key) AND (c.context_id = :id) AND (c.primary_consumer_key IS NULL) AND (c.primary_context_id IS NULL)) OR ' .
             '((c.primary_consumer_key = :key) AND (c.primary_context_id = :id) AND (share_approved = 1))';
    }
    $key = $resource_link->getKey();
    $id = $resource_link->getId();
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    if ($query->execute()) {
      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $row = array_change_key_case($row);
        $user = new LTI_User($resource_link, $row['user_id']);
        $user->consumer_key = $row['consumer_key'];
        $user->context_id = $row['context_id'];
        $user->lti_result_sourcedid = $row['lti_result_sourcedid'];
        if (is_null($id_scope)) {
          $users[] = $user;
        } else {
          $users[$user->getId($id_scope)] = $user;
        }
      }
    }

    return $users;

  }

###
#    Get an array of LTI_Resource_Link_Share objects for each resource link which is sharing this resource link
###
  public function Resource_Link_getShares($resource_link) {

    $shares = array();

    $key = $resource_link->getKey();
    $id = $resource_link->getId();
    $sql = 'SELECT consumer_key, context_id, title, share_approved ' .
           'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
           'WHERE (primary_consumer_key = :key) AND (primary_context_id = :id) ' .
           'ORDER BY consumer_key';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    if ($query->execute()) {
      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $row = array_change_key_case($row);
        $share = new LTI_Resource_Link_Share();
        $share->consumer_key = $row['consumer_key'];
        $share->resource_link_id = $row['context_id'];
        $share->title = $row['title'];
        $share->approved = ($row['share_approved'] == 1);
        $shares[] = $share;
      }
    }

    return $shares;

  }


###
###  LTI_Consumer_Nonce methods
###

###
#    Load the consumer nonce from the database
###
  public function Consumer_Nonce_load($nonce) {

// Delete any expired nonce values
    $now = date("{$this->date_format} {$this->time_format}", time());
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::NONCE_TABLE_NAME . ' WHERE expires <= :now';
    $query = $this->db->prepare($sql);
    $query->bindValue('now', $now, PDO::PARAM_STR);
    $query->execute();

// Load the nonce
    $key = $nonce->getKey();
    $value = $nonce->getValue();
    $sql = 'SELECT value T FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::NONCE_TABLE_NAME . ' WHERE (consumer_key = :key) AND (value = :value)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('value', $value, PDO::PARAM_STR);
    $ok = $query->execute();
    if ($ok) {
      $row = $query->fetch(PDO::FETCH_ASSOC);
      if ($row === FALSE) {
        $ok = FALSE;
      }
    }

    return $ok;

  }

###
#    Save the consumer nonce in the database
###
  public function Consumer_Nonce_save($nonce) {

    $key = $nonce->getKey();
    $value = $nonce->getValue();
    $expires = date("{$this->date_format} {$this->time_format}", $nonce->expires);
    $sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::NONCE_TABLE_NAME . ' (consumer_key, value, expires) VALUES (:key, :value, :expires)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('value', $value, PDO::PARAM_STR);
    $query->bindValue('expires', $expires, PDO::PARAM_STR);
    $ok = $query->execute();

    return $ok;

  }


###
###  LTI_Resource_Link_Share_Key methods
###

###
#    Load the resource link share key from the database
###
  public function Resource_Link_Share_Key_load($share_key) {

// Clear expired share keys
    $now = date("{$this->date_format} {$this->time_format}", time());
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' WHERE expires <= :now';
    $query = $this->db->prepare($sql);
    $query->bindValue('now', $now, PDO::PARAM_STR);
    $query->execute();

// Load share key
    $id = $share_key->getId();
    $sql = 'SELECT share_key_id, primary_consumer_key, primary_context_id, auto_approve, expires ' .
           'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
           'WHERE share_key_id = :id';
    $query = $this->db->prepare($sql);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $ok = $query->execute();
    if ($ok) {
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $ok = ($row !== FALSE);
    }

    if ($ok) {
      $row = array_change_key_case($row);
      $share_key->primary_consumer_key = $row['primary_consumer_key'];
      $share_key->primary_resource_link_id = $row['primary_context_id'];
      $share_key->auto_approve = ($row['auto_approve'] == 1);
      $share_key->expires = strtotime($row['expires']);
    }

    return $ok;

  }

###
#    Save the resource link share key to the database
###
  public function Resource_Link_Share_Key_save($share_key) {

    if ($share_key->auto_approve) {
      $approve = 1;
    } else {
      $approve = 0;
    }
    $expires = date("{$this->date_format} {$this->time_format}", $share_key->expires);
    $id = $share_key->getId();
    $sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
           '(share_key_id, primary_consumer_key, primary_context_id, auto_approve, expires) ' .
           'VALUES (:id, :primary_consumer_key, :primary_context_id, :approve, :expires)';
    $query = $this->db->prepare($sql);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $query->bindValue('primary_consumer_key', $share_key->primary_consumer_key, PDO::PARAM_STR);
    $query->bindValue('primary_context_id', $share_key->primary_resource_link_id, PDO::PARAM_STR);
    $query->bindValue('approve', $approve, PDO::PARAM_INT);
    $query->bindValue('expires', $expires, PDO::PARAM_STR);

    return $query->execute();

  }

###
#    Delete the resource link share key from the database
###
  public function Resource_Link_Share_Key_delete($share_key) {

    $id = $share_key->getId();
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' WHERE share_key_id = :id';
    $query = $this->db->prepare($sql);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $ok = $query->execute();
    if ($ok) {
      $share_key->initialise();
    }

    return $ok;

  }


###
###  LTI_User methods
###

###
#    Load the user from the database
###
  public function User_load($user) {

    $key = $user->getResourceLink()->getKey();
    $id = $user->getResourceLink()->getId();
    $userId = $user->getId(LTI_Tool_Provider::ID_SCOPE_ID_ONLY);
    $sql = 'SELECT lti_result_sourcedid, created, updated ' .
           'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
           'WHERE (consumer_key = :key) AND (context_id = :id) AND (user_id = :user_id)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $query->bindValue('user_id', $userId, PDO::PARAM_STR);
    $ok = $query->execute();
    if ($ok) {
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $ok = ($row !== FALSE);
    }

    if ($ok) {
      $row = array_change_key_case($row);
      $user->lti_result_sourcedid = $row['lti_result_sourcedid'];
      $user->created = strtotime($row['created']);
      $user->updated = strtotime($row['updated']);
    }

    return $ok;

  }

###
#    Save the user to the database
###
  public function User_save($user) {

    $time = time();
    $now = date("{$this->date_format} {$this->time_format}", $time);
    $key = $user->getResourceLink()->getKey();
    $id = $user->getResourceLink()->getId();
    $userId = $user->getId(LTI_Tool_Provider::ID_SCOPE_ID_ONLY);
    if (is_null($user->created)) {
      $sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' (consumer_key, context_id, ' .
             'user_id, lti_result_sourcedid, created, updated) ' .
             'VALUES (:key, :id, :user_id, :lti_result_sourcedid, :now, :now)';
    } else {
      $sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
             'SET lti_result_sourcedid = :lti_result_sourcedid, updated = :now ' .
             'WHERE (consumer_key = :key) AND (context_id = :id) AND (user_id = :user_id)';
    }
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $query->bindValue('user_id', $userId, PDO::PARAM_STR);
    $query->bindValue('lti_result_sourcedid', $user->lti_result_sourcedid, PDO::PARAM_STR);
    $query->bindValue('now', $now, PDO::PARAM_STR);
    $ok = $query->execute();
    if ($ok) {
      if (is_null($user->created)) {
        $user->created = $time;
      }
      $user->updated = $time;
    }

    return $ok;

  }

###
#    Delete the user from the database
###
  public function User_delete($user) {

    $key = $user->getResourceLink()->getKey();
    $id = $user->getResourceLink()->getId();
    $userId = $user->getId(LTI_Tool_Provider::ID_SCOPE_ID_ONLY);
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
           'WHERE (consumer_key = :key) AND (context_id = :id) AND (user_id = :user_id)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $query->bindValue('user_id', $userId, PDO::PARAM_STR);
    $ok = $query->execute();

    if ($ok) {
      $user->initialise();
    }

    return $ok;

  }

}

?>
