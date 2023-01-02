<?php

namespace SigmaPHP\DB\Migrations;

use SigmaPHP\DB\Interfaces\Migrations\LoggerInterface;


/**
 * Logger Class
 */
class Logger implements LoggerInterface
{
    /**
     * @var string $logsTable
     */
    private $logsTable;

    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;

    /**
     * @var \PDO $connection
     */
    private $connection;
    
    /**
     * Logger Constructor
     */
    public function __construct($dbConfigs, $logsTable)
    {
        $this->dbConfigs = $dbConfigs;
        $this->logsTable = $logsTable;

        $this->connection = new \PDO(
            "mysql:host={$this->dbConfigs['host']};
            dbname={$this->dbConfigs['name']}",
            $this->dbConfigs['user'],
            $this->dbConfigs['pass']
        );

        $this->createLogsTable();
    }

    /**
     * Create migrations logs table if doesn't exists.
     * 
     * @return void
     */
    private function createLogsTable()
    {
        $createLogsTable = $this->connection->prepare("
            CREATE TABLE IF NOT EXISTS {$this->logsTable} (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $createLogsTable->execute();
    }

    /**
     * Log the latest migration status.
     * 
     * @param $string $migration the migration file name
     * @return void
     */
    final public function log($migration)
    {
        $createLogsTable = $this->connection->prepare("
            INSERT INTO {$this->logsTable} (migration)
            VALUES ('$migration')
            ;
        ");

        $createLogsTable->execute();
    }

    /**
     * Get all migrations files that can be migrated.
     * 
     * @param array $migrations
     * @return array
     */
    final public function canBeMigrated($migrations)
    {
        $allLoggedMigrations = $this->connection->prepare("
            SELECT migration FROM {$this->logsTable};
        ");

        $allLoggedMigrations->execute();

        return array_diff(
            $migrations,
            $allLoggedMigrations->fetchAll(\PDO::FETCH_COLUMN, 0),
        );
    }
    
    /**
     * Get all migrations that can be rolled back.
     * 
     * @return array
     */
    final public function canBeRolledBack()
    {
        $migrations = $this->connection->prepare("
            SELECT
                migration 
            FROM
                {$this->logsTable}
            WHERE DATE(executed_at) = (
                SELECT
                    DATE(executed_at)
                FROM
                    {$this->logsTable}
                GROUP BY
                    DATE(executed_at) 
                ORDER BY
                    DATE(executed_at) DESC
                LIMIT 1
            );
        ");

        $migrations->execute();
        return $migrations->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Remove the log for a migration.
     * 
     * @param string $migration the migration file name
     * @return void
     */
    final public function removeLog($migration)
    {
        $removeMigration = $this->connection->prepare("
            DELETE FROM 
                {$this->logsTable} 
            WHERE 
                migration='$migration';
        ");

        $removeMigration->execute();
    }
}