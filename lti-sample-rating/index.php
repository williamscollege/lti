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
	 * This page displays a list of items for a resource link.  Students are able to rate
	 * each item; staff may add, edit, re-order and delete items.
	 */

	require_once('lib.php');

	// Initialise session and database
	$db = NULL;
	# original
	$ok = init($db, TRUE);
	# DKC-Modification: change to false
	# $ok = init($db, FALSE);

	// Initialise parameters
	$id = 0;
	if ($ok) {
		$action = '';
		// Check for item id and action parameters
		if (isset($_REQUEST['id'])) {
			$id = intval($_REQUEST['id']);
		}
		if (isset($_REQUEST['do'])) {
			$action = $_REQUEST['do'];
		}
		// Process add/update item action
		if ($action == 'add') {
			$update_item             = getItem($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], $id);
			$update_item->item_title = $_POST['title'];
			$update_item->item_text  = $_POST['text'];
			$update_item->item_url   = $_POST['url'];
			$update_item->max_rating = intval($_POST['max_rating']);
			$update_item->step       = intval($_POST['step']);
			$was_visible             = $update_item->visible;
			$update_item->visible    = isset($_POST['visible']);
			// Ensure all required fields have been provided
			if (isset($_POST['id']) && isset($_POST['title']) && !empty($_POST['title'])) {
				if (saveItem($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], $update_item)) {
					$_SESSION['message'] = 'The item has been saved.';
					if ($update_item->visible != $was_visible) {
						updateGradebook($db);
					}
				}
				else {
					$_SESSION['error_message'] = 'Unable to save the item; please check the data and try again.';
				}
				header('Location: ./');
				exit;
			}

			// Process delete item action
		}
		else {
			if ($action == 'delete') {
				$update_item = getItem($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], $id);
				$was_visible = $update_item->visible;
				if (deleteItem($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], $id)) {
					$_SESSION['message'] = 'The item has been deleted.';
					if ($was_visible) {
						updateGradebook($db);
					}
				}
				else {
					$_SESSION['error_message'] = 'Unable to delete the item; please try again.';
				}
				header('Location: ./');
				exit;

				// Process reorder item action
			}
			else {
				if (($action == 'reorder') && (isset($_GET['seq']))) {
					if (reorderItem($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], intval($_GET['id']), intval($_GET['seq']))) {
						$_SESSION['message'] = 'The item has been moved.';
					}
					else {
						$_SESSION['error_message'] = 'Unable to move the item; please try again.';
					}
					header('Location: ./');
					exit;

				}
				else {
					// Initialise an empty item instance
					$update_item = new Item();
				}
			}
		}

		// Fetch a list of existing items for the resource link
		$items = getItems($db, $_SESSION['consumer_key'], $_SESSION['resource_id']);

		if ($_SESSION['isStudent']) {
			// Fetch a list of ratings for items for the resource link for the student
			$user_rated = getUserRated($db, $_SESSION['consumer_key'], $_SESSION['resource_id'], $_SESSION['user_consumer_key'], $_SESSION['user_id']);
		}

	}

	// Page header
	$title = APP_NAME;

	$page = <<< EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-language" content="EN" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>{$title}</title>
<link href="css/rateit.css" media="screen" rel="stylesheet" type="text/css" />
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery.rateit.min.js" type="text/javascript"></script>
<link href="css/rating.css" media="screen" rel="stylesheet" type="text/css" />
</head>

<body>

EOD;

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

		if (count($items) <= 0) {
			$page .= <<< EOD
<p>No items have been added yet.</p>

EOD;
		}
		else {
			$page .= <<< EOD
<table class="items" border="0" cellpadding="3">
<tbody>

EOD;
			$row = 0;
			foreach ($items as $item) {
				if (!$_SESSION['isStudent'] || $item->visible) {
					$row++;
					if (!empty($id) && ($id == $item->item_id)) {
						$update_item = $item;
					}
					if (!$item->visible) {
						$trclass = 'notvisible';
						$row--;
					}
					else {
						if (($row % 2) == 1) {
							$trclass = 'oddrow';
						}
						else {
							$trclass = 'evenrow';
						}
					}
					if (isset($item->item_url)) {
						$title = '<a href="' . $item->item_url . '" target="_blank">' . $item->item_title . '</a>';
					}
					else {
						$title = $item->item_title;
					}
					if (!$item->visible) {
						$title .= ' [hidden]';
					}
					if (isset($item->item_text)) {
						$text = "<br />\n{$item->item_text}";
					}
					else {
						$text = '';
					}
					$step     = 1.0 / $item->step;
					$value    = '0';
					$readonly = 'true';
					if ($_SESSION['isStudent'] && !in_array(strval($item->item_id), $user_rated)) {
						$readonly = 'false';
					}
					else {
						if ($item->num_ratings > 0) {
							$value = floatToStr($item->tot_ratings / $item->num_ratings);
						}
					}
					$page .= <<< EOD
  <tr class="{$trclass}">
    <td><span class="title">{$title}</span>{$text}</td>
    <td><div class="rateit" data-id="{$item->item_id}" title="{$value}" data-rateit-min="0" data-rateit-max="{$item->max_rating}" data-rateit-step="{$step}" data-rateit-value="{$value}" data-rateit-readonly="false"></div></td>
    <!-- data-rateit-readonly="{$readonly}" -->

EOD;
					if (!$_SESSION['isStudent']) {
						$page .= <<< EOD
    <td class="aligncentre">
      <select name="seq{$item->item_id}" onchange="location.href='./?do=reorder&amp;id={$item->item_id}&amp;seq='+this.value;" class="alignright">

EOD;
						for ($i = 1; $i <= count($items); $i++) {
							if ($i == $item->sequence) {
								$sel = ' selected="selected"';
							}
							else {
								$sel = '';
							}
							$page .= <<< EOD
        <option value="{$i}"{$sel}>{$i}</option>

EOD;
						}
						$page .= <<< EOD
      </select>
    </td>
    <td class="iconcolumn aligncentre">
      <a href="./?id={$item->item_id}"><img src="images/edit.png" title="Edit item" alt="Edit item" /></a>&nbsp;<a href="./?do=delete&amp;id={$item->item_id}" onclick="return confirm('Delete item; are you sure?');"><img src="images/delete.png" title="Delete item" alt="Delete item" /></a>
    </td>

EOD;
					}
					$page .= <<< EOD
  </tr>

EOD;
				}
			}
			$page .= <<< EOD
</tbody>
</table>

EOD;
		}
	}

	// Display form for adding/editing an item
	if ($ok && !$_SESSION['isStudent']) {
		if (isset($update_item->item_id)) {
			$mode = 'Update';
		}
		else {
			$mode = 'Add new';
		}
		$title = htmlentities($update_item->item_title);
		$url   = htmlentities($update_item->item_url);
		$text  = htmlentities($update_item->item_text);
		if ($update_item->visible) {
			$checked = ' checked="checked"';
		}
		else {
			$checked = '';
		}
		$page .= <<< EOD
<h2>{$mode} item</h2>

<form action="./" method="post">
<div class="box">
  <span class="label">Title:<span class="required" title="required">*</span></span>&nbsp;<input name="title" type="text" size="50" maxlength="200" value="{$title}" /><br />
  <span class="label">URL:</span>&nbsp;<input name="url" type="text" size="75" maxlength="200" value="{$url}" /><br />
  <span class="label">Description:</span>&nbsp;<textarea name="text" rows="3" cols="60">{$text}</textarea><br />
  <span class="label">Visible?</span>&nbsp;<input name="visible" type="checkbox" value="1"{$checked} /><br />
  <span class="label">Maximum rating:<span class="required" title="required">*</span></span>&nbsp;<select name="max_rating">

EOD;
		for ($i = 3; $i <= 10; $i++) {
			if ($i == $update_item->max_rating) {
				$sel = ' selected="selected"';
			}
			else {
				$sel = '';
			}
			$page .= <<< EOD
    <option value="{$i}"{$sel}>{$i}</option>

EOD;
		}
		$sel1 = '';
		$sel2 = '';
		$sel4 = '';
		if ($update_item->step == 1) {
			$sel1 = ' selected="selected"';
		}
		if ($update_item->step == 2) {
			$sel2 = ' selected="selected"';
		}
		if ($update_item->step == 4) {
			$sel4 = ' selected="selected"';
		}
		$page .= <<< EOD
  </select><br />
  <span class="label">Rating step:<span class="required" title="required">*</span></span>&nbsp;<select name="step">
    <option value="4"{$sel4}>0.25</option>
    <option value="2"{$sel2}>0.5</option>
    <option value="1"{$sel1}>1</option>
  </select><br />
  <br />
  <input type="hidden" name="do" value="add" />
  <input type="hidden" name="id" value="{$id}" />
  <span class="label"><span class="required" title="required">*</span>&nbsp;=&nbsp;required field</span>&nbsp;<input type="submit" value="{$mode} item" />

EOD;

		if (isset($update_item->item_id)) {
			$page .= <<< EOD
  &nbsp;<input type="reset" value="Cancel" onclick="location.href='./';" />

EOD;
		}
	}

	// Page footer
	$page .= <<< EOD
</div>
</form>
<script type="text/javascript">
$('.rateit').bind('over', function (event, value) {
  $(this).attr('title', value);
});

$('.rateit').bind('rated reset', function (event) {
//alert('fish');
  var ri = $(this);

  var value = ri.rateit('value');
  var id = ri.data('id');

  $.ajax({
    url: 'rating.php',
    data: { id: id, value: value },
    dataType: 'json',
    type: 'POST',
    success: function (data) {
      if (data.response == 'Success') {
        ri.rateit('readonly', true);
        alert('Your rating has been saved.');
      } else {
        ri.rateit('value', 0);
        alert('Unable to save your rating; please try again.');
      }
    },
    error: function (jxhr, msg, err) {
      ri.rateit('value', 0);
      alert('Unable to save your rating; please try again.');
    }
  });
});
</script>
</body>
</html>

EOD;

	// Display page
	echo $page;

?>
