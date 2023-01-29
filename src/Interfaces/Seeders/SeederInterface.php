<?php

namespace SigmaPHP\DB\Interfaces\Seeders;

/**
 * Seeder Interface
 */
interface SeederInterface
{
    /**
     * Execute seeder instructions.
     * 
     * This method will be overridden by children classes (seeders). And
     * will be called by the 'seed' command in the CLI tool.
     * 
     * @return void
     */
    public function run();
}