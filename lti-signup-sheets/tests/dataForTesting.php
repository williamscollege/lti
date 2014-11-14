<?php
	require_once dirname(__FILE__) . '/../classes/auth_base.class.php';
	require_once dirname(__FILE__) . '/../classes/auth_LDAP.class.php';

    require_once dirname(__FILE__) . '/../classes/action.class.php';
    require_once dirname(__FILE__) . '/../classes/role.class.php';
    require_once dirname(__FILE__) . '/../classes/role_action_target.class.php';
    require_once dirname(__FILE__) . '/../classes/user.class.php';
    require_once dirname(__FILE__) . '/../classes/user_role.class.php';

	/*
	This file contains a series of methods for creating known test data in a target database
	*/
// NOTE !!!!!!!!!!!!!!!!!!!!
// Actions and Roles are pre-populated and fixed - there is no creation nor removal of test data for those tables

/*
function createTestData_XXXX($dbConn) {
    // 1100 series ids
    # XXXX: user_id, created_at, updated_at, username, screen_name, flag_is_system_admin, flag_is_banned, flag_delete
    $addTestSql  = "INSERT INTO " . XXXX::$dbTable . " VALUES
        (1,NOW(),NOW())
    ";
    $addTestStmt = $dbConn->prepare($addTestSql);
    $addTestStmt->execute();
    if ($addTestStmt->errorInfo()[0] != '0000') {
        echo "<pre>error adding test XXXX data to the DB\n";
        print_r($addTestStmt->errorInfo());
        debug_print_backtrace();
        exit;
    }
}
*/
    $ACTIONS = array();



    function createTestData_Role_Action_Targets($dbConn) {
        // 200 series ids
        // Role_Action_Target: 'role_action_target_link_id', 'created_at', 'updated_at', 'last_user_id', 'role_id', 'action_id', 'target_type', 'target_id', 'flag_delete'
        // VALID_TARGET_TYPES = ['global_notebook', 'global_metadata', 'global_plants', 'global_specimens', 'notebook', 'metadata', 'plant', 'specimen'];
        $addTestSql  = "INSERT INTO " . Role_Action_Target::$dbTable . " VALUES
                        (201,NOW(),NOW(), 110, 2, 1, 'global_notebook', 0, 0),
                        (202,NOW(),NOW(), 110, 2, 2, 'global_notebook', 0, 0),
                        (203,NOW(),NOW(), 110, 2, 2, 'global_metadata', 0, 0),
                        (204,NOW(),NOW(), 110, 2, 2, 'global_plant', 0, 0),
                        (205,NOW(),NOW(), 110, 2, 2, 'global_specimen', 0, 0),
                        (206,NOW(),NOW(), 110, 2, 1, 'global_metadata', 0, 0),
                        (207,NOW(),NOW(), 110, 3, 1, 'global_metadata', 0, 0),
                        (208,NOW(),NOW(), 110, 4, 1, 'global_metadata', 0, 0),
                        (209,NOW(),NOW(), 110, 2, 1, 'global_plant', 0, 0),
                        (210,NOW(),NOW(), 110, 3, 1, 'global_plant', 0, 0),
                        (211,NOW(),NOW(), 110, 4, 1, 'global_plant', 0, 0),
                        (212,NOW(),NOW(), 110, 3, 1, 'notebook', 1004, 0),
                        (213,NOW(),NOW(), 110, 4, 1, 'notebook', 1004, 0),
                        (214,NOW(),NOW(), 110, 2, 3, 'global_metadata', 0, 0),
                        (215,NOW(),NOW(), 110, 2, 3, 'global_notebook', 0, 0),
                        (216,NOW(),NOW(), 110, 2, 3, 'global_specimen', 0, 0),
                        (217,NOW(),NOW(), 110, 2, 4, 'global_metadata', 0, 0),
                        (218,NOW(),NOW(), 110, 2, 4, 'global_notebook', 0, 0),
                        (219,NOW(),NOW(), 110, 2, 4, 'global_specimen', 0, 0),
                        (220,NOW(),NOW(), 110, 3, 4, 'global_notebook', 0, 0),
                        (221,NOW(),NOW(), 110, 3, 4, 'global_specimen', 0, 0)
                    ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Role_Action_Targets data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_LMS_Users($dbConn) {
        // 100 series ids
        # user: user_id, username, email, first_name, last_name, screen_name, created_at, updated_at, flag_is_system_admin, flag_is_banned, flag_delete
        // 101-104 are field user
        // 105 is assistant
        // 106-108 are field user
        // 109 has no roles (implicit 'public' role)
        // 110 is manager
        $addTestSql  = "INSERT INTO " . User::$dbTable . " VALUES
            (101,'" . Auth_Base::$TEST_USERNAME . "','" . Auth_Base::$TEST_LNAME . ", " . Auth_Base::$TEST_FNAME . "',0,0,0),
            (102,'testUser2','tusr2@williams.edu','tu2F','tu2L','tu2L, tu2F',NOW(),NOW(),0,0,0),
            (103,'testUser3','tusr3@williams.edu','tu3F','tu3L','tu3L, tu3F',NOW(),NOW(),0,0,0),
            (104,'testUser4','tusr4@williams.edu','tu4F','tu4L','tu4L, tu4F',NOW(),NOW(),0,0,0),
            (105,'testUser5','tusr5@williams.edu','tu5F','tu5L','tu5L, tu5F',NOW(),NOW(),0,0,0),
            (106,'testUser6','tusr6@williams.edu','tu6F','tu6L','tu6L, tu6F',NOW(),NOW(),1,0,0),
            (107,'testUser7','tusr7@williams.edu','tu7F','tu7L','tu7L, tu7F',NOW(),NOW(),0,1,0),
            (108,'testUser8','tusr8@williams.edu','tu8F','tu8L','tu8L, tu8F',NOW(),NOW(),0,0,1),
            (109,'testUser9','tusr9@williams.edu','tu9F','tu9L','tu9L, tu9F',NOW(),NOW(),0,0,0),
            (110,'testUser10','tusr10@williams.edu','tu10F','tuL','tu10L, tu10F',NOW(),NOW(),0,0,0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Users data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function makeAuthedTestUserAdmin($dbConn) {
        $u1                       = User::getOneFromDb(['username' => TESTINGUSER], $dbConn);
        $u1->flag_is_system_admin = TRUE;
        $u1->updateDb();
    }

    function createTestData_User_Roles($dbConn) {
        // 300 series ids
        # User_Role: 'user_role_link_id', 'created_at', 'updated_at', 'last_user_id', 'user_id', 'role_id'
        $addTestSql  = "INSERT INTO " . User_Role::$dbTable . " VALUES
            (301,NOW(),NOW(),110,101,3),
            (302,NOW(),NOW(),110,102,3),
            (303,NOW(),NOW(),110,103,3),
            (304,NOW(),NOW(),110,104,3),
            (305,NOW(),NOW(),110,105,2),
            (306,NOW(),NOW(),110,106,3),
            (307,NOW(),NOW(),110,107,3),
            (308,NOW(),NOW(),110,110,1),
            (309,NOW(),NOW(),110,108,3)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test User_Role data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

//--------------------------------------------------------------------------------------------------------------

	function createAllTestData($dbConn) {
        createTestData_Role_Action_Targets($dbConn);
        createTestData_Users($dbConn);
        createTestData_User_Roles($dbConn);

        $all_actions = Action::getAllFromDb([],$dbConn);
        global $ACTIONS;
        foreach ($all_actions as $a) {
            $ACTIONS[$a->name] = $a;
        }
	}

//--------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------

	function _removeTestDataFromTable($dbConn, $tableName) {
		$sql = "DELETE FROM $tableName";
		//echo "<pre>" . $sql . "\n</pre>";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

    function removeTestData_EXAMPLE($dbConn) {
        _removeTestDataFromTable($dbConn, Metadata_Structure::$dbTable);
    }


    function removeTestData_Role_Action_Targets($dbConn) {
        $sql = "DELETE FROM ".Role_Action_Target::$dbTable." WHERE ".Role_Action_Target::$primaryKeyField." > 100";
        //echo "<pre>" . $sql . "\n</pre>";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    }
    function removeTestData_Users($dbConn) {
        $sql = "DELETE FROM ".User::$dbTable." WHERE ".User::$primaryKeyField." > 1";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    }
    function removeTestData_User_Roles($dbConn) {
        $sql = "DELETE FROM ".User_Role::$dbTable." WHERE ".User_Role::$primaryKeyField." > 1";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    }

//--------------------------------------------------------------------------------------------------------------

	function removeAllTestData($dbConn) {
        removeTestData_Role_Action_Targets($dbConn);
        removeTestData_Users($dbConn);
        removeTestData_User_Roles($dbConn);

	}