<?php 

namespace SigmaPHP\DB\TestCases;

use PHPUnit\Framework\TestCase;

/**
 * Db Test Case
 */
class DbTestCase extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    protected $dbConfigs;
    
    /**
     * DbTestCase SetUp
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
    }
    
    /**
     * DbTestCase TearDown
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
    protected function connectToDatabase()
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
    protected function createTestTable($name = 'test')
    {
        $testTable = $this->connectToDatabase()->prepare("
            CREATE TABLE IF NOT EXISTS {$name} (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(25) NOT NULL,
                email VARCHAR(50) NOT NULL,
                age INT UNSIGNED DEFAULT 0
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
    protected function dropTestTable($name = 'test')
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
    protected function checkTableExists($table)
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
}