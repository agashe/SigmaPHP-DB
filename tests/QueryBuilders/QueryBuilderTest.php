<?php 

use SigmaPHP\DB\TestCases\DbTestCase;
use SigmaPHP\DB\QueryBuilders\QueryBuilder;
use SigmaPHP\DB\Exceptions\InvalidArgumentException;

/**
 * QueryBuilder Test
 */
class QueryBuilderTest extends DbTestCase
{    
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
        // Please Note : we don't need to create test table
        // before each test case , so we override the setup
        // ans tearDown methods
        
        // add your database configs to phpunit.xml
        $this->dbConfigs = [
            'host' => $GLOBALS['DB_HOST'],
            'name' => $GLOBALS['DB_NAME'],
            'user' => $GLOBALS['DB_USER'],
            'pass' => $GLOBALS['DB_PASS'],
            'port' => $GLOBALS['DB_PORT']
        ];
        
        // create new query builder instance
        $this->queryBuilder = new QueryBuilder(
            $this->connectToDatabase()
        );
    }

    /**
     * QueryBuilderTest TearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        // override DbTestCase TearDown method
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
     * Test throws exception if fields is not of type array.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testThrowsExceptionIfFieldsIsNotOfTypeArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->queryBuilder
            ->table('test')
            ->select('name as n')
            ->print();
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
            "SELECT * FROM test WHERE name like '%abc';", 
            $this->queryBuilder
                ->table('test')
                ->where('name', 'like', "%abc")
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
        $queryBuilder2 = new QueryBuilder(
            $this->connectToDatabase()
        );

        $query = $queryBuilder2->table('test2')->select(['name']);

        $this->assertEquals(
            '(SELECT name FROM test1) UNION ALL (SELECT name FROM test2);', 
            $this->queryBuilder
                ->table('test1')
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
    {
        $this->createTestTable();

        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name,email,age)
            VALUES
                ('test1', 'test1@testing.com', 15), 
                ('test2', 'test2@testing.com', 25), 
                ('test3', 'test3@testing.com', 35);
        ");

        $addTestData->execute();

        $query = $this->queryBuilder
            ->table('test')
            ->select(['age as a'])
            ->get();
            
        $this->assertEquals('15', $query['a']);

        $query = $this->queryBuilder
            ->table('test')
            ->select(['sum(age) as total_age'])
            ->get();
            
        $this->assertEquals('75', $query['total_age']);

        $query = $this->queryBuilder
            ->table('test')
            ->where('name', '=', 'test1')
            ->get();
            
        $this->assertEquals('test1', $query['name']);

        $query = $this->queryBuilder
            ->table('test')
            ->whereIn('name', ['test1', 'test3'])
            ->get();
            
        $this->assertEquals('test1', $query['name']);

        $this->dropTestTable();
    }
    
    /**
     * Test get all method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGetAllMethod()
    {
        $this->createTestTable();

        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name,email,age)
            VALUES
                ('test1', 'test1@testing.com', 15), 
                ('test2', 'test2@testing.com', 25), 
                ('test3', 'test3@testing.com', 35);
        ");

        $addTestData->execute();

        $query = $this->queryBuilder
            ->table('test')
            ->where('name', 'not like', '%test1%')
            ->getAll();
            
        $this->assertEquals(2, count($query));

        $this->dropTestTable();
    }

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