<?php
    session_start();

    require_once('../institution.cfg.php');
    require_once('../lang.cfg.php');

    require_once('../classes/ALL_CLASS_INCLUDES.php');

    require_once('../auth.cfg.php');
    require_once('../util.php');

    # TODO: validate the request (user logged in, fingerprint checks out)
    if (! array_key_exists('isAuthenticated',$_SESSION) || ! $_SESSION['isAuthenticated']) {
        echo 'not authenticated';
        exit;
    }
    $FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks
    if ($_SESSION['fingerprint'] != $FINGERPRINT) {
        echo 'bad fingerprint';
        exit;
    }

	# Create database connection object
	$DB = util_createDbConnection();

    $all_actions = Action::getAllFromDb(['flag_delete'=>false],$DB);
    $ACTIONS = array();
    foreach ($all_actions as $a) {
        $ACTIONS[$a->name] = $a;
    }

    $USER = User::getOneFromDb(['username' => $_SESSION['userdata']['username']],$DB);
    if (! $USER->matchesDb) {
        echo 'user did not load correctly';
        exit;
    }

    #------------------------------------------------#
    # Set default return value
    #------------------------------------------------#
    $results = [
        'status' => 'failure',
        'note'   => 'unknown reason',
        'html_output'   => ''
    ];
?>