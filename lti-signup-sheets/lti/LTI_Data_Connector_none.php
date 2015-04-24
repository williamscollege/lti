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
 *   2.1.00   3-Jul-12
 *   2.2.00  16-Oct-12
 *   2.3.00   2-Jan-13  Updated Context to Resource_Link in method names
 *   2.3.01   2-Feb-13
 *   2.3.02  18-Feb-13
 *   2.3.03   5-Jun-13  Corrected errors with Consumer_Nonce_load and Consumer_Nonce_save methods
 *                      Fixed syntax errors with $now variable references
 *   2.3.04  13-Aug-13
 *   2.3.05  29-Jul-14
 *   2.3.06   5-Aug-14
*/

###
###  Class to represent a dummy LTI Data Connector with no data persistence
###

class LTI_Data_Connector_None extends LTI_Data_Connector {

###
###  LTI_Tool_Consumer methods
###

###
#    Load the tool consumer from the database
###
  public function Tool_Consumer_load($consumer) {

    $consumer->secret = 'secret';
    $consumer->enabled = TRUE;
    $now = time();
    $consumer->created = $now;
    $consumer->updated = $now;
    return TRUE;

  }

###
#    Save the tool consumer to the database
###
  public function Tool_Consumer_save($consumer) {

    $consumer->updated = time();
    return TRUE;

  }

###
#    Delete the tool consumer from the database
###
  public function Tool_Consumer_delete($consumer) {

    $consumer->initialise();
    return TRUE;

  }

###
#    Load all tool consumers from the database
###
  public function Tool_Consumer_list() {

    return array();

  }

###
###  LTI_Resource_Link methods
###

###
#    Load the resource link from the database
###
  public function Resource_Link_load($resource_link) {

    $now = time();
    $resource_link->created = $now;
    $resource_link->updated = $now;
    return TRUE;

  }

###
#    Save the resource link to the database
###
  public function Resource_Link_save($resource_link) {

    $resource_link->updated = time();
    return TRUE;

  }

###
#    Delete the resource link from the database
###
  public function Resource_Link_delete($resource_link) {

    $resource_link->initialise();
    return TRUE;

  }

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
###
  public function Resource_Link_getUserResultSourcedIDs($resource_link, $local_only, $id_scope) {

    return array();

  }

###
#    Get an array of LTI_Resource_Link_Share objects for each resource link which is sharing this resource link
###
  public function Resource_Link_getShares($resource_link) {

    return array();

  }


###
###  LTI_Consumer_Nonce methods
###

###
#    Load the consumer nonce from the database
###
  public function Consumer_Nonce_load($nonce) {

    return FALSE;  // assume the nonce does not already exist

  }

###
#    Save the consumer nonce in the database
###
  public function Consumer_Nonce_save($nonce) {

    return TRUE;

  }


###
###  LTI_Resource_Link_Share_Key methods
###

###
#    Load the resource link share key from the database
###
  public function Resource_Link_Share_Key_load($share_key) {

    return TRUE;

  }

###
#    Save the resource link share key to the database
###
  public function Resource_Link_Share_Key_save($share_key) {

    return TRUE;

  }

###
#    Delete the resource link share key from the database
###
  public function Resource_Link_Share_Key_delete($share_key) {

    return TRUE;

  }


###
###  LTI_User methods
###


###
#    Load the user from the database
###
  public function User_load($user) {

    $now = time();
    $user->created = $now;
    $user->updated = $now;
    return TRUE;

  }

###
#    Save the user to the database
###
  public function User_save($user) {

    $user->updated = time();
    return TRUE;

  }

###
#    Delete the user from the database
###
  public function User_delete($user) {

    $user->initialise();
    return TRUE;

  }

}

?>
