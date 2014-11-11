<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/db_linked.class.php';

class Trial_Db_Linked extends Db_Linked {
    public static $fields = array('dblinktest_id','charfield','intfield','flagfield');
    public static $primaryKeyField = 'dblinktest_id';
    public static $dbTable = 'dblinktest';
    public static $entity_type_label = 'dblinktest';
}

class Trial_Bad_Db_Linked_No_PK extends Db_Linked {
    public static $fields = array('dblinktest_id','charfield','intfield','flagfield');
    public static $primaryKeyField = '';
    public static $dbTable = 'dblinktest';
    public static $entity_type_label = 'dblinktest';
}

class Trial_Bad_Db_Linked_No_Table extends Db_Linked {
	public static $fields = array('dblinktest_id','charfield','intfield','flagfield');
	public static $primaryKeyField = 'dblinktest_id';
	public static $dbTable = '';
    public static $entity_type_label = 'dblinktest';
}

	class TestOfDB_Linked extends WMSUnitTestCaseDB {

    /////////////////////////////////////////

    function setUp() {
        $this->_dbClear();
    }


    function _dbClear() {
        $setUpSql = 'DELETE FROM dblinktest';
        $setUpStmt = $this->DB->prepare($setUpSql);
        $setUpStmt->execute();
    }

    function _dbInsertTestRecord($dataHash=false) {
        if (! $dataHash) { $dataHash = array(); }
        if (! array_key_exists('id',$dataHash)) { $dataHash['id'] = 5; }
        if (! array_key_exists('char',$dataHash)) { $dataHash['char'] = 'char data'; }
        if (! array_key_exists('int',$dataHash)) { $dataHash['int'] = 1; }
        if (! array_key_exists('flag',$dataHash)) { $dataHash['flag'] = 0; }
        $insertSql = "INSERT INTO dblinktest VALUES (".$dataHash['id'].",'".$dataHash['char']."',".$dataHash['int'].",".$dataHash['flag'].")";
        $insertStmt = $this->DB->prepare($insertSql);
        $insertStmt->execute();
    }

	# BELOW: TESTS FOR INSTANCE METHODS

    function testConnectedToDatabase() {
       $this->assertNotNull($this->DB);
    }

    function testInitializing() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertEqual($testObj->dblinktest_id,1);
        $this->assertEqual($testObj->dblinktest_id,$testObj->fieldValues['dblinktest_id']);
    }

	function testInstantiationFailsClassDefHasNoPK() {
		$this->expectError(Db_Linked::$ERR_MSG_NO_PK);
		$testObj = new Trial_Bad_Db_Linked_No_PK( ['DB'=>$this->DB ,'dblinktest_id'=>'1'] );
	}

	function testInstantiationFailsClassDefHasNoTable() {
		$this->expectError(Db_Linked::$ERR_MSG_NO_TABLE);
		$testObj = new Trial_Bad_Db_Linked_No_Table( ['DB'=>$this->DB ,'dblinktest_id'=>'1'] );
	}

	function testInstantiationFailsNoDBGiven() {
		$this->expectError(Db_Linked::$ERR_MSG_NO_DB);
		$testObj = new Trial_Db_Linked( [ 'dblinktest_id'=>'1'] );
	}

	function testInstantiationFailsBadDBGiven() {
		$this->expectError(Db_Linked::$ERR_MSG_BAD_DB);
		$testObj = new Trial_Db_Linked( ['DB'=>'' ,'dblinktest_id'=>'1'] );
	}

    function testGetSet() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertTrue(is_null($testObj->charfield));

        $testObj->charfield = 'hello world';

        $this->assertEqual($testObj->charfield,'hello world');
        $this->assertEqual($testObj->charfield,$testObj->fieldValues['charfield']);

		// update an attribute with the same value as existing value; we want matchesDb to still be true
        $testObj->matchesDb = true;
        $this->assertTrue($testObj->matchesDb);

        $testObj->charfield = 'hello world';

        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->charfield,'hello world');
    }

    function testLoadNothing() {
        $this->_dbClear();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);

        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        $testObj = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'1'],$this->DB);

        $this->assertFalse($testObj->matchesDb);
        $this->assertNull($testObj->dblinktest_id);
    }

    function testRefreshNothing() {
        $this->_dbClear();
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,1);

        $testObj->refreshFromDb();

        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,1);
    }

    function testLoadSomething() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);

        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        $testObj = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'5'],$this->DB);

        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,5);
        $this->assertEqual($testObj->charfield,'char data');
        $this->assertEqual($testObj->intfield,1);
        $this->assertEqual($testObj->flagfield,0);
    }

    function testRefreshSomething() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,'dblinktest_id'=>'5']);
        $this->assertEqual($testObj->dblinktest_id,5);
        $this->assertNull($testObj->charfield);
        $this->assertFalse($testObj->matchesDb);
        
        $testObj->refreshFromDb();
        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,5);
        $this->assertEqual($testObj->charfield,'char data');
        $this->assertEqual($testObj->intfield,1);
        $this->assertEqual($testObj->flagfield,0);
    }

    function testUpdateExistingSomething() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);
        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        $testObj = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'5'],$this->DB);
        $this->assertTrue($testObj->matchesDb);

        $testObj->charfield = 'new char data';
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->charfield,'new char data');

        $testObj->updateDb();
        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->charfield,'new char data');

        $selectSql = "SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE dblinktest_id=5";
        $selectStmt = $this->DB->prepare($selectSql);
        $selectStmt->execute();
        $selectResult = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($selectResult['charfield'],'new char data');
    }

    function testUpdateNewSomethingWithId() {
        $this->_dbClear();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
                                         'dblinktest_id'=>'7',
                                         'charfield'=>'the stringiest',
                                         'intfield'=>38,
                                         'flagfield'=>true]);
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,7);
        $this->assertEqual($testObj->charfield,'the stringiest');
        $this->assertEqual($testObj->intfield,38);
        $this->assertEqual($testObj->flagfield,true);


        $testObj->updateDb();
        $this->assertTrue($testObj->matchesDb);

        $selectSql = "SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE dblinktest_id=7";
        $selectStmt = $this->DB->prepare($selectSql);
        $selectStmt->execute();
        $selectResult = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($selectResult['dblinktest_id'],7);
        $this->assertEqual($selectResult['charfield'],'the stringiest');
        $this->assertEqual($selectResult['intfield'],38);
        $this->assertEqual($selectResult['flagfield'],true);
    }

    function testUpdateNewSomethingWithoutId() {
        $this->_dbClear();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
                                         'charfield'=>'even stringier',
                                         'intfield'=>42,
                                         'flagfield'=>false]);
        $this->assertFalse($testObj->matchesDb);
        $this->assertNull($testObj->dblinktest_id);
        $this->assertEqual($testObj->charfield,'even stringier');
        $this->assertEqual($testObj->intfield,42);
        $this->assertEqual($testObj->flagfield,false);

        $testObj->updateDb();
        $this->assertTrue($testObj->matchesDb);
        $this->assertNotNull($testObj->dblinktest_id);

        $selectSql = "SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE intfield=42";
        $selectStmt = $this->DB->prepare($selectSql);
        $selectStmt->execute();
        $selectResult = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($selectResult['dblinktest_id'],$testObj->dblinktest_id);
        $this->assertEqual($selectResult['charfield'],'even stringier');
        $this->assertEqual($selectResult['intfield'],42);
        $this->assertEqual($selectResult['flagfield'],false);
    }


    function testBuildFetchSqlWithSpecialKeys() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
            'charfield'=>'even stringier',
            'intfield'=>42,
            'flagfield'=>false]);

        $searchHash = ['dblinktest_id'=>$testObj->dblinktest_id];
        $fetchSql = $testObj->buildFetchSql($searchHash);
//        $this->dump($fetchSql);
        $this->assertEqual($fetchSql,'SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE 1=1 AND dblinktest_id = :dblinktest_id');
        $this->assertEqual(':dblinktest_id',array_keys($searchHash)[0]);

        $searchHash = ['intfield >'=>42];
        $fetchSql = $testObj->buildFetchSql($searchHash);
//        $this->dump($fetchSql);
        $this->assertEqual($fetchSql,'SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE 1=1 AND intfield > :intfield');
        $this->assertEqual(':intfield',array_keys($searchHash)[0]);

        $searchHash = ['flagfield IS NOT NULL'=>1];
        $fetchSql = $testObj->buildFetchSql($searchHash);
//        $this->dump($fetchSql);
        $this->assertEqual($fetchSql,'SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE 1=1 AND flagfield IS NOT NULL');
        $this->assertEqual(count(array_keys($searchHash)),0);
    }


        function testBuildFetchSqlWithPepeatedKeys() {
            $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
                'charfield'=>'even stringier',
                'intfield'=>42,
                'flagfield'=>false]);

            $searchHash = ['intfield >'=>40,'intfield <'=>45];
            $fetchSql = $testObj->buildFetchSql($searchHash);
//            $this->dump($fetchSql);
//            $this->dump($searchHash);
            $this->assertPattern('/intfield </',$fetchSql);
            $this->assertPattern('/intfield >/',$fetchSql);
            $this->assertPattern('/:intfield/',$fetchSql);
            $this->assertPattern('/:intfield__2/',$fetchSql);
        }

    function testID() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'5'],$this->DB);
        $this->assertEqual(5,$testObj->ID());
    }

    function testSetFromArray() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'5'],$this->DB);

        $new_vals_ar = [
            'dblinktest-charfield_5' => 'new char data',
            'dblinktest-intfield_5' => '22',
            'dblinktest-flagfield_5' => '1'
        ];

        $testObj->setFromArray($new_vals_ar);
        $testObj->updateDb();

        $newTestObj = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'5'],$this->DB);

        $this->assertEqual('new char data',$newTestObj->charfield);
        $this->assertEqual('22',$newTestObj->intfield);
        $this->assertEqual('1',$newTestObj->flagfield);
    }

        # BELOW: TESTS FOR STATIC METHODS

    function testCheckStatementError() {
        $badSql = "INSERT INTO dblinktest VALUES ('a')";
        $badStmt = $this->DB->prepare($badSql);
        $badStmt->execute();

        $this->expectError(Db_Linked::$ERR_MSG_SQL_STMT_ERROR);
        Trial_Db_Linked::checkStmtError($badStmt);
    }

    function testLoadExistingOneFromDb() {
        $this->_dbClear();
        $this->_dbInsertTestRecord(['id'=>1]);

        $matchingObject = Trial_Db_Linked::getOneFromDb(['intfield'=>1],$this->DB);

        $this->assertNotNull($matchingObject);
        $this->assertTrue($matchingObject->matchesDb);
        $this->assertEqual($matchingObject->charfield,'char data');
        $this->assertEqual($matchingObject->intfield,1);
        $this->assertEqual($matchingObject->flagfield,false);
	}

    function testLoadExistingUsingKeyWithComparator() {
        $this->_dbClear();
        $this->_dbInsertTestRecord(['id'=>1]);

        $matchingObject = Trial_Db_Linked::getOneFromDb(['intfield <'=>2],$this->DB);

        $this->assertNotNull($matchingObject);
        $this->assertTrue($matchingObject->matchesDb);
        $this->assertEqual($matchingObject->charfield,'char data');
        $this->assertEqual($matchingObject->intfield,1);
        $this->assertEqual($matchingObject->flagfield,false);

        $matchingObject = Trial_Db_Linked::getOneFromDb(['charfield like'=>'%data'],$this->DB);

        $this->assertNotNull($matchingObject);
        $this->assertTrue($matchingObject->matchesDb);
        $this->assertEqual($matchingObject->charfield,'char data');
        $this->assertEqual($matchingObject->intfield,1);
        $this->assertEqual($matchingObject->flagfield,false);

        $matchingObject = Trial_Db_Linked::getOneFromDb(['charfield not like'=>'%data'],$this->DB);

        $this->assertNotNull($matchingObject);
        $this->assertFalse($matchingObject->matchesDb);
    }

    function testLoadNonexistingOneFromDb() {
        $this->_dbClear();

        $matchingObject = Trial_Db_Linked::getOneFromDb(['dblinktest_id'=>28],$this->DB);

        $this->assertFalse($matchingObject->matchesDb);
    }

    function testLoadMultipleFromDbUsingScalarsInSearchHash() {
        $this->_dbClear();
        $this->_dbInsertTestRecord(['id'=>1]);
        $this->_dbInsertTestRecord(['id'=>2]);
        $this->_dbInsertTestRecord(['id'=>3]);
        $this->_dbInsertTestRecord(['id'=>5,'int'=>2]);

        $matchingObjects = Trial_Db_Linked::getAllFromDb(['intfield'=>1],$this->DB);

        $this->assertEqual(count($matchingObjects),3);
        $this->assertPattern('/[123]/',$matchingObjects[0]->dblinktest_id);
        $this->assertPattern('/[123]/',$matchingObjects[1]->dblinktest_id);
        $this->assertPattern('/[123]/',$matchingObjects[2]->dblinktest_id);
        $this->assertNotEqual($matchingObjects[0]->dblinktest_id,$matchingObjects[1]->dblinktest_id);
        $this->assertNotEqual($matchingObjects[0]->dblinktest_id,$matchingObjects[2]->dblinktest_id);
        $this->assertNotEqual($matchingObjects[1]->dblinktest_id,$matchingObjects[2]->dblinktest_id);
        $this->assertTrue($matchingObjects[0]->matchesDb);
        $this->assertTrue($matchingObjects[1]->matchesDb);
        $this->assertTrue($matchingObjects[2]->matchesDb);

        $noMatchingObjects = Trial_Db_Linked::getAllFromDb(['intfield'=>7],$this->DB);
        $this->assertEqual(count($noMatchingObjects),0);
    }

    function testLoadMultipleFromDbUsingArrayInSearchHash() {
        $this->_dbClear();
        $this->_dbInsertTestRecord(['id'=>1]);
        $this->_dbInsertTestRecord(['id'=>2]);
        $this->_dbInsertTestRecord(['id'=>3]);

        $matchingObjects = Trial_Db_Linked::getAllFromDb(['dblinktest_id'=>[1,2]],$this->DB);

        $this->assertEqual(count($matchingObjects),2);
        $this->assertPattern('/[12]/',$matchingObjects[0]->dblinktest_id);
        $this->assertPattern('/[12]/',$matchingObjects[1]->dblinktest_id);
    }

    function testArrayToPkHash() {
        $objAr = [
            new Trial_Db_Linked( ['dblinktest_id'=>'3','DB'=>$this->DB]),
            new Trial_Db_Linked( ['dblinktest_id'=>'8','DB'=>$this->DB]),
            new Trial_Db_Linked( ['dblinktest_id'=>'11','DB'=>$this->DB]),
            new Trial_Db_Linked( ['dblinktest_id'=>'5','DB'=>$this->DB])
        ];

        $this->assertEqual(count($objAr),4);
        $this->assertEqual(array_keys($objAr),[0,1,2,3]);

        $pkHash = Trial_Db_Linked::arrayToPkHash($objAr);

        $this->assertEqual(count($pkHash),4);
        $newKeys = array_keys($pkHash);
        sort($newKeys);
        $this->assertEqual($newKeys,[3,5,8,11]);

        $this->assertEqual($pkHash[3]->dblinktest_id,3);
        $this->assertEqual($pkHash[5]->dblinktest_id,5);
        $this->assertEqual($pkHash[8]->dblinktest_id,8);
        $this->assertEqual($pkHash[11]->dblinktest_id,11);
    }

        function testArrayOfAttrValues() {
            $objAr = [
                new Trial_Db_Linked( ['dblinktest_id'=>'3','DB'=>$this->DB]),
                new Trial_Db_Linked( ['dblinktest_id'=>'8','DB'=>$this->DB]),
                new Trial_Db_Linked( ['dblinktest_id'=>'11','DB'=>$this->DB]),
                new Trial_Db_Linked( ['dblinktest_id'=>'5','DB'=>$this->DB])
            ];

            $this->assertEqual(count($objAr),4);
            $this->assertEqual(array_keys($objAr),[0,1,2,3]);

            $valArr = Trial_Db_Linked::arrayOfAttrValues($objAr,'dblinktest_id');

            $this->assertEqual(4,count($valArr));

            $this->assertEqual($valArr[0],3);
            $this->assertEqual($valArr[1],8);
            $this->assertEqual($valArr[2],11);
            $this->assertEqual($valArr[3],5);
        }

    function testSanitizeFieldName() {
        $f = 'order';

        $this->assertEqual('`order`',Db_Linked::sanitizeFieldName($f));
    }

    function testFieldsAsDataAttribs() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
            'dblinktest_id'=>43,
            'charfield'=>'even stringier',
            'intfield'=>42,
            'flagfield'=>false]);

        $attrib_string = $testObj->fieldsAsDataAttribs();
        $this->assertEqual('data-dblinktest_id="43" data-charfield="even stringier" data-intfield="42" data-flagfield="0"',$attrib_string);

        $testObj->flagfield = true;
        $attrib_string = $testObj->fieldsAsDataAttribs();
        $this->assertEqual('data-dblinktest_id="43" data-charfield="even stringier" data-intfield="42" data-flagfield="1"',$attrib_string);

        $testObj->flagfield = false;
        $testObj->charfield = 'this is a "quotes" test';
        $attrib_string = $testObj->fieldsAsDataAttribs();
        $this->assertEqual('data-dblinktest_id="43" data-charfield="this is a &quot;quotes&quot; test" data-intfield="42" data-flagfield="0"',$attrib_string);

        $testObj->charfield = 'this is a string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test string-too-long test';
        $attrib_string = $testObj->fieldsAsDataAttribs();
        $this->assertEqual('data-dblinktest_id="43" data-charfield="&lt;DATA TOO LONG&gt;" data-intfield="42" data-flagfield="0"',$attrib_string);

    }
}


?>