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

    /**
     * Insert data into table.
     * 
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insert($table, $data);
    
    /**
     * Update data in table.
     * 
     * @param string $table
     * @param array $data
     * @param array $search
     * @return void
     */
    public function update($table, $data, $search);

    /**
     * Delete data from table.
     * 
     * @param string $table
     * @param array $search
     * @return void
     */
    public function delete($table, $search);
}