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
	 * This page provides general functions to support the application.
	 */

	require_once(dirname(__FILE__) . '/db.php');

	###
	###  Initialise application session and database connection
	###
	function init(&$db, $checkSession = NULL) {

		$ok = TRUE;

		// Set timezone
		if (!ini_get('date.timezone')) {
			date_default_timezone_set('UTC');
		}

		// Set session cookie path
		ini_set('session.cookie_path', getAppPath());

		// Open session
		session_name(LTI_SESSION_NAME);
		session_start();

		if (!is_null($checkSession) && $checkSession) {
			$ok = isset($_SESSION['consumer_key']) && isset($_SESSION['resource_id']) && isset($_SESSION['user_consumer_key']) &&
				isset($_SESSION['user_id']) && isset($_SESSION['isStudent']);
		}

		if (!$ok) {
			$_SESSION['error_message'] = 'Unable to open session.';
		}
		else {
			// Open database connection
			$db = open_db(!$checkSession);
			$ok = $db !== FALSE;
			if (!$ok) {
				if (!is_null($checkSession) && $checkSession) {
					// Display a more user-friendly error message to LTI users
					$_SESSION['error_message'] = 'Unable to open database.';
				}
			}
			else {
				if (!is_null($checkSession) && !$checkSession) {
					// Create database tables (if needed)
					$ok = init_db($db); // assumes a MySQL/SQLite database is being used
					if (!$ok) {
						$_SESSION['error_message'] = 'Unable to initialise database.';
					}
				}
			}
		}

		return $ok;

	}


	###
	###  Return the number of items to be rated for a specified resource link
	###
	function getNumItems($db, $consumer_key, $resource_id) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
SELECT COUNT(i.item_id)
FROM {$prefix}item i
WHERE (i.consumer_key = :consumer_key) AND (i.resource_id = :resource_id)
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
		$query->execute();

		$row = $query->fetch(PDO::FETCH_NUM);
		if ($row === FALSE) {
			$num = 0;
		}
		else {
			$num = intval($row[0]);
		}

		return $num;

	}


	###
	###  Return an array containing the items for a specified resource link
	###
	function getItems($db, $consumer_key, $resource_id) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
SELECT i.item_id, i.item_title, i.item_text, i.item_url, i.max_rating mr, i.step st, i.visible vis, i.sequence seq,
   i.created cr, i.updated upd, COUNT(r.user_id) num, SUM(r.rating) total
FROM {$prefix}item i LEFT OUTER JOIN {$prefix}rating r ON i.item_id = r.item_id
WHERE (i.consumer_key = :consumer_key) AND (i.resource_id = :resource_id)
GROUP BY i.item_id, i.item_title, i.item_text, i.item_url, i.max_rating, i.step, i.visible, i.sequence, i.created, i.updated
ORDER BY i.sequence
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
		$query->execute();

		$rows = $query->fetchAll(PDO::FETCH_CLASS, 'Item');
		if ($rows === FALSE) {
			$rows = array();
		}

		return $rows;

	}


	###
	###  Return an array of ratings made for items for a specified resource link by a specified user
	###
	function getUserRated($db, $consumer_key, $resource_id, $user_consumer_key, $user_id) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
SELECT r.item_id
FROM {$prefix}item i INNER JOIN {$prefix}rating r ON i.item_id = r.item_id
WHERE (i.consumer_key = :consumer_key) AND (i.resource_id = :resource_id) AND (r.consumer_key = :user_consumer_key) AND (r.user_id = :user_id)
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
		$query->bindValue('user_consumer_key', $user_consumer_key, PDO::PARAM_STR);
		$query->bindValue('user_id', $user_id, PDO::PARAM_STR);
		$query->execute();

		$rows  = $query->fetchAll(PDO::FETCH_OBJ);
		$rated = array();
		if ($rows !== FALSE) {
			foreach ($rows as $row) {
				$rated[] = $row->item_id;
			}
		}

		return $rated;

	}


	###
	###  Return details for a specific item for a specified resource link
	###
	function getItem($db, $consumer_key, $resource_id, $item_id) {

		$item = new Item();

		if (!empty($item_id)) {
			$prefix = LTI_DB_TABLENAME_PREFIX;
			$sql    = <<< EOD
SELECT i.item_id, i.item_title, i.item_text, i.item_url, i.max_rating mr, i.step st, i.visible vis, i.sequence seq, i.created cr, i.updated upd
FROM {$prefix}item i
WHERE (i.consumer_key = :consumer_key) AND (i.resource_id = :resource_id) AND (i.item_id = :item_id)
EOD;

			$query = $db->prepare($sql);
			$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
			$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
			$query->bindValue('item_id', $item_id, PDO::PARAM_INT);
			$query->setFetchMode(PDO::FETCH_CLASS, 'Item');
			$query->execute();

			$row = $query->fetch();
			if ($row !== FALSE) {
				$item = $row;
			}
		}

		return $item;

	}


	###
	###  Save the details for an item for a specified resource link
	###
	function saveItem($db, $consumer_key, $resource_id, $item) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		if (!isset($item->item_id)) {
			$sql = <<< EOD
INSERT INTO {$prefix}item (consumer_key, resource_id, item_title, item_text, item_url, max_rating, step, visible, sequence, created, updated)
VALUES (:consumer_key, :resource_id, :item_title, :item_text, :item_url, :max_rating, :step, :visible, :sequence, :created, :updated)
EOD;
		}
		else {
			$sql = <<< EOD
UPDATE {$prefix}item
SET item_title = :item_title, item_text = :item_text, item_url = :item_url, max_rating = :max_rating, step = :step, visible = :visible,
    sequence = :sequence, updated = :updated
WHERE (item_id = :item_id) AND (consumer_key = :consumer_key) AND (resource_id = :resource_id)
EOD;
		}

		$query = $db->prepare($sql);
		if (!isset($item->item_id)) {
			$item->created  = new DateTime();
			$item->sequence = getNumItems($db, $consumer_key, $resource_id) + 1;
			$query->bindValue('created', $item->created->format('Y-m-d H:i:s'), PDO::PARAM_STR);
		}
		else {
			$query->bindValue('item_id', $item->item_id, PDO::PARAM_INT);
		}
		$item->updated = new DateTime();
		$query->bindValue('item_title', $item->item_title, PDO::PARAM_STR);
		$query->bindValue('item_text', $item->item_text, PDO::PARAM_STR);
		$query->bindValue('item_url', $item->item_url, PDO::PARAM_STR);
		$query->bindValue('max_rating', $item->max_rating, PDO::PARAM_INT);
		$query->bindValue('step', $item->step, PDO::PARAM_INT);
		$query->bindValue('visible', $item->visible, PDO::PARAM_INT);
		$query->bindValue('sequence', $item->sequence, PDO::PARAM_INT);
		$query->bindValue('updated', $item->updated->format('Y-m-d H:i:s'), PDO::PARAM_STR);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);

		return $query->execute();

	}


	###
	###  Delete the ratings for an item
	###
	function deleteRatings($db, $item_id) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
DELETE FROM {$prefix}rating
WHERE item_id = :item_id
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('item_id', $item_id, PDO::PARAM_INT);
		$query->execute();

	}


	###
	###  Delete a specific item for a specified resource link including any related ratings
	###
	function deleteItem($db, $consumer_key, $resource_id, $item_id) {

		// Update order for other items for the same resource link
		reorderItem($db, $consumer_key, $resource_id, $item_id, 0);

		// Delete any ratings
		deleteRatings($db, $item_id);

		// Delete the item
		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
DELETE FROM {$prefix}item
WHERE (item_id = :item_id) AND (consumer_key = :consumer_key) AND (resource_id = :resource_id)
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('item_id', $item_id, PDO::PARAM_INT);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
		$ok = $query->execute();

		return $ok;

	}


	###
	###  Change the position of an item in the list displayed for the resource link
	###
	function reorderItem($db, $consumer_key, $resource_id, $item_id, $new_pos) {

		$item = getItem($db, $consumer_key, $resource_id, $item_id);

		$ok = !empty($item->item_id);
		if ($ok) {
			$old_pos = $item->sequence;
			$ok      = ($old_pos != $new_pos);
		}
		if ($ok) {
			$prefix = LTI_DB_TABLENAME_PREFIX;
			if ($new_pos <= 0) {
				$sql = <<< EOD
UPDATE {$prefix}item
SET sequence = sequence - 1
WHERE (consumer_key = :consumer_key) AND (resource_id = :resource_id) AND (sequence > :old_pos)
EOD;
			}
			else {
				if ($old_pos < $new_pos) {
					$sql = <<< EOD
UPDATE {$prefix}item
SET sequence = sequence - 1
WHERE (consumer_key = :consumer_key) AND (resource_id = :resource_id) AND (sequence > :old_pos) AND (sequence <= :new_pos)
EOD;
				}
				else {
					$sql = <<< EOD
UPDATE {$prefix}item
SET sequence = sequence + 1
WHERE (consumer_key = :consumer_key) AND (resource_id = :resource_id) AND (sequence < :old_pos) AND (sequence >= :new_pos)
EOD;
				}
			}

			$query = $db->prepare($sql);
			$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
			$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
			$query->bindValue('old_pos', $old_pos, PDO::PARAM_INT);
			if ($new_pos > 0) {
				$query->bindValue('new_pos', $new_pos, PDO::PARAM_INT);
			}

			$ok = $query->execute();

			if ($ok && ($new_pos > 0)) {
				$item->sequence = $new_pos;
				$ok             = saveItem($db, $consumer_key, $resource_id, $item);
			}

		}

		return $ok;

	}


	###
	###  Save the rating for an item for a specified user
	###
	function saveRating($db, $user_consumer_key, $user_id, $item_id, $rating) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
INSERT INTO {$prefix}rating (item_id, consumer_key, user_id, rating)
VALUES (:item_id, :consumer_key, :user_id, :rating)
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('item_id', $item_id, PDO::PARAM_INT);
		$query->bindValue('consumer_key', $user_consumer_key, PDO::PARAM_STR);
		$query->bindValue('user_id', $user_id, PDO::PARAM_STR);
		$query->bindValue('rating', $rating);

		$ok = $query->execute();

		return $ok;

	}


	###
	###  Update the gradebook with proportion of visible items which have been rated by each user
	###
	function updateGradebook($db, $user_consumer_key = NULL, $user_user_id = NULL) {

		$data_connector = LTI_Data_Connector::getDataConnector(LTI_DB_TABLENAME_PREFIX, $db);
		$consumer       = new LTI_Tool_Consumer($_SESSION['consumer_key'], $data_connector);
		$resource_link  = new LTI_Resource_Link($consumer, $_SESSION['resource_id']);

		$num     = getVisibleItemsCount($db, $_SESSION['consumer_key'], $_SESSION['resource_id']);
		$ratings = getVisibleRatingsCounts($db, $_SESSION['consumer_key'], $_SESSION['resource_id']);
		$users   = $resource_link->getUserResultSourcedIDs();
		foreach ($users as $user) {
			$consumer_key = $user->getResourceLink()->getKey();
			$user_id      = $user->getId();
			$update       = is_null($user_consumer_key) || is_null($user_user_id) || (($user_consumer_key == $consumer_key) && ($user_user_id == $user_id));
			if ($update) {
				if ($num > 0) {
					$count = 0;
					if (isset($ratings[$consumer_key]) && isset($ratings[$consumer_key][$user_id])) {
						$count = $ratings[$consumer_key][$user_id];
					}
					$lti_outcome = new LTI_Outcome(NULL, $count / $num);
					$resource_link->doOutcomesService(LTI_Resource_Link::EXT_WRITE, $lti_outcome, $user);
				}
				else {
					$lti_outcome = new LTI_Outcome();
					$resource_link->doOutcomesService(LTI_Resource_Link::EXT_DELETE, $lti_outcome, $user);
				}
			}
		}

	}


	###
	###  Return a count of visible items for a specified resource link
	###
	function getVisibleItemsCount($db, $consumer_key, $resource_id) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
SELECT COUNT(i.item_id) count
FROM {$prefix}item i
WHERE (i.consumer_key = :consumer_key) AND (i.resource_id = :resource_id) AND (i.visible = 1)
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
		$query->execute();

		$row = $query->fetch(PDO::FETCH_NUM);
		if ($row === FALSE) {
			$num = 0;
		}
		else {
			$num = intval($row[0]);
		}

		return $num;

	}


	###
	###  Return a count of visible ratings made for items for a specified resource link by each user
	###
	function getVisibleRatingsCounts($db, $consumer_key, $resource_id) {

		$prefix = LTI_DB_TABLENAME_PREFIX;
		$sql    = <<< EOD
SELECT r.consumer_key, r.user_id, COUNT(r.item_id) count
FROM {$prefix}item i INNER JOIN {$prefix}rating r ON i.item_id = r.item_id
WHERE (i.consumer_key = :consumer_key) AND (i.resource_id = :resource_id) AND (i.visible = 1)
GROUP BY r.consumer_key, r.user_id
EOD;

		$query = $db->prepare($sql);
		$query->bindValue('consumer_key', $consumer_key, PDO::PARAM_STR);
		$query->bindValue('resource_id', $resource_id, PDO::PARAM_STR);
		$query->execute();

		$rows    = $query->fetchAll(PDO::FETCH_OBJ);
		$ratings = array();
		if ($rows !== FALSE) {
			foreach ($rows as $row) {
				$ratings["{$row->consumer_key}"]["{$row->user_id}"] = $row->count;
			}
		}

		return $ratings;

	}


	###
	###  Get the web path to the application
	###
	function getAppPath() {

		$root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		$dir  = str_replace('\\', '/', dirname(__FILE__));

		$path = str_replace($root, '', $dir) . '/';

		return $path;

	}


	###
	###  Get the URL to the application
	###
	function getAppUrl() {

		$scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
			? 'http'
			: 'https';
		$url    = $scheme . '://' . $_SERVER['HTTP_HOST'] . getAppPath();

		return $url;

	}


	###
	###  Return a string representation of a float value
	###
	function floatToStr($num) {

		$str = sprintf('%f', $num);
		$str = preg_replace('/0*$/', '', $str);
		if (substr($str, -1) == '.') {
			$str = substr($str, 0, -1);
		}

		return $str;

	}


	###
	###  Class representing an item
	###
	class Item {

		public $item_id = NULL;
		public $item_title = '';
		public $item_text = '';
		public $item_url = '';
		public $max_rating = 3;
		public $step = 1;
		public $visible = FALSE;
		public $sequence = 0;
		public $created = NULL;
		public $updated = NULL;
		public $num_ratings = 0;
		public $tot_ratings = 0;

		// ensure non-string properties have the appropriate data type
		function __set($name, $value) {
			if ($name == 'mr') {
				$this->max_rating = intval($value);
			}
			else {
				if ($name == 'st') {
					$this->step = intval($value);
				}
				else {
					if ($name == 'vis') {
						$this->visible = $value == '1';
					}
					else {
						if ($name == 'seq') {
							$this->sequence = intval($value);
						}
						else {
							if ($name == 'cr') {
								$this->created = DateTime::createFromFormat('Y-m-d H:i:s', $value);
							}
							else {
								if ($name == 'upd') {
									$this->updated = DateTime::createFromFormat('Y-m-d H:i:s', $value);
								}
								else {
									if ($name == 'num') {
										$this->num_ratings = intval($value);
									}
									else {
										if ($name == 'total') {
											$this->tot_ratings = floatval($value);
										}
									}
								}
							}
						}
					}
				}
			}

		}

	}
