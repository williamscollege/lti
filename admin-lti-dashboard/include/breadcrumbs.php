<?php
	// set path, and strip off left-most character
	$pathFull = substr($_SERVER["SCRIPT_NAME"], 1);

	// explode url into array as per delimiter
	$arrayPathBits = explode("/", $pathFull);

	// display contents of array
	$homePath    = "";
	$filePath    = "";
	$cntItem     = 0;
	$cntPathBits = count($arrayPathBits);

	foreach ($arrayPathBits as $thisBit) {
		$cntItem += 1;
		if ($cntItem < $cntPathBits) {
			$homePath = $homePath . '/' . $thisBit;
		}
		else {
			$filePath = $homePath . '/' . $thisBit;
		}
	};
	// display home path root
	echo "<a href=\"$homePath\" title=\"Home\">Home</a>";
	// display destination path
	echo " &#x25BA; <code>$thisBit</code>";
	// echo " &#x25BA; <a href=\"$filePath\" title=\"$filePath\">$thisBit</a>";


