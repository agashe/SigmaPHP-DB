<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Seeders\Seeder;
use SigmaPHP\DB\Connectors\Connector;

/**
 * Seeder Test
 */
class SeederTest extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;
    
    /**
     * @var Seeder $seeder
     */
    private $seeder;

    /**
     * SeederTest SetUp
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
        
        // create new seeder instance
        $connector = new Connector();
        $this->seeder = new Seeder(
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
     * Test execute SQL statements.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testExecuteSqlStatements()
    {
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES ('hello', 'world');
        ");

        $dataWasInserted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasInserted->execute();
        $this->assertNotEmpty($dataWasInserted->fetchAll());
    }
    
    /**
     * Test insert data into table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testInsertDataIntoTable()
    {
        $this->seeder->insert(
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
     * Test update data in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateDataInTable()
    {
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES ('user1', 'email1');
        ");

        $this->seeder->update(
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
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES
            ('user1', 'email1'),
            ('user2', 'email2'),
            ('user3', 'email3');
        ");

        $this->seeder->update(
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
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES ('user1', 'email1');
        ");

        $this->seeder->delete(
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
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES
            ('user1', 'email1'),
            ('user2', 'email2'),
            ('user3', 'email3');
        ");

        $this->seeder->delete('test');

        $dataWasDeleted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasDeleted->execute();
        $this->assertEmpty($dataWasDeleted->fetchAll());
    }
}