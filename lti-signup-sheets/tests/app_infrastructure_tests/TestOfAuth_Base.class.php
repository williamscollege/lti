<?php

require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';
require_once dirname(__FILE__) . '/../../auth.cfg.php';

class TestOfAuth_Base extends UnitTestCase {

    function testClassExists() {
        // no real test - the require at the top of the file is enough
        $this->assertTrue(1==1);
    }

    function testAuthenticateTestUser() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_Base();        
        $this->assertTrue($AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD));
    }

    function testAuthenticateNontestUserFails() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_Base();        
        $this->assertFalse($AUTH->authenticate(TESTINGUSER.'foo',TESTINGPASSWORD));
    }

    function testAuthenticateTestUserBaddPasswordFails() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_Base();        
        $this->assertFalse($AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD.'foo'));
    }

    function testAuthHasNecessaryFieldsForEqUserCreation() {
        $AUTH = new Auth_Base();

        $AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD);

        $this->assertEqual($AUTH->username,Auth_Base::$TEST_USERNAME);
        $this->assertEqual($AUTH->fname,Auth_Base::$TEST_FNAME);
        $this->assertEqual($AUTH->lname,Auth_Base::$TEST_LNAME);
        $this->assertEqual($AUTH->sortname,Auth_Base::$TEST_SORTNAME);
        $this->assertEqual($AUTH->email,Auth_Base::$TEST_EMAIL);
        $this->assertEqual($AUTH->inst_groups,Auth_Base::$TEST_INST_GROUPS);
    }

}
?>