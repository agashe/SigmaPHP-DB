<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Migrations\Migration;

/**
 * Migration Test
 */
class MigrationTest extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;
    
    /**
     * @var Migration $migration
     */
    private $migration;

    /**
     * MigrationTest SetUp
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
            'pass' => $GLOBALS['DB_PASS']
        ];

        $this->migration = new Migration($this->dbConfigs);
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
     * Check if table exists.
     *
     * @param string $table
     * @return bool
     */
    private function checkTableExists($table)
    {
        $tableExists = $this->connectToDatabase()->prepare("
            SELECT
                TABLE_NAME
            FROM 
                INFORMATION_SCHEMA.TABLES
            WHERE 
                TABLE_SCHEMA = '{$this->dbConfigs['name']}'
            AND
                TABLE_NAME = '{$table}'
        ");

        $tableExists->execute();
        return ($tableExists->fetch() != false);
    }

    /**
     * Get table fields.
     *
     * @param string $table
     * @return array
     */
    protected function getTableFields($table)
    {
        $tableFields = $this->connectToDatabase()->prepare("
            SELECT
                GROUP_CONCAT(COLUMN_NAME) AS FIELDS
            FROM 
                INFORMATION_SCHEMA.COLUMNS
            WHERE 
                TABLE_SCHEMA = '{$this->dbConfigs['name']}'
            AND
                TABLE_NAME = '{$table}'
        ");

        $tableFields->execute();
        $fields = explode(',', $tableFields->fetchAll()[0]['FIELDS']);

        return array_values($fields);
    }

    /**
     * Test execute SQL statements.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testExecuteSqlStatements()
    {
        $this->createTestTable();

        $this->migration->execute('DROP TABLE IF EXISTS test');

        $this->assertFalse($this->checkTableExists('test'));
    }

    /**
     * Test create table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCreateTable()
    {
        $this->migration->createTable(
            'test',
            [
                ['name' => 'id', 'type' => 'bigint', 'primary' => true],
                ['name' => 'title', 'type' => 'varchar', 'size' => 25],
            ],
            [
                'engine' => 'innodb'
            ]
        );
        
        $this->assertTrue($this->checkTableExists('test'));
        $this->assertEquals(2, count($this->getTableFields('test')));

        $this->dropTestTable();
    }
    
    /**
     * Test update table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateTable()
    {
        $this->createTestTable();

        $this->migration->updateTable(
            'test',
            [
                'comment' => 'hello world'
            ]
        );

        // get table comment
        $tableComment = $this->connectToDatabase()->prepare("
            SELECT
                TABLE_COMMENT
            FROM 
                INFORMATION_SCHEMA.TABLES
            WHERE 
                TABLE_SCHEMA = '{$this->dbConfigs['name']}'
            AND
                TABLE_NAME = 'test'
        ");

        $tableComment->execute();   

        $this->assertEquals('hello world', 
            $tableComment->fetch()['TABLE_COMMENT']);

        $this->dropTestTable();
    }

    /**
     * Test rename table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testRenameTable()
    {
        $this->createTestTable();

        $this->migration->renameTable('test', 'test_renamed');

        $this->assertTrue($this->checkTableExists('test_renamed'));

        $this->dropTestTable('test_renamed');
    }
    
    /**
     * Test check table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCheckTable()
    {
        $this->createTestTable();

        $this->assertTrue($this->migration
            ->checkTable('test'));

        $this->dropTestTable();
    }

    /**
     * Test change primary key for table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testChangePrimaryKeyForTable()
    {
        $this->createTestTable();

        $this->migration->changeTablePrimaryKey('test', 'id', 'name');

        // get table primary key
        $tablePrimaryKey = $this->connectToDatabase()->prepare("
            show columns from test where `Key` = 'PRI';
        ");

        $tablePrimaryKey->execute();   

        $this->assertEquals('name', 
            $tablePrimaryKey->fetch()['Field']);

        $this->dropTestTable();
    }

    /**
     * Test drop table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDropTable()
    {
        $this->createTestTable();

        $this->migration->dropTable('test');

        $this->assertFalse($this->checkTableExists('test_renamed'));
    }

    /**
     * Test add column to table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testAddColumnToTable()
    {
        $this->createTestTable();

        $this->migration->addColumn(
            'test',
            'my_field',
            [
                'type' => 'varchar',
                'size' => 25
            ]
        );

        $this->assertEquals(4, count($this->getTableFields('test')));

        $this->dropTestTable();
    }

    /**
     * Test update column in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateColumnInTable()
    {
        $this->createTestTable();

        $this->migration->updateColumn(
            'test',
            'email',
            [
                'type' => 'text',
            ]
        );

        $fieldType = $this->connectToDatabase()->prepare("
            SELECT
                DATA_TYPE
            FROM 
                INFORMATION_SCHEMA.COLUMNS
            WHERE 
                TABLE_SCHEMA = '{$this->dbConfigs['name']}'
            AND
                TABLE_NAME = 'test'
            AND
                COLUMN_NAME = 'email'
        ");

        $fieldType->execute();

        $this->assertEquals('text', $fieldType->fetch()['DATA_TYPE']);

        $this->dropTestTable();
    }

    /**
     * Test rename column in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testRenameColumnInTable()
    {
        $this->createTestTable();

        $this->migration->renameColumn(
            'test',
            'email',
            'my_field'
        );

        $fieldRenamed = $this->connectToDatabase()->prepare("
            SELECT
                COLUMN_NAME
            FROM 
                INFORMATION_SCHEMA.COLUMNS
            WHERE 
                TABLE_SCHEMA = '{$this->dbConfigs['name']}'
            AND
                TABLE_NAME = 'test'
            AND
                COLUMN_NAME = 'my_field'
        ");

        $fieldRenamed->execute();

        $this->assertNotEmpty($fieldRenamed->fetch());

        $this->dropTestTable();
    }

    /**
     * Test drop column from table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDropColumnFromTable()
    {
        $this->createTestTable();

        $this->migration->dropColumn('test', 'email');

        $this->assertEquals(2, count($this->getTableFields('test')));

        $this->dropTestTable();
    }
}