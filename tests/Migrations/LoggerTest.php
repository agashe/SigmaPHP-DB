<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Migrations\Logger;

/**
 * Logger Test
 */
class LoggerTest extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;
    
    /**
     * @var Logger $migration
     */
    private $migration;

    /**
     * LoggerTest SetUp
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
        ];

        $this->dbConfigs['logs_table_name'] = 'db_logs';

        // create new migration instance
        $this->logger = new Logger($this->dbConfigs);
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
     * Test log's record is created for new migration.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testLogsRecordIsCreatedForNewMigration()
    {
        $this->logger->log('my_migration_123');

        $logs = $this->connectToDatabase()->prepare("
            SELECT * FROM {$this->dbConfigs['logs_table_name']}
        ");

        $logs->execute();
        $this->assertNotEmpty($logs->fetch());

        $dropMigrationsLogs = $this->connectToDatabase()->prepare("
            DROP TABLE {$this->dbConfigs['logs_table_name']}
        ");

        $dropMigrationsLogs->execute();
    }
}