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
 * This page processes an AJAX request to save a user rating for an item.
 */

  require_once('lib.php');

// Initialise session and database
  $db = NULL;
  $ok = init($db, TRUE);
  if ($ok) {
// Ensure request is complete and for a student
    $ok = isset($_POST['id']) && isset($_POST['value']) && $_SESSION['isStudent'];
  }
  if ($ok) {
// Save rating
    $ok = FALSE;
    $item = getItem($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], intval($_POST['id']));
    if (($item !== FALSE) && saveRating($db, $_SESSION['user_consumer_key'], $_SESSION['user_id'], $_POST['id'], $_POST['value'])) {
      updateGradebook($db, $_SESSION['user_consumer_key'], $_SESSION['user_id']);
      $ok = TRUE;
    }
  }

// Generate response
  if ($ok) {
    $response = array('response' => 'Success');
  } else {
    $response = array('response' => 'Fail');
  }

// Return response
  header('Content-type: application/json');
  echo json_encode($response);

?>
