# Course Email (LTI application)

**Author:** Developed by David Keiser-Clark (dwk2@williams.edu) for the Office for Information Technology at Williams College

**Follow:** If you have collaborative ideas or want to be notified of changes, please email me.

## SCREENSHOT

 - [Course Email: view screenshot](http://www.screencast.com/t/j3QFNbUpcYC "Course Email")

## COURSE EMAIL

**Purpose:** Easily email course participants using your preferred email client (i.e Gmail, Thunderbird, Outlook, Mac Mail, etc.)

**Features:**
 - Global selector: select/deselect all course participants
 - Roles: select/deselect everyone, students, TA's, or teachers
 - Sections: select/deselect everyone within a section (this displays only when > 1 section exists)
 - Add or remove the selectors or manually click checkboxes to get desired list
 - Compose Email: send all selected addresses as recipients to your default email client
 - Copy as Text: manually copy all selected addresses as comma-separated text list
 - Validation: User friendly and helpful validation messages (jQuery)
 - Bells and Whistles: dynamic counts for recipients selected and static totals of everyone, roles, sections
 - More Bells: AJAX loader enables fast LTI load followed by spinner and "fetching data" message; action buttons enabled only after ajax completes its data fetch
 - PHP Curl command fetches participants of current Canvas course (utilizes their API)
 - Bootstrap framework standardizes responsive CSS on all our LTI apps
 - Instructions for configuring Chrome GMail as default email client
 - Application modified as per results of local stress testing; see comments in code
 - Fixed efficiency issues and maximum limits with courses used for large placement exams
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

#### LICENSE

Copyright (c) 2014 David Keiser-Clark

Dual licensed under the MIT and GPL licenses.

Free as in Bacon.
