<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Migrations\Logger;

/**
 * Logger Test
 */
class LoggerTest extends TestCase
{
    /**
     * @var string $testLogsTable
     */
    private $testLogsTable;

    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;
    
    /**
     * @var Logger $logger
     */
    private $logger;

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

        $this->testLogsTable = 'db_logs_test';

        // create new migration instance
        $this->logger = new Logger($this->dbConfigs, $this->testLogsTable);
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
            SELECT * FROM {$this->testLogsTable}
        ");

        $logs->execute();
        $this->assertNotEmpty($logs->fetch());

        $dropMigrationsLogs = $this->connectToDatabase()->prepare("
            DROP TABLE {$this->testLogsTable}
        ");

        $dropMigrationsLogs->execute();
    }

    /**
     * Test logger fetch all migrations that can be run.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testLoggerFetchAllMigrationsThatCanBeRun()
    {
        $this->logger->log('my_migration_1');
        $this->logger->log('my_migration_2');
        $this->logger->log('my_migration_3');

        $this->assertTrue(
            in_array(
                'my_migration_4',
                $this->logger->canBeMigrated(
                    ['my_migration_2', 'my_migration_4']
                )
            )
        );
        
        $dropMigrationsLogs = $this->connectToDatabase()->prepare("
            DROP TABLE {$this->testLogsTable}
        ");

        $dropMigrationsLogs->execute();
    }
    
    /**
     * Test logger fetch all migrations that can be rolled back.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testLoggerFetchAllMigrationsThatCanBeRolledBack()
    {
        $this->logger->log('my_migration_123');

        $logs = $this->connectToDatabase()->prepare("
            DELETE FROM {$this->testLogsTable} WHERE 1;
        ");

        $logs->execute();

        $logs = $this->connectToDatabase()->prepare("
            INSERT INTO {$this->testLogsTable} 
                (migration, executed_at)
            VALUES
                ('test1', '2022-12-14 11:11:00'), 
                ('test2', '2022-12-15 12:12:00'), 
                ('test3', '2022-12-15 13:13:00'); 
        ");

        $logs->execute();

        $this->assertEquals(2, count($this->logger->canBeRolledBack()));
        
        $dropMigrationsLogs = $this->connectToDatabase()->prepare("
            DROP TABLE {$this->testLogsTable}
        ");

        $dropMigrationsLogs->execute();
    }

    /**
     * Test log's record is removed for rolled back migration.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testLogsRecordIsRemovedForRolledBackMigration()
    {
        $this->logger->log('my_migration_1');
        $this->logger->log('my_migration_2');

        $this->logger->removeLog('my_migration_2');

        $logs = $this->connectToDatabase()->prepare("
            SELECT * FROM {$this->testLogsTable}
        ");

        $logs->execute();
        $this->assertEquals(1, count($logs->fetchAll(\PDO::FETCH_COLUMN, 1)));

        $dropMigrationsLogs = $this->connectToDatabase()->prepare("
            DROP TABLE {$this->testLogsTable}
        ");

        $dropMigrationsLogs->execute();
    }
}