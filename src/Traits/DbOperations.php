<?php

namespace SigmaPHP\DB\Traits;

use SigmaPHP\DB\Traits\DbMethods;

/**
 * DB Operations Trait.
 */
trait DbOperations
{
    use DbMethods;

    /**
     * Insert data into table.
     * 
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insert($table, $data)
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
    public function update($table, $data, $search = [])
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
    public function delete($table, $search = [])
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