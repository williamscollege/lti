<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester.php';
require_once dirname(__FILE__) . '/../../util.php';


class TestOfUtil extends UnitTestCase {

	function setUp() {
	}
	
	function tearDown() {
	}


    function testGenRandomIdString() {
        
        $randomId = util_genRandomIdString(24);

        $this->assertEqual(24,strlen($randomId));
    }   

    function testWipeSession() {
        //session_start();
        $_SESSION['isAuthenticated'] = 'foo';
        $_SESSION['fingerprint'] = 'bar';
        $_SESSION['userdata'] = array('baz');

		$this->expectError("Cannot modify header information - headers already sent");

        util_wipeSession();

        $this->assertFalse(isset($_SESSION['isAuthenticated']));
        $this->assertFalse(isset($_SESSION['fingerprint']));
        $this->assertFalse(isset($_SESSION['userdata']));
    }   

    function testCheckAuthentication() {
        $this->assertFalse(util_checkAuthentication());

        $_SESSION['isAuthenticated'] = false;

        $this->assertFalse(util_checkAuthentication());

        $_SESSION['isAuthenticated'] = true;

        $this->assertTrue(util_checkAuthentication());
    }   

    function testCreateDbConnection() {
        
        $dbConn = util_createDbConnection();

        $this->assertEqual(get_class($dbConn),'PDO');
    }

    function testProcessTimeString() {
        $s = util_processTimeString('2013-03-09 15:05:00');

        $this->assertEqual($s['YYYY'],'2013');
        $this->assertEqual($s['MM'],'03');
        $this->assertEqual($s['DD'],'09');
        $this->assertEqual($s['hh'],'15');
        $this->assertEqual($s['mi'],'05');
        $this->assertEqual($s['ss'],'00');

        $this->assertEqual($s['Y'],'2013');
        $this->assertEqual($s['M'],'3');
        $this->assertEqual($s['D'],'9');
        $this->assertEqual($s['h'],'15');
        $this->assertEqual($s['hhap'],'03');
        $this->assertEqual($s['hap'],'3');
        $this->assertEqual($s['ap'],'PM');
        $this->assertEqual($s['m'],'5');
        $this->assertEqual($s['s'],'0');

        $this->assertEqual($s['date'],'2013/3/9');

        $s = util_processTimeString('2013-03-09 09:00:00');
        $this->assertEqual($s['hhap'],'09');
        $this->assertEqual($s['hap'],'9');
        $this->assertEqual($s['ap'],'AM');
        $this->assertEqual($s['mi'],'00');
        $this->assertEqual($s['m'],'0');
        $this->assertEqual($s['s'],'0');

        $s = util_processTimeString('2013-03-09 00:30:00');
        $this->assertEqual($s['ap'],'AM');
        $this->assertEqual($s['hh'],'00');
        $this->assertEqual($s['h'],'0');
        $this->assertEqual($s['hhap'],'12');
        $this->assertEqual($s['hap'],'12');
    }

    function testTimeRangeString() {
        $this->assertEqual(util_timeRangeString('2013-03-09 15:00:00','2013-03-09 15:30:00'),'2013/3/9 3:00-3:30 PM');
        $this->assertEqual(util_timeRangeString('2013-03-09 09:00:00','2013-03-09 15:30:00'),'2013/3/9 9:00 AM-3:30 PM');
        $this->assertEqual(util_timeRangeString('2013-03-09 15:00:00','2013-03-10 15:00:00'),'2013/3/9 3:00 PM-2013/3/10 3:00 PM');
    }

    function testLang() {
        // 1. set up LANGUAGE and CURRENT_LANGUAGE_SET
        // 2. run the assert
        global $LANGUAGE, $CUR_LANG_SET;
        $CUR_LANG_SET = 'test';
        $LANGUAGE[$CUR_LANG_SET]['foo'] = 'bar';
        $LANGUAGE[$CUR_LANG_SET]['baz_maz'] = 'baz maz';

        $this->assertEqual(util_lang('foo'),'bar');
        $this->assertEqual(util_lang('baz_maz'),'baz maz');
        $this->assertEqual(util_lang('baz_maz','ucfirst'),'Baz maz');
        $this->assertEqual(util_lang('baz_maz','properize'),'Baz Maz');
    }

    function testStartListItem() {
        $this->assertEqual('<li>',util_listItemTag());
        $this->assertEqual('<li id="idfoo">',util_listItemTag('idfoo'));
        $this->assertEqual('<li class="class1foo class2foo">',util_listItemTag('',['class1foo','class2foo']));
        $this->assertEqual('<li id="idfoo" class="class1foo class2foo">',util_listItemTag('idfoo',['class1foo','class2foo']));
        $this->assertEqual('<li foostatus="statusfoo" typebar="bartype">',util_listItemTag('',[],['foostatus'=>'statusfoo','typebar'=>'bartype']));
        $this->assertEqual('<li id="idfoo" class="class1foo class2foo" foostatus="statusfoo" typebar="bartype">',util_listItemTag('idfoo',['class1foo','class2foo'],['foostatus'=>'statusfoo','typebar'=>'bartype']));
        $this->assertEqual('<li id="idfoo" class="class1foo class2foo" foostatus="statusfoo" typebar="bartype">',util_listItemTag('idfoo',['class1foo','class2foo'],['typebar'=>'bartype','foostatus'=>'statusfoo']));
    }

    function testSanitizeFileName() {
        $this->assertEqual('filename',util_sanitizeFileName('filename'));
        $this->assertEqual('filename_',util_sanitizeFileName('filename;'));
        $this->assertEqual('file_name',util_sanitizeFileName('file name'));
        $this->assertEqual('_filename',util_sanitizeFileName('../filename'));
        $this->assertEqual('filename',util_sanitizeFileName('....filename'));
        $this->assertEqual('.filename',util_sanitizeFileName('.....filename'));
    }

    function testSanitizeFileReference() {
        $this->assertEqual('filename',util_sanitizeFileReference('filename'));
        $this->assertEqual('file_name',util_sanitizeFileReference('file name'));
        $this->assertEqual('filename',util_sanitizeFileReference('../filename'));
        $this->assertEqual('foo/filename',util_sanitizeFileReference('../foo/filename'));
        $this->assertEqual('/foo/filename',util_sanitizeFileReference('/../foo/filename'));
        $this->assertEqual('__filename',util_sanitizeFileReference('; filename'));
    }

    function testCoordsMapLink() {
        $this->assertEqual('http://maps.google.com/maps?q=42.7118454,-73.2054918+(point)&z=19&ll=42.7118454,-73.2054918',util_coordsMapLink(-73.2054918, 42.7118454));
        $this->assertEqual('http://maps.google.com/maps?q=42.7118454,-73.2054918+(point)&z=12&ll=42.7118454,-73.2054918',util_coordsMapLink(-73.2054918, 42.7118454,12));
    }

    function testOrderingControls() {
        $this->todo();
    }
}
