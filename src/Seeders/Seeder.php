<?php

namespace SigmaPHP\DB\Seeders;

use SigmaPHP\DB\Interfaces\Seeders\SeederInterface;

/**
 * Seeder Class
 */
class Seeder implements SeederInterface
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
     * Execute seeder instructions.
     * 
     * This method will be overridden by children classes (seeders). And
     * will be called by the 'seed' command in the CLI tool.
     * 
     * @return void
     */
    public function run(){}

    /**
     * Execute SQL statements.
     *
     * @param string $statement
     * @return void
     */
    final public function execute($statement)
    {
        try {
            $this->connection
                ->prepare($statement)
                ->execute();
        } catch (\Exception $e) {
            echo $e;
        }
    }

    /**
     * Insert data into table.
     * 
     * @param string $table
     * @param array $data
     * @return void
     */
    final public function insert($table, $data)
    {
        foreach ($data as $row) {
            $fields = '';
            $values = '';

            $fields = implode(',', array_keys($row));
            $values = array_values($row);
            
            $valuesStr = '';
            foreach ($values as $value) {
                $valuesStr .= (is_string($value) ? "'$value'" : $value ). ','; 
            }

            $valuesStr = rtrim($valuesStr, ',');
            
            $this->execute("
                INSERT INTO $table ($fields) VALUES ($valuesStr);
            ");
        }
    }
    
    /**
     * Update data in table.
     * 
     * @param string $table
     * @param array $data
     * @param array $search
     * @return void
     */
    final public function update($table, $data, $search = '')
    {
        $updateStatement = "UPDATE $table SET ";

        foreach ($data as $col => $val) {
            $val = is_string($val) ? "'$val'" : $val; 
            $updateStatement .= "$col = $val,";
        }

        $updateStatement = rtrim($updateStatement, ',');

        if (isset($search) && !empty($search)) {
            $field = implode('', array_keys($search));
            $value = implode('', array_values($search));
            $value = is_string($value) ? "'$value'" : $value;
            $updateStatement .= " WHERE $field = $value;";
        }

        $this->execute($updateStatement);
    }

    /**
     * Delete data from table.
     * 
     * @param string $table
     * @param array $search
     * @return void
     */
    final public function delete($table, $search = '')
    {
        $condition = 1;
        
        if (isset($search) && !empty($search)) {
            $field = implode('', array_keys($search));
            $value = implode('', array_values($search));
            $value = is_string($value) ? "'$value'" : $value;
            $condition = "$field = $value";
        }

        $this->execute("
            DELETE FROM $table WHERE $condition;
        ");
    }
}