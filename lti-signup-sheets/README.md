# Signup Sheets (LTI application)

**Author:** Developed by David Keiser-Clark (dwk2@williams.edu) for the Office for Information Technology at Williams College

**Follow:** If you have collaborative ideas or want to be notified of changes, please email me.

## SCREENSHOT

TODO: Update this
 - [Signup Sheets: view screenshot](http://www.screencast.com/ "Signup Sheets (LTI application)")

## SIGNUP SHEETS

**Purpose:** This tool lets any user create a sheet with openings at specific times, and then allows other users to sign up for those openings. This is analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name - e.g. signing up for a particular lab slot, scheduling office hours, picking a study group time, or more general things like planning a party.

**Features:**
 - TODO
 - ...
 - This codebase utilizes (and slightly forks) Stephen P Vickers sample LTI "Rating" project (http://www.spvsoftwareproducts.com/php/rating/). Thank you Stephen.

**Dependencies: Install:**
 - Apache, PHP 5.2 (or higher), MySQL 5x, phpMyAdmin, emacs

**Dependencies: Enable PHP modules:**
 - PDO, curl, mbyte, dom

**Create access and security:**
 - sudo access on machine
 - mysql root access
 - implement .htaccess for /admin/ folder

**Software dependencies:**
 - [PHP LTI Tool Provider class - download](http://projects.oscelot.org/gf/project/php-basic-lti/ "PHP LTI Tool Provider class - download")
 - My improvements to "/lti/LTI_Tool_Provider.php" on lines 167, 376, 496 (hint: search for "Modifications")
 - [Bootstrap CSS Framework](http://getbootstrap.com/getting-started/#download "Bootstrap CSS Framework")
 - [jQuery JavaScript Library](http://jquery.com/ "jQuery JavaScript Library")
 - [jQuery UI JavaScript Library](http://jqueryui.com/ "jQuery UI JavaScript Library")
 - [jQuery Validation Plugin](http://jqueryvalidation.org/ "jQuery Validation Plugin")
 - [jQuery BOOTBOXJS Plugin](http://bootboxjs.com/ "jQuery BootBoxJS Plugin")

#### LICENSE

Copyright (c) 2014 David Keiser-Clark

Dual licensed under the MIT and GPL licenses.

Free as in Bacon.
