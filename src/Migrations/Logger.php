<?php

namespace SigmaPHP\DB\Migrations;

use SigmaPHP\DB\Interfaces\Migrations\LoggerInterface;
use SigmaPHP\DB\Connectors\Connector;
use SigmaPHP\DB\Traits\DbMethods;

/**
 * Logger Class
 */
class Logger implements LoggerInterface
{
    use DbMethods;
    
    /**
     * @var Connector $dbConnection
     */
    private $dbConnection;

    /**
     * @var string $logsTable
     */
    private $logsTable;
    
    /**
     * Logger Constructor
     */
    public function __construct($dbConnection, $logsTable)
    {
        $this->dbConnection = $dbConnection;
        $this->logsTable = $logsTable;

        $this->createLogsTable();
    }

    /**
     * Create migrations logs table if doesn't exists.
     * 
     * @return void
     */
    private function createLogsTable()
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS {$this->logsTable} (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    /**
     * Log the latest migration status.
     * 
     * @param $string $migration the migration file name
     * @return void
     */
    final public function log($migration)
    {
        $this->execute("
            INSERT INTO {$this->logsTable} (migration)
            VALUES ('$migration')
            ;
        ");
    }

    /**
     * Get all migrations files that can be migrated.
     * 
     * @param array $migrations
     * @return array
     */
    final public function canBeMigrated($migrations)
    {
        $allLoggedMigrations = $this->fetchColumn("
            SELECT migration FROM {$this->logsTable};
        ", 0);

        return array_diff(
            $migrations,
            $allLoggedMigrations,
        );
    }
    
    /**
     * Get all migrations that can be rolled back.
     * 
     * @return array
     */
    final public function canBeRolledBack()
    {
        return $this->fetchColumn("
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
        ", 0);
    }

    /**
     * Remove the log for a migration.
     * 
     * @param string $migration the migration file name
     * @return void
     */
    final public function removeLog($migration)
    {
        $this->execute("
            DELETE FROM 
                {$this->logsTable} 
            WHERE 
                migration='$migration';
        ");
    }
}