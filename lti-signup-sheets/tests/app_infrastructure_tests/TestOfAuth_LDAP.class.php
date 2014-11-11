<?php

//require_once dirname(__FILE__) . '/../../classes/auth_LDAP.class.php';
require_once dirname(__FILE__) . '/../../auth.cfg.php';

class TestOfAuth_LDAP extends UnitTestCase {

    function testClassExists() {
        // no real test - the require at the top of the file is enough
        $this->assertTrue(1==1);
    }

    function testAuthenticateTestUser() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_LDAP();        
        $this->assertTrue($AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD));
    }

    function testAuthenticateNontestUserFails() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_LDAP();        
        $this->assertFalse($AUTH->authenticate(TESTINGUSER.'foo',TESTINGPASSWORD));
    }

    function testAuthenticateTestUserBaddPasswordFails() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_LDAP();        
        $this->assertFalse($AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD.'foo'));
    }

    function testAuthHasNecessaryFieldsForEqUserCreation() {
        $AUTH = new Auth_LDAP();

        $AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD);

        $this->assertEqual($AUTH->username,Auth_Base::$TEST_USERNAME);
        $this->assertEqual($AUTH->fname,Auth_Base::$TEST_FNAME);
        $this->assertEqual($AUTH->lname,Auth_Base::$TEST_LNAME);
        $this->assertEqual($AUTH->sortname,Auth_Base::$TEST_SORTNAME);
        $this->assertEqual($AUTH->email,Auth_Base::$TEST_EMAIL);
        $this->assertEqual($AUTH->inst_groups,Auth_Base::$TEST_INST_GROUPS);
    }

    function testAuthLDAPfindOneUserByUsername() {
        $AUTH = new Auth_LDAP();

        // NOTE: this test uses live data! Adjust to your own institutions data
        $uData = $AUTH->findOneUserByUsername('cwarren');

        $this->assertTrue($uData);
        $this->assertEqual($uData['username'],'cwarren');
    }

    function testAuthLDAPfindAllUsersBySearchTerm() {
        $AUTH = new Auth_LDAP();

        // NOTE: this test uses live data! Adjust to your own institutions data
        $uDataList = $AUTH->findAllUsersBySearchTerm('warren');

//        $this->dump($uDataList);

        $this->assertEqual(count($uDataList),2);
    }

}

?>