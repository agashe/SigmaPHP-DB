<?php

namespace SigmaPHP\DB\Traits;

use SigmaPHP\DB\Traits\DbMethods;
use SigmaPHP\DB\Traits\HelperMethods;

/**
 * DB Operations Trait.
 */
trait DbOperations
{
    use DbMethods, HelperMethods;

    /**
     * Insert data into table.
     * 
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insert($table, $data)
    {
        $fields = '';
        $values = '';
        
        foreach ($data as $row) {
            $fields = implode(',', array_keys($row));            
            $values .= 
                '(' . $this->concatenateTokens(array_values($row), true) . '),';
        }

        $values = rtrim($values, ',');

        $this->execute("
            INSERT INTO $table ($fields) VALUES {$values};
        ");
    }
    
    /**
     * Get the newly inserted row's primary key value.
     * 
     * @return void
     */
    public function getLatestInsertedRowPrimaryKeyValue()
    {
        return $this->fetchColumn("SELECT LAST_INSERT_ID()")[0];
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
            $val = $this->addQuotes($val); 
            $updateStatement .= "$col = $val,";
        }

        $updateStatement = rtrim($updateStatement, ',');

        if (isset($search) && !empty($search)) {
            $field = implode('', array_keys($search));
            $value = $this->addQuotes(implode('', array_values($search)));
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
            $value = $this->addQuotes(implode('', array_values($search)));
            $condition = "$field = $value";
        }

        $this->execute("
            DELETE FROM $table WHERE $condition;
        ");
    }
}