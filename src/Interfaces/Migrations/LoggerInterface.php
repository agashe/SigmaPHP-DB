<?php

namespace SigmaPHP\DB\Interfaces\Migrations;

/**
 * Logger Interface
 */
interface LoggerInterface
{
    /**
     * Log the latest migration status.
     * 
     * @param string $migration the migration file name
     * @return void
     */
    public function log($migration);

    /**
     * Get all migrations files that can be migrated.
     * 
     * @param array $migrations
     * @return array
     */
    public function canBeMigrated($migrations);

    /**
     * Get all migrations that can be rolled back.
     * 
     * @param string $date
     * @return array
     */
    public function canBeRolledBack($date);

    /**
     * Remove the log for a migration.
     * 
     * @param string $migration the migration file name
     * @return void
     */
    public function removeLog($migration);
}