<?php 

use SigmaPHP\DB\TestCases\DbTestCase;
use SigmaPHP\DB\Traits\DbOperations;

/**
 * DbOperations Test
 */
class DbOperationsTest extends DbTestCase
{
    /**
     * @var object $testTrait
     */
    private $testTrait;
    
    /**
     * DbOperationsTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // create new instance of anonymous class that
        // implements DbOperations Trait
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
            use DbOperations;

            private $dbConnection;
            
            public function __construct($dbConnection)
            {
                $this->dbConnection = $dbConnection;
            }
        };
    }

    /**
     * Test insert data into table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testInsertDataIntoTable()
    {
        $this->testTrait->insert(
            'test',
            [
                ['name' => 'user1', 'email' => 'email1'],
                ['name' => 'user2', 'email' => 'email2'],
                ['name' => 'user3', 'email' => 'email3'],
            ]
        );

        $dataWasInserted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasInserted->execute();
        $this->assertEquals(3, count($dataWasInserted->fetchAll()));
    }

    /**
     * Test get the newly inserted row's primary key value.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGetTheNewlyInsertedRowsPrimaryKeyValue()
    {
        $this->testTrait->insert(
            'test',
            [
                ['name' => 'user1', 'email' => 'email1'],
                ['name' => 'user2', 'email' => 'email2'],
                ['name' => 'user3', 'email' => 'email3'],
            ]
        );

        // insert method uses batch insert , so it returns only 
        // the PK value for the first inserted row only :(
        $this->assertEquals(
            1, 
            $this->testTrait->getLatestInsertedRowPrimaryKeyValue()  
        );
    }

    /**
     * Test update data in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateDataInTable()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('user1', 'email1');
        ");

        $addTestData->execute();

        $this->testTrait->update(
            'test',
            ['email' => 'email1_updated'],
            ['name' => 'user1']
        );

        $dataWasUpdated = $this->connectToDatabase()->prepare("
            SELECT * FROM test WHERE name = 'user1';
        ");

        $dataWasUpdated->execute();

        $this->assertEquals('email1_updated', $dataWasUpdated
            ->fetch()['email']);
    }
    
    /**
     * Test update all data in table if no condition was set.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateAllDataInTableIfNoConditionWasSet()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('user1', 'email1'),
                ('user2', 'email2'),
                ('user3', 'email3');
        ");

        $addTestData->execute();

        $this->testTrait->update(
            'test',
            ['email' => 'email_with_no_number']
        );

        $dataWasUpdated = $this->connectToDatabase()->prepare("
            SELECT * FROM test WHERE email = 'email_with_no_number';
        ");

        $dataWasUpdated->execute();
        $this->assertEquals(3, count($dataWasUpdated->fetchAll()));
    }

    /**
     * Test delete data from table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDeleteDataFromTable()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('user1', 'email1');
        ");

        $addTestData->execute();

        $this->testTrait->delete(
            'test',
            ['name' => 'user1']
        );

        $dataWasDeleted = $this->connectToDatabase()->prepare("
            SELECT * FROM test WHERE name = 'user1';
        ");

        $dataWasDeleted->execute();
        $this->assertFalse($dataWasDeleted->fetch());
    }

    /**
     * Test delete all data from table if no condition was set.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDeleteAllDataFromTableIfNoConditionWasSet()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('user1', 'email1'),
                ('user2', 'email2'),
                ('user3', 'email3');
        ");

        $addTestData->execute();

        $this->testTrait->delete('test');

        $dataWasDeleted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasDeleted->execute();
        $this->assertEmpty($dataWasDeleted->fetchAll());
    }
}