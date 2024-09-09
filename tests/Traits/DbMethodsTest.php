<?php 

use SigmaPHP\DB\Tests\TestCases\DbTestCase;
use SigmaPHP\DB\Traits\DbMethods;

/**
 * DbMethods Test
 */
class DbMethodsTest extends DbTestCase
{
    /**
     * @var object $testTrait
     */
    private $testTrait;
    
    /**
     * DbMethodsTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // create new instance of anonymous class that
        // implements DbMethods Trait
        $this->testTrait = $this->createTestObject();
    }
    
    /**
     * Create new instance from test class.
     *
     * @return object
     */
    private function createTestObject()
    {
        return new class($this->connectToDatabase()) {
            use DbMethods;

            private $dbConnection;
            
            public function __construct($dbConnection)
            {
                $this->dbConnection = $dbConnection;
            }
        };
    }

    /**
     * Test execute SQL statements.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testExecuteSqlStatements()
    {
        $this->testTrait->execute("
            INSERT INTO test (name, email) VALUES ('hello', 'world');
        ");

        $dataWasInserted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasInserted->execute();
        $this->assertNotEmpty($dataWasInserted->fetchAll());
    }

    /**
     * Test fetch single row.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testFetchSingleRow()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('test1', 'test1@test.local'), 
                ('test2', 'test2@test.local'), 
                ('test3', 'test3@test.local'); 
        ");

        $addTestData->execute();

        $result = $this->testTrait->fetch("
            SELECT * FROM test;
        ");

        $this->assertEquals(4, count($result));
    }

    /**
     * Test fetch All the resulted data.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testFetchAllTheResultedData()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('test1', 'test1@test.local'), 
                ('test2', 'test2@test.local'), 
                ('test3', 'test3@test.local'); 
        ");

        $addTestData->execute();

        $result = $this->testTrait->fetchAll("
            SELECT * FROM test;
        ");

        $this->assertEquals(3, count($result));
    }
    
    /**
     * Test fetch single column from from the resulted data.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testFetchSingleColumnFromFromTheResultedData()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('test1', 'test1@test.local'), 
                ('test2', 'test2@test.local'), 
                ('test3', 'test3@test.local'); 
        ");

        $addTestData->execute();

        $result = $this->testTrait->fetchColumn("
            SELECT * FROM test;
        ");

        $this->assertEquals(3, count($result));
    }

    /*
     * Test table exists.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testTableExists()
    {
        $this->assertTrue($this->testTrait->tableExists(
            $this->dbConfigs['name'], 'test'
        ));
    }
    
    /*
     * Test get all tables names in database.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGetAllTablesNamesInDatabase()
    {
        $this->assertEquals(1, count(
            $this->testTrait->getAllTables($this->dbConfigs['name'])
        ));
    }
}