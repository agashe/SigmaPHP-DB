<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Connectors\Connector;
use SigmaPHP\DB\Traits\DbMethods;

/**
 * DbMethods Test
 */
class DbMethodsTest extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;

    /**
     * @var Connector $connector
     */
    private $connector;

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
        // add your database configs to phpunit.xml
        $this->dbConfigs = [
            'host' => $GLOBALS['DB_HOST'],
            'name' => $GLOBALS['DB_NAME'],
            'user' => $GLOBALS['DB_USER'],
            'pass' => $GLOBALS['DB_PASS'],
            'port' => $GLOBALS['DB_PORT']
        ];

        // create test table
        $this->createTestTable();

        // create new connector instance
        $this->connector = new Connector();
        $this->testTrait = $this->createTestObject();
    }
    
    /**
     * SeederTest TearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->dropTestTable();
    }
    
    /**
     * Connect to database.
     * 
     * @return \PDO
     */
    private function connectToDatabase()
    {
        return new \PDO(
            "mysql:host={$this->dbConfigs['host']};
            dbname={$this->dbConfigs['name']}",
            $this->dbConfigs['user'],
            $this->dbConfigs['pass']
        );
    }

    /**
     * Create test table.
     *
     * @param string $name
     * @return void
     */
    private function createTestTable($name = 'test')
    {
        $testTable = $this->connectToDatabase()->prepare("
            CREATE TABLE IF NOT EXISTS {$name} (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(25) NOT NULL,
                email VARCHAR(50) NOT NULL
            );
        ");

        $testTable->execute();
    }

    /**
     * Drop test table.
     *
     * @param string $name
     * @return void
     */
    private function dropTestTable($name = 'test')
    {
        $testTable = $this->connectToDatabase()->prepare("
            DROP TABLE IF EXISTS {$name};
        ");

        $testTable->execute();
    }
    
    /**
     * Create new instance from test class.
     *
     * @return object
     */
    private function createTestObject()
    {
        return new class($this->connector->connect($this->dbConfigs)) {
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

        $this->assertEquals(3, count($result));
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
        ", 0);

        $this->assertEquals(3, count($result));
    }
}