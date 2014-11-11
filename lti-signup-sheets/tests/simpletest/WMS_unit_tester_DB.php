<?php
/**
 *  base include file for SimpleTest, extended by CSW to have a DB connection
 *  @package    SimpleTest
 *  @subpackage UnitTesterDB
 *  @version    $Id: unit_tester.php 1882 2009-07-01 14:30:05Z lastcraft $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once dirname(__FILE__) . '/unit_tester.php';
/**#@-*/

require_once dirname(__FILE__).'/../../institution.cfg.php';
require_once dirname(__FILE__).'/../../util.php';
require_once dirname(__FILE__) . '/../dataForTesting.php';

/**
 *    Standard unit test class for day to day testing
 *    of PHP code XP style. Adds some useful standard
 *    assertions.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
abstract class WMSUnitTestCaseDB extends UnitTestCase {
    //public $DB = 'foo';
    public $DB;

    function __construct($label = false) {
        parent::__construct($label);

        $this->DB = new PDO("mysql:host=".TESTING_DB_SERVER.";dbname=".TESTING_DB_NAME.";port=3306",TESTING_DB_USER,TESTING_DB_PASS);    
        removeAllTestData($this->DB);
    }
}
?>