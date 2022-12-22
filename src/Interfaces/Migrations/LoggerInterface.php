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
     * @param $string $migration the migration file name
     * @return void
     */
    public function log($migration);
}