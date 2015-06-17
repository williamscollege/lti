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
 * This page processes a launch request from an LTI tool consumer.
 */

  require_once('lib.php');


// Cancel any existing session
  session_name(SESSION_NAME);
  session_start();
  $_SESSION = array();
  session_destroy();

  class Rating_LTI_Tool_Provider extends LTI_Tool_Provider {

    function __construct($data_connector = '', $callbackHandler = NULL) {

      parent::__construct($data_connector, $callbackHandler);
      $this->baseURL = getAppUrl();

    }

    function onLaunch() {

      global $db;

// Check the user has an appropriate role
      if ($this->user->isLearner() || $this->user->isStaff()) {
// Initialise the user session
        $_SESSION['consumer_key'] = $this->consumer->getKey();
        $_SESSION['resource_id'] = $this->resource_link->getId();
        $_SESSION['user_consumer_key'] = $this->user->getResourceLink()->getConsumer()->getKey();
        $_SESSION['user_id'] = $this->user->getId();
        $_SESSION['isStudent'] = $this->user->isLearner();
        $_SESSION['isContentItem'] = FALSE;

// Redirect the user to display the list of items for the resource link
        $this->redirectURL = getAppUrl();

      } else {

        $this->reason = 'Invalid role.';
        $this->isOK = FALSE;

      }

    }

    function onContentItem() {

// Check that the Tool Consumer is allowing the return of an LTI link
      $this->isOK = in_array(LTI_Content_Item::LTI_LINK_MEDIA_TYPE, $this->mediaTypes) || in_array('*/*', $this->mediaTypes);
      if (!$this->isOK) {
        $this->reason = 'Return of an LTI link not offered';
      } else {
        $this->isOK = !in_array('none', $this->documentTargets) || (count($this->documentTargets) > 1);
        if (!$this->isOK) {
          $this->reason = 'No visible document target offered';
        }
      }
      if ($this->isOK) {
// Initialise the user session
        $_SESSION['consumer_key'] = $this->consumer->getKey();
        $_SESSION['resource_id'] = getGuid();
        $_SESSION['resource_id_created'] = FALSE;
        $_SESSION['user_consumer_key'] = $_SESSION['consumer_key'];
        $_SESSION['user_id'] = 'System';
        $_SESSION['isStudent'] = FALSE;
        $_SESSION['isContentItem'] = TRUE;
        $_SESSION['lti_version'] = $_POST['lti_version'];
        $_SESSION['return_url'] = $this->return_url;
        $_SESSION['title'] = postValue('title');
        $_SESSION['text'] = postValue('text');
        $_SESSION['data'] = postValue('data');
        $_SESSION['document_targets'] = $this->documentTargets;
// Redirect the user to display the list of items for the resource link
        $this->redirectURL = getAppUrl();
      }

    }

    function onDashboard() {

      global $db;

      $title = APP_NAME;
      $app_url = 'http://www.spvsoftwareproducts.com/php/rating/';
      $icon_url = getAppUrl() . 'images/icon50.png';
      $context_id = postValue('context_id', '');
      if (empty($context_id)) {
        $ratings = getUserSummary($db, $this->user->getResourceLink()->getConsumer()->getKey(), $this->user->getId());
        $num_ratings = count($ratings);
        $courses = array();
        $lists = array();
        $tot_rating = 0;
        foreach ($ratings as $rating) {
          $courses[$rating->lti_context_id] = TRUE;
          $lists[$rating->resource_id] = TRUE;
          $tot_rating += ($rating->rating / $rating->max_rating);
        }
        $num_courses = count($courses);
        $num_lists = count($lists);
        if ($num_ratings > 0) {
          $av_rating = floatToStr($tot_rating / $num_ratings * 5);
        }
        $html = <<< EOD
        <p>
          Here is a summary of your rating of items:
        </p>
        <ul>
          <li><em>Number of courses:</em> {$num_courses}</li>
          <li><em>Number of rating lists:</em> {$num_lists}</li>
          <li><em>Number of ratings made:</em> {$num_ratings}</li>

EOD;
        if ($num_ratings > 0) {
          $html .= <<< EOD
          <li><em>Average rating:</em> {$av_rating} out of 5</li>

EOD;
        }
        $html .= <<< EOD
        </ul>

EOD;
        $this->output = $html;
      } else {
        if ($this->user->isLearner()) {
          $ratings = getUserRatings($db, $this->consumer->getKey(), $this->resource_link->lti_context_id, $this->user->getResourceLink()->getConsumer()->getKey(), $this->user->getId());
        } else {
          $ratings = getContextRatings($db, $this->consumer->getKey(), $this->resource_link->lti_context_id);
        }
        $resources = array();
        $totals = array();
        foreach ($ratings as $rating) {
          $tot = ($rating->rating / $rating->max_rating);
          if (array_key_exists($rating->title, $resources)) {
            $resources[$rating->title] += 1;
            $totals[$rating->title] += $tot;
          } else {
            $resources[$rating->title] = 1;
            $totals[$rating->title] = $tot;
          }
        }
        ksort($resources);
        $items = '';
        foreach ($resources as $title => $value) {
          $av = floatToStr($totals[$title] / $value * 5);
          $plural = '';
          if ($value <> 1) {
            $plural = 's';
          }
          $items .= <<< EOD
    <item>
      <title>{$title}</title>
      <description>{$value} item{$plural} rated (average {$av} out of 5)</description>
    </item>
EOD;
        }
        $rss = <<< EOD
<rss xmlns:a10="http://www.w3.org/2005/Atom" version="2.0">
  <channel>
    <title>Dashboard</title>
    <link>{$app_url}</link>
    <description />
    <image>
      <url>{$icon_url}</url>
      <title>Dashboard</title>
      <link>{$app_url}</link>
      <description>{$title} Dashboard</description>
    </image>{$items}
  </channel>
</rss>
EOD;
        header('Content-type: text/xml');
        $this->output = $rss;
      }

    }

    function onRegister() {

// Initialise the user session
      $_SESSION['consumer_key'] = $this->consumer->getKey();
      $_SESSION['tc_profile_url'] = $_POST['tc_profile_url'];
      $_SESSION['tc_profile'] = $this->consumer->profile;
      $_SESSION['launch_presentation_return_url'] = $_POST['launch_presentation_return_url'];

// Redirect the user to process the registration
      $this->redirectURL = getAppUrl() . 'register.php';

    }

    function onError() {

      $msg = $this->message;
      if ($this->debugMode && !empty($this->reason)) {
        $msg = $this->reason;
      }
      $title = APP_NAME;

      $this->error_output = <<< EOD
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
<h1>Error</h1>
<p style="font-weight: bold; color: #f00;">{$msg}</p>
</body>
</html>
EOD;
    }

  }

// Initialise database
  $db = NULL;
  if (init($db)) {
    $data_connector = LTI_Data_Connector::getDataConnector(DB_TABLENAME_PREFIX, $db);
    $tool = new Rating_LTI_Tool_Provider($data_connector);
    $tool->setParameterConstraint('oauth_consumer_key', TRUE, 50);
    $tool->setParameterConstraint('resource_link_id', TRUE, 50, array('basic-lti-launch-request'));
    $tool->setParameterConstraint('user_id', TRUE, 50, array('basic-lti-launch-request'));
    $tool->setParameterConstraint('roles', TRUE, NULL, array('basic-lti-launch-request'));
  } else {
    $tool = new Rating_LTI_Tool_Provider(NULL);
    $tool->reason = $_SESSION['error_message'];
  }
  $tool->handle_request();

?>
