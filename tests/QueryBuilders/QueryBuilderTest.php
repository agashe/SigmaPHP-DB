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
            'SELECT * FROM test WHERE id = 5;', 
            $this->queryBuilder
                ->table('test')
                ->where('id', '=', 5)
                ->print()
        );
        
        $this->assertEquals(
            'SELECT * FROM test WHERE age >= 15;', 
            $this->queryBuilder
                ->table('test')
                ->where('age', '>=', 15)
                ->print()
        );
        
        $this->assertEquals(
            'SELECT * FROM test WHERE name like %abc;', 
            $this->queryBuilder
                ->table('test')
                ->where('name', 'like', '%abc')
                ->print()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE email is not null;', 
            $this->queryBuilder
                ->table('test')
                ->where('email', 'is not', 'null')
                ->print()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE date(created_at) = date(now());', 
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
            'SELECT * FROM test WHERE id > 10  AND age < 20;', 
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
            'SELECT * FROM test WHERE id > 10  OR age < 20;', 
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
            'SELECT * FROM test WHERE age BETWEEN 5 AND 10;', 
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
            'SELECT * FROM test WHERE age IN (5,10,15);', 
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
            'SELECT avg(age) as avg_age FROM test HAVING avg(age) > 10;', 
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
    
    /**
     * Test limit statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testLimitStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test LIMIT 10;', 
            $this->queryBuilder
                ->table('test')
                ->limit(10)
                ->print()
        );
    }

    /**
     * Test offset statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testOffsetStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test LIMIT 10 OFFSET 5;', 
            $this->queryBuilder
                ->table('test')
                ->limit(10, 5)
                ->print()
        );
    }
    
    /**
     * Test order by statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testOrderByStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test ORDER BY name asc;', 
            $this->queryBuilder
                ->table('test')
                ->orderBy(['name asc'])
                ->print()
        );
    }

    /**
     * Test group by statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGroupByStatement()
    {
        $this->assertEquals(
            'SELECT * FROM test GROUP BY name,age;', 
            $this->queryBuilder
                ->table('test')
                ->groupBy(['name', 'age'])
                ->print()
        );
    }
    
    /**
     * Test union statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUnionStatement()
    {
        $query = $this->queryBuilder
            ->table('test1')
            ->select(['name'])
            ->print();

        $this->assertEquals(
            '(SELECT name FROM test1) UNION ALL (SELECT name FROM test2);', 
            $this->queryBuilder
                ->table('test2')
                ->select(['name'])
                ->union($query, true)
                ->print()
        );
    }
    
    /**
     * Test join statement.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testJoinStatement()
    {
        $validJoinQuery = 'SELECT t1.name,t2.age FROM test1 as t1';
        $validJoinQuery .= ' CROSS JOIN test2 as t2 ON t1.id = t2.id;';
                 
        $this->assertEquals(
            $validJoinQuery, 
            $this->queryBuilder
                ->table('test1 as t1')
                ->select(['t1.name', 't2.age'])
                ->join('test2 as t2', 't1.id', '=', 't2.id', 'cross')
                ->print()
        );
    }

    /**
     * Test get method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGetMethod()
    {}
    
    /**
     * Test get all method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGetAllMethod()
    {}

    /**
     * Test print query method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testPrintQueryMethod()
    {
        echo $this->queryBuilder
            ->table('test')
            ->print();

        $this->expectOutputString('SELECT * FROM test;');
    }
}