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
     * @return void
     */
    public function log();
}