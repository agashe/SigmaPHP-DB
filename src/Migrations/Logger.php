<?php

namespace SigmaPHP\DB\Migrations;

use SigmaPHP\DB\Interfaces\Migrations\LoggerInterface;
use SigmaPHP\DB\Traits\DbOperations;

/**
 * Logger Class
 */
class Logger implements LoggerInterface
{
    use DbOperations;
    
    /**
     * @var \PDO $dbConnection
     */
    private $dbConnection;

    /**
     * @var string $logsTable
     */
    private $logsTable;
    
    /**
     * Logger Constructor
     * 
     * @param \PDO $dbConnection
     * @param string $logsTable
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
        $this->insert($this->logsTable, [
            ['migration' => $migration]
        ]);
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
        ");

        return array_diff(
            $migrations,
            $allLoggedMigrations,
        );
    }
    
    /**
     * Get all migrations that can be rolled back.
     * 
     * @param string $date
     * @return array
     */
    final public function canBeRolledBack($date = '')
    {
        $dateExpression = '';

        if (empty($date)) {
            $dateExpression = "= (
                SELECT
                    DATE(executed_at)
                FROM
                    {$this->logsTable}
                GROUP BY
                    DATE(executed_at) 
                ORDER BY
                    DATE(executed_at) DESC
                LIMIT 1
            )";
        } else {
            $dateExpression = ">= '{$date}'";
        }

        return $this->fetchColumn("
            SELECT
                migration 
            FROM
                {$this->logsTable}
            WHERE DATE(executed_at) {$dateExpression};
        ");
    }

    /**
     * Remove the log for a migration.
     * 
     * @param string $migration the migration file name
     * @return void
     */
    final public function removeLog($migration)
    {
        $this->delete($this->logsTable, ['migration' => $migration]);
    }
}