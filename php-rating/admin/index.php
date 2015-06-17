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

  require_once('../lib.php');

// Initialise session and database
  $db = NULL;
  $ok = init($db, FALSE);
// Initialise parameters
  $key = '';
  if ($ok) {
// Create LTI Tool Provider instance
    $data_connector = LTI_Data_Connector::getDataConnector(DB_TABLENAME_PREFIX, $db);
    $tool = new LTI_Tool_Provider($data_connector);
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
      $update_consumer = new LTI_Tool_Consumer($key, $data_connector);
      $update_consumer->name = $_POST['name'];
      $update_consumer->secret = $_POST['secret'];
      $update_consumer->enabled = isset($_POST['enabled']);
      $date = $_POST['enable_from'];
      if (empty($date)) {
        $update_consumer->enable_from = NULL;
      } else {
        $update_consumer->enable_from = strtotime($date);
      }
      $date = $_POST['enable_until'];
      if (empty($date)) {
        $update_consumer->enable_until = NULL;
      } else {
        $update_consumer->enable_until = strtotime($date);
      }
      $update_consumer->protected = isset($_POST['protected']);
// Ensure all required fields have been provided
      if (!empty($_POST['key']) && !empty($_POST['name']) && !empty($_POST['secret'])) {
        if ($update_consumer->save()) {
          $_SESSION['message'] = 'The consumer has been saved.';
        } else {
          $_SESSION['error_message'] = 'Unable to save the consumer; please check the data and try again.';
        }
        header('Location: ./');
        exit;
      }

// Process delete consumer action
    } else if ($action == 'delete') {
      $consumer = new LTI_Tool_Consumer($key, $data_connector);
      if ($consumer->delete()) {
        $_SESSION['message'] = 'The consumer has been deleted.';
      } else {
        $_SESSION['error_message'] = 'Unable to delete the consumer; please try again.';
      }
      header('Location: ./');
      exit;

    } else {
// Initialise an empty tool consumer instance
      $update_consumer = new LTI_Tool_Consumer(NULL, $data_connector);
    }

// Fetch a list of existing tool consumer records
    $consumers = $tool->getConsumers();

  }

// Page header
  $title = APP_NAME . ': Manage tool consumers';
  $page = <<< EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-language" content="EN" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>{$title}</title>
<link href="../css/rating.css" media="screen" rel="stylesheet" type="text/css" />
</head>

<body>
<h1>{$title}</h1>

EOD;

// Display warning message if access does not appear to have been restricted
  if (!(isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['REMOTE_USER']) && isset($_SERVER['PHP_AUTH_PW']))) {
    $page .= <<< EOD
<p><strong>*** WARNING *** Access to this page should be restricted to application administrators only.</strong></p>

EOD;
  }

// Check for any messages to be displayed
  if (isset($_SESSION['error_message'])) {
  $page .= <<< EOD
<p style="font-weight: bold; color: #f00;">ERROR: {$_SESSION['error_message']}</p>

EOD;
    unset($_SESSION['error_message']);
  }

  if (isset($_SESSION['message'])) {
  $page .= <<< EOD
<p style="font-weight: bold; color: #00f;">{$_SESSION['message']}</p>

EOD;
    unset($_SESSION['message']);
  }

// Display table of existing tool consumer records
  if ($ok) {

    if (count($consumers) <= 0) {
      $page .= <<< EOD
<p>No consumers have been added yet.</p>

EOD;
    } else {
      $page .= <<< EOD
<table class="items" border="1" cellpadding="3">
<thead>
  <tr>
    <th>Name</th>
    <th>Key</th>
    <th>Version</th>
    <th>Available?</th>
    <th>Protected?</th>
    <th>Last access</th>
    <th>Options</th>
  </tr>
</thead>
<tbody>

EOD;
      foreach ($consumers as $consumer) {
        $trkey = urlencode($consumer->getKey());
        if ($key == $consumer->getKey()) {
          $update_consumer = $consumer;
        }
        if (!$consumer->getIsAvailable()) {
          $available = 'cross';
          $available_alt = 'Not available';
          $trclass = 'notvisible';
        } else {
          $available = 'tick';
          $available_alt = 'Available';
          $trclass = '';
        }
        if ($consumer->protected) {
          $protected = 'tick';
          $protected_alt = 'Protected';
        } else {
          $protected = 'cross';
          $protected_alt = 'Not protected';
        }
        if (is_null($consumer->last_access)) {
          $last = 'None';
        } else {
          $last = date('j-M-Y', $consumer->last_access);
        }
        $page .= <<< EOD
  <tr class="{$trclass}">
    <td>{$consumer->name}</td>
    <td>{$consumer->getKey()}</td>
    <td><span title="{$consumer->consumer_guid}">{$consumer->consumer_version}</span></td>
    <td class="aligncentre"><img src="../images/{$available}.gif" alt="{$available_alt}" title="{$available_alt}" /></td>
    <td class="aligncentre"><img src="../images/{$protected}.gif" alt="{$protected_alt}" title="{$protected_alt}" /></td>
    <td>{$last}</td>
    <td class="iconcolumn aligncentre">
      <a href="./?key={$trkey}#edit"><img src="../images/edit.png" title="Edit consumer" alt="Edit consumer" /></a>&nbsp;<a href="./?do=delete&amp;key={$trkey}" onclick="return confirm('Delete consumer; are you sure?');"><img src="../images/delete.png" title="Delete consumer" alt="Delete consumer" /></a>
    </td>
  </tr>

EOD;
      }
      $page .= <<< EOD
</tbody>
</table>

EOD;

    }

// Display form for adding/editing a tool consumer
    if (isset($update_consumer->created)) {
      $mode = 'Update';
      $type = ' disabled="disabled"';
      $key1 = '';
      $key2 = 'key';
    } else {
      $mode = 'Add new';
      $type = '';
      $key1 = 'key';
      $key2 = '';
    }
    $name = htmlentities($update_consumer->name);
    $key = htmlentities($update_consumer->getKey());
    $secret = htmlentities($update_consumer->secret);
    if ($update_consumer->enabled) {
      $enabled = ' checked="checked"';
    } else {
      $enabled = '';
    }
    $enable_from = '';
    if (!is_null($update_consumer->enable_from)) {
      $enable_from = date('j-M-Y H:i', $update_consumer->enable_from);
    }
    $enable_until = '';
    if (!is_null($update_consumer->enable_until)) {
      $enable_until = date('j-M-Y H:i', $update_consumer->enable_until);
    }
    if ($update_consumer->protected) {
      $protected = ' checked="checked"';
    } else {
      $protected = '';
    }
    $page .= <<< EOD
<h2><a name="edit">{$mode} consumer</a></h2>

<form action="./" method="post">
<div class="box">
  <span class="label">Name:<span class="required" title="required">*</span></span>&nbsp;<input name="name" type="text" size="50" maxlength="50" value="{$name}" /><br />
  <span class="label">Key:<span class="required" title="required">*</span></span>&nbsp;<input name="{$key1}" type="text" size="75" maxlength="50" value="{$key}"{$type} /><br />
  <span class="label">Secret:<span class="required" title="required">*</span></span>&nbsp;<input name="secret" type="text" size="75" maxlength="200" value="{$secret}" /><br />
  <span class="label">Enabled?</span>&nbsp;<input name="enabled" type="checkbox" value="1"{$enabled} /><br />
  <span class="label">Enable from:</span>&nbsp;<input name="enable_from" type="text" size="50" maxlength="200" value="{$enable_from}" /><br />
  <span class="label">Enable until:</span>&nbsp;<input name="enable_until" type="text" size="50" maxlength="200" value="{$enable_until}" /><br />
  <span class="label">Protected?</span>&nbsp;<input name="protected" type="checkbox" value="1"{$protected} /><br />
  <br />
  <input type="hidden" name="do" value="add" />
  <input type="hidden" name="{$key2}" value="{$key}" />
  <span class="label"><span class="required" title="required">*</span>&nbsp;=&nbsp;required field</span>&nbsp;<input type="submit" value="{$mode} consumer" />

EOD;

    if (isset($update_consumer->created)) {
      $page .= <<< EOD
  &nbsp;<input type="reset" value="Cancel" onclick="location.href='./';" />

EOD;

    }
  }

// Page footer
  $page .= <<< EOD
</div>
</form>
</body>
</html>

EOD;

// Display page
  echo $page;

?>
