<?php
	/***********************************************
	 ** LTI Application: "Generic Kaltura Embed"
	 ** Purpose: Build a dynamic LTI video player that will play the requested video based on Kaltura params (entry_id, wid) while leveraging Canvas LDAP authentication.
	 ** Author: David Keiser-Clark, Williams College
	 ** Current features:
	 **  - Requires two Kaltura params (entry_id, wid)
	 **  - Builds video player based on Kaltura params
	 ***********************************************/


	require_once('lib.php');
	require_once(dirname(__FILE__) . '/util.php');

	// Initialise database
	$db = NULL;
	init($db);

	// get parent referer url and querystring params
	$http_referer = $_SERVER['HTTP_REFERER'];

	// Security: Exit if http_referer missing
	if (!$http_referer) {
		echo "<br /><br />iframe likely not present.<br />URL=" . $_SERVER['REQUEST_URI'] . "<br />Missing http_referer. Exiting.<br />";
		exit;
	}

	// Security: Expected http_referer must match the following
	if ( (strpos($http_referer, "glow.williams.edu")) || (strpos($http_referer, "glow2.williams.edu")) || (strpos($http_referer, "williams.instructure.com")) || (strpos($http_referer, "williams.test.instructure.com")) || (strpos($http_referer, "williams.beta.instructure.com")) ) {
		// get querystring
		$querystring = substr($http_referer, strpos($http_referer, "?"));

		// explode individual querystring values into an array (delimiter: "&")
		$array_qs_parts = array();
		$array_qs_parts = explode("&", $querystring);

		// Security: Exit if expected querystring values are missing
		if (!$array_qs_parts[0]) {
			echo "Missing array. Exiting.<br />";
			exit;
		}
		elseif (!$array_qs_parts[1]) {
			echo "Missing array. Exiting.<br />";
			exit;
		}
		// Security: Exit if expected querystring values are not expected size
		if (strlen($array_qs_parts[0]) <= 0 || strlen($array_qs_parts[1]) <= 0) {
			echo "Missing parameters received. Exiting.<br />";
			exit;
		}
		elseif (strlen($array_qs_parts[0]) > 15 || strlen($array_qs_parts[1]) > 15) {
			echo "Invalid parameters received. Exiting.<br />";
			exit;
		}

		$kaltura_entry_id = $array_qs_parts[0];
		$kaltura_wid      = $array_qs_parts[1];

		// construct kaltura iframe string
		$kaltura_iframe = '<iframe id="kaltura_player" src="https://cdnapisec.kaltura.com/p/1384471/sp/138447100/embedIframeJs/uiconf_id/12892131/partner_id/1384471?iframeembed=true&amp;playerId=kaltura_player&amp;entry_id=' . $kaltura_entry_id . '&amp;flashvars[mediaProtocol]=rtmp&amp;flashvars[streamerType]=rtmp&amp;flashvars[streamerUrl]=rtmp://www.kaltura.com:1935&amp;flashvars[rtmpFlavors]=1&amp;&amp;wid=' . $kaltura_wid . '" width="400" height="285" style="width: 800px; height: 570px" allowfullscreen webkitallowfullscreen mozAllowFullScreen frameborder="0"></iframe>';
	}
	else {
		echo "Invalid refering site. Exiting.<br />";
		exit;
	}


	/* TEST OUTPUT:
	echo '<br /><br /><strong>Debugging:</strong>';
	echo "<br />HTTP_REFERER : " . $_SERVER['HTTP_REFERER'];
	echo '<br />Tool Provider: ' . LTI_TOOL_PROVIDER_SERVER_NAME;
	echo "<br />URL: " . $url;
	echo "<br />Querystring: " . $querystring;
	echo "<br />Querystring (array of values):";
	util_prePrintR($array_qs_parts);
	*/

	/*
	// Alternate Javascript version
	<script type="text/javascript">

	function getParentUrl() {
		var isInIframe = (parent !== window),
			parentUrl = null;
		if (isInIframe) {
			parentUrl = document.referrer;
		}
		return parentUrl;
	}

	alert(getParentUrl());

	var url = getParentUrl();
	var i = url.find('?');
	var j = url.find('&', i);

	if (i > -1) {
		var entry_id = url.substring(i + 1, j - i);
		var wid = url.substring(j + 1);

		document.write('<iframe id="kaltura_player" src="https://cdnapisec.kaltura.com/p/1384471/sp/138447100/embedIframeJs/uiconf_id/12892131/partner_id/1384471?iframeembed=true&amp;playerId=kaltura_player&amp;entry_id=' + entry_id + '&amp;fla\
	 shvars[mediaProtocol]=rtmp&amp;flashvars[streamerType]=rtmp&amp;flashvars[streamerUrl]=rtmp://www.kaltura.com:1935&amp;flashvars[rtmpFlavors]=1&amp;&amp;wid=' + wid + '" width="400" height="285" allowfullscreen webkitallowfullscreen moz\
	 AllowFullScreen frameborder="0"></iframe>');
	}
	else {
		document.write('missing id');
	}
	</script>
	*/

?>
<!DOCTYPE html>
<html>
<head>
	<title>Generic Kaltura Embed</title>
	<!-- Bootstrap: ensure proper rendering and touch zooming -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- CSS Framework: Bootstrap -->
	<!-- CSS Custom -->
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<!-- JavaScript Bootstrap plugins (compiled) -->
	<!-- JavaScript Custom -->
</head>
<body>

<?php
	echo $kaltura_iframe;
?>

</body>
</html>