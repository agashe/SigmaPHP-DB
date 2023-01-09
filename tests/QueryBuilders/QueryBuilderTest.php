<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\QueryBuilders\QueryBuilder;
use SigmaPHP\DB\Connectors\Connector;

/**
 * QueryBuilder Test
 */
class QueryBuilderTest extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;
    
    /**
     * @var QueryBuilder $queryBuilder
     */
    private $queryBuilder;

    /**
     * QueryBuilderTest SetUp
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
        
        // create new QueryBuilder instance
        $connector = new Connector();
        $this->queryBuilder = new QueryBuilder(
            $connector->connect($this->dbConfigs)
        );
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
                email VARCHAR(50) NOT NULL,
                age INT UNSIGNED
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
     * Test table method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testTableMethod()
    {
        $this->assertEquals(
            'SELECT * FROM test;', 
            $this->queryBuilder->table('test')->print()
        );
    }
    
    /**
     * Test select method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSelectMethod()
    {
        $this->assertEquals(
            'SELECT name,age FROM test;', 
            $this->queryBuilder
                ->table('test')
                ->select(['name', 'age'])
                ->print()
        );
    }
}