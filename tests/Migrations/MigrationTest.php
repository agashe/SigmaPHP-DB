<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Migrations\Migration;
use SigmaPHP\DB\Connectors\Connector;

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
            'pass' => $GLOBALS['DB_PASS'],
            'port' => $GLOBALS['DB_PORT'],
        ];

        // create test table
        $this->createTestTable();
        
        // create new migration instance
        $connector = new Connector();
        $this->migration = new Migration(
            $connector->connect($this->dbConfigs),
            $this->dbConfigs['name']
        );
    }
    
    /**
     * MigrationTest TearDown
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
    private function getTableFields($table)
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
            'my_table',
            [
                ['name' => 'id', 'type' => 'bigint', 'primary' => true],
                ['name' => 'title', 'type' => 'varchar', 'size' => 25],
            ],
            [
                'engine' => 'innodb'
            ]
        );
        
        $this->assertTrue($this->checkTableExists('my_table'));
        $this->assertEquals(2, count($this->getTableFields('my_table')));

        $this->dropTestTable('my_table');
    }
    
    /**
     * Test update table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateTable()
    {
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
    }

    /**
     * Test rename table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testRenameTable()
    {
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
        $this->assertTrue($this->migration
            ->checkTable('test'));
    }

    /**
     * Test change primary key for table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testChangePrimaryKeyForTable()
    {
        $this->migration->changeTablePrimaryKey('test', 'id', 'name');

        // get table primary key
        $tablePrimaryKey = $this->connectToDatabase()->prepare("
            show columns from test where `Key` = 'PRI';
        ");

        $tablePrimaryKey->execute();   

        $this->assertEquals('name', 
            $tablePrimaryKey->fetch()['Field']);
    }

    /**
     * Test drop table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDropTable()
    {
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
    }

    /**
     * Test rename column in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testRenameColumnInTable()
    {
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
    }

    /**
     * Test check column does exists in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCheckColumnDoesExistsInTable()
    {
        $this->assertTrue($this->migration->checkColumn('test', 'email'));
    }
    
    /**
     * Test drop column from table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDropColumnFromTable()
    {
        $this->migration->dropColumn('test', 'email');
        $this->assertEquals(2, count($this->getTableFields('test')));
    }

    /**
     * Test add index to table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testAddIndexToTable()
    {
        $this->migration->addIndex(
            'test',
            'test_index',
            ['name', 'email'],
            'normal',
            [
                'name' => 'desc'
            ]
        );

        $indexWasCreated = $this->connectToDatabase()->prepare("
            SHOW INDEX FROM test WHERE Key_name='test_index'
        ");

        $indexWasCreated->execute();
        $this->assertNotEmpty($indexWasCreated->fetch());
    }

    /**
     * Test check index does exists in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCheckIndexDoesExistsInTable()
    {
        $createIndex = $this->connectToDatabase()->prepare("
            CREATE INDEX test_index ON test (name);
        ");

        $createIndex->execute();
        $this->assertTrue($this->migration->checkIndex('test', 'test_index'));
    }

    /**
     * Test drop index from table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDropIndexFromTable()
    {
        $createIndex = $this->connectToDatabase()->prepare("
            CREATE INDEX test_index ON test (name);
        ");

        $createIndex->execute();

        $this->migration->dropIndex('test', 'test_index');

        $indexWasDeleted = $this->connectToDatabase()->prepare("
            SHOW INDEX FROM test WHERE Key_name='test_index'
        ");

        $indexWasDeleted->execute();
        $this->assertEmpty($indexWasDeleted->fetch());
    }

    /**
     * Test add foreign key to table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testAddForeignKeyToTable()
    {
        $this->createTestTable('test2');
        
        $this->migration->addForeignKey(
            'test_foreign_key',
            'test',
            'id',
            'test2',
            'id',
            [
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ]
        );

        $foreignKeyExists = $this->connectToDatabase()->prepare("
            SELECT
                CONSTRAINT_NAME
            FROM 
                INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE 
                TABLE_NAME = 'test'
            AND
                CONSTRAINT_NAME = 'test_foreign_key';
        ");

        $foreignKeyExists->execute();
        $this->assertNotEmpty($foreignKeyExists->fetch());

        $this->connectToDatabase()->prepare("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE test2;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }
    
    /**
     * Test check foreign key does exists in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCheckForeignKeyDoesExistsInTable()
    {
        $this->createTestTable('test2');
        
        $createForeignKey = $this->connectToDatabase()->prepare("
            ALTER TABLE test ADD CONSTRAINT test_foreign_key
            FOREIGN KEY (id) REFERENCES test2(id)
        ");

        $createForeignKey->execute();

        $foreignKeyExists = $this->connectToDatabase()->prepare("
            SELECT
                CONSTRAINT_NAME
            FROM 
                INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE 
                TABLE_NAME = 'test'
            AND
                CONSTRAINT_NAME = 'test_foreign_key';
        ");

        $foreignKeyExists->execute();
        $this->assertNotEmpty($foreignKeyExists->fetch());

        $this->connectToDatabase()->prepare("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE test2;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }

    /**
     * Test drop foreign key from table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDropForeignKeyFromTable()
    {
        $this->createTestTable('test2');
        
        $createForeignKey = $this->connectToDatabase()->prepare("
            ALTER TABLE test ADD CONSTRAINT test_foreign_key
            FOREIGN KEY (id) REFERENCES test2(id)
        ");

        $createForeignKey->execute();

        $this->migration->dropForeignKey('test', 'test_foreign_key');

        $foreignKeyExists = $this->connectToDatabase()->prepare("
            SELECT
                CONSTRAINT_NAME
            FROM 
                INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE 
                TABLE_NAME = 'test'
            AND
                CONSTRAINT_NAME = 'test_foreign_key';
        ");

        $foreignKeyExists->execute();
        $this->assertEmpty($foreignKeyExists->fetch());

        $this->connectToDatabase()->prepare("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE test2;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }
}