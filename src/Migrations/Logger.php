<?php

namespace SigmaPHP\DB\Migrations;

use SigmaPHP\DB\Interfaces\Migrations\LoggerInterface;


/**
 * Logger Class
 */
class Logger implements LoggerInterface
{
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
    public function __construct($dbConfigs)
    {
        $this->dbConfigs = $dbConfigs;

        $this->connection = new \PDO(
            "mysql:host={$this->dbConfigs['host']};
            dbname={$this->dbConfigs['name']}",
            $this->dbConfigs['user'],
            $this->dbConfigs['pass']
        );
    }

    /**
     * Create migrations logs table if doesn't exists.
     * 
     * @return void
     */
    private function createLogsTable()
    {
        $createLogsTable = $this->connection->prepare("
            CREATE TABLE IF NOT EXISTS {$this->dbConfigs['logs_table_name']} (
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
    public function log($migration)
    {
        $this->createLogsTable();

        $createLogsTable = $this->connection->prepare("
            INSERT INTO {$this->dbConfigs['logs_table_name']} (migration)
            VALUES ('$migration')
            ;
        ");

        $createLogsTable->execute();
    }
}