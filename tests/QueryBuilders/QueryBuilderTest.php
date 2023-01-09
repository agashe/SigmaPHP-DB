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
     * Test table with alias.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testTableWithAlias()
    {
        $this->assertEquals(
            'SELECT * FROM test as t;', 
            $this->queryBuilder->table('test as t')->print()
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
    
    /**
     * Test select fields with aliases.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSelectFieldsWithAliases()
    {
        $this->assertEquals(
            'SELECT name as n,age as a FROM test;', 
            $this->queryBuilder
                ->table('test')
                ->select([
                    'name as n',
                    'age as a'
                ])
                ->print()
        );
    }
    
    /**
     * Test select method with aggregates.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSelectMethodWithAggregates()
    {
        $this->assertEquals(
            'SELECT count(*) as total,avg(age) as avg_age FROM test;', 
            $this->queryBuilder
                ->table('test')
                ->select([
                    'count(*) as total',
                    'avg(age) as avg_age'
                ])
                ->print()
        );
    }

    /**
     * Test where conditions.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testWhereConditions()
    {
        $this->assertEquals(
            'SELECT * FROM test WHERE id = 5 ;', 
            $this->queryBuilder
                ->table('test')
                ->where('id', '=', 5)
                ->print()
        );
        
        $this->assertEquals(
            'SELECT * FROM test WHERE age >= 15 ;', 
            $this->queryBuilder
                ->table('test')
                ->where('age', '>=', 15)
                ->print()
        );
        
        $this->assertEquals(
            'SELECT * FROM test WHERE name like %abc ;', 
            $this->queryBuilder
                ->table('test')
                ->where('name', 'like', '%abc')
                ->print()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE email is not null ;', 
            $this->queryBuilder
                ->table('test')
                ->where('email', 'is not', 'null')
                ->print()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE date(created_at) = date(now()) ;', 
            $this->queryBuilder
                ->table('test')
                ->where('date(created_at)', '=', 'date(now())')
                ->print()
        );
    }

    /**
     * Test and where statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testAndWhereStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test WHERE id > 10  AND age < 20 ;', 
            $this->queryBuilder
                ->table('test')
                ->where('id', '>', 10)
                ->andWhere('age', '<', 20)
                ->print()
        );
    }
    
    /**
     * Test or where statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testOrWhereStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test WHERE id > 10  OR age < 20 ;', 
            $this->queryBuilder
                ->table('test')
                ->where('id', '>', 10)
                ->orWhere('age', '<', 20)
                ->print()
        );
    }
    
    /**
     * Test where between statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testWhereBetweenStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test WHERE age BETWEEN 5 AND 10 ;', 
            $this->queryBuilder
                ->table('test')
                ->whereBetween('age', 5, 10)
                ->print()
        );
    }
    
    /**
     * Test where in statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testWhereInStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test WHERE age IN (5,10,15) ;', 
            $this->queryBuilder
                ->table('test')
                ->wherein('age', [5, 10, 15])
                ->print()
        );
    }
    
    /**
     * Test having statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testHavingStatement()
    {
        $this->assertEquals(
            'SELECT avg(age) as avg_age FROM test HAVING avg(age) > 10 ;', 
            $this->queryBuilder
                ->table('test')
                ->select(['avg(age) as avg_age'])
                ->having('avg(age)', '>', 10)
                ->print()
        );
    }

    /**
     * Test distinct statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDistinctStatement()
    {
        $this->assertEquals(
            'SELECT DISTINCT name FROM test;', 
            $this->queryBuilder
                ->table('test')
                ->select(['name'])
                ->distinct()
                ->print()
        );
    }
}