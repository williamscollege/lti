<?php
/**
 *  base include file for WebTest
 *  @package    SimpleTest
 *  @subpackage WebUnitTester
 *  @version    $Id: unit_tester.php 1882 2009-07-01 14:30:05Z lastcraft $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once dirname(__FILE__) . '/web_tester.php';
/**#@-*/

require_once dirname(__FILE__).'/../../institution.cfg.php';
require_once dirname(__FILE__).'/../../util.php';
require_once dirname(__FILE__) . '/../dataForTesting.php';

/**
 *    Standard unit test class for day to day testing
 *    of PHP code XP style. Adds some useful standard
 *    assertions.
 *    WMS extension adds some handle assertions
 *    @package  SimpleTest
 *    @subpackage   WebTester
 */
abstract class WMSWebTestCase extends WebTestCase {

    public $DB;

    function __construct($label = false) {
        parent::__construct($label);
		$this->DB = new PDO("mysql:host=".TESTING_DB_SERVER.";dbname=".TESTING_DB_NAME.";port=3306",TESTING_DB_USER,TESTING_DB_PASS);
        removeAllTestData($this->DB);
	}

    function assertEltByIdHasAttrOfValue($eltId,$attrName,$attrValueExpected = true) {
        $matches = array();
        $haystack = $this->getBrowser()->getContent();

//        preg_match('/(\<[^\>]\s+id\s*=\s*"'.$eltId.'"\s+[^\>]*\>)/',$this->getBrowser()->getContent(),$matches);
        preg_match('/(\<[^\>]*\s+id\s*=\s*"'.$eltId.'"\s*[^\>]*\>)/',$haystack,$matches);

//        echo $matches[1];
        
        if (! $this->assertTrue(isset($matches[1]),"Element with id [$eltId] should exist")) {
            return false;
        }

        $haystack = $matches[1];
        $matches = array();
        preg_match('/\s+('.$attrName.')\s*=\s*"([^"]*)"/',$haystack,$matches);
        if (! $this->assertTrue(isset($matches[1]) && isset($matches[2]),"Element with id [$eltId] should have attribute of [$attrName]")) {
            return false;
        }

        if ($attrValueExpected === true) {
            return true;
        }

        if (! SimpleExpectation::isExpectation($attrValueExpected)) {
            $attrValueExpected = new IdenticalExpectation($attrValueExpected);
        }
        $haystack = $matches[2];
        if ($attrValueExpected->test($haystack)) {
            return true;
        }

        return $this->assert($attrValueExpected, $haystack, "Element with id [$eltId] attribute [$attrName] value does not match- ".$attrValueExpected->testMessage($haystack));
    }

    function assertEltByIdDoesNotHaveAttr($eltId,$attrName) {
        $matches = array();
        $haystack = $this->getBrowser()->getContent();

//        preg_match('/(\<[^\>]\s+id\s*=\s*"'.$eltId.'"\s+[^\>]*\>)/',$this->getBrowser()->getContent(),$matches);
        preg_match('/(\<[^\>]*\s+id\s*=\s*"'.$eltId.'"\s+[^\>]*\>)/',$haystack,$matches);

        //echo $matches[1];

        if (! $this->assertTrue(isset($matches[1]),"Element with id [$eltId] should exist")) {
            return false;
        }

        $haystack = $matches[1];
        $matches = array();
        preg_match('/\s+('.$attrName.')\s*=\s*"([^"]*)"/',$haystack,$matches);
        if (! $this->assertFalse(isset($matches[1]) && isset($matches[2]),"Element with id [$eltId] should NOT have attribute of [$attrName]")) {
            return false;
        }
        return true;
    }

    /**
     *    Tests for the non-presence of a form field with the given id. Match is
     *    case insensitive with normalised space.
     *    @param string/integer $id       ID attribute.
     *    @param string $message          Message to display. Default
     *                                    can be embedded with %s.
     *    @return boolean                 True if link missing.
     *    @access public
     */
    function assertNoFieldById($id, $message = '%s') {
//        echo "getFieldById($id) result is |".$this->getBrowser()->getFieldById($id)."|\n";
        return $this->assertTrue(
            is_null($this->getBrowser()->getFieldById($id)),
            sprintf($message, "Field [$id] should not exist"));
    }

    function showContent() {
        echo "<pre>\n";
        echo htmlentities($this->getBrowser()->getContent());
        echo "\n</pre>";
    }
}
?>

