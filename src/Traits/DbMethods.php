<?php

namespace SigmaPHP\DB\Traits;

/**
 * DB Methods Trait.
 */
trait DbMethods
{
    /**
     * Execute SQL statements.
     *
     * @param string $statement
     * @return bool
     */
    public function execute($statement)
    {
        return $this->dbConnection->prepare($statement)->execute();
    }
    
    /**
     * Execute SQL query and fetch single result.
     *
     * @param string $query
     * @return string|bool
     */
    public function fetch($query)
    {
        $handler = $this->dbConnection->prepare($query);
        $handler->execute();
        return $handler->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute SQL query and fetch all the rows in the result. 
     *
     * @param string $query
     * @return array
     */
    public function fetchAll($query)
    {
        $handler = $this->dbConnection->prepare($query);
        $handler->execute();
        return $handler->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute SQL query and fetch single column values. 
     *
     * @param string $query
     * @param int $columnId
     * @return array
     */
    public function fetchColumn($query, $columnId = 0)
    {
        $handler = $this->dbConnection->prepare($query);
        $handler->execute();
        return $handler->fetchAll(\PDO::FETCH_COLUMN, $columnId);
    }

    /**
     * Check if table exists. 
     *
     * @param string $dbName
     * @param string $tableName
     * @return array
     */
    public function tableExists($dbName, $tableName)
    {
        return (bool) $this->fetch("
            SELECT
                TABLE_NAME
            FROM 
                INFORMATION_SCHEMA.TABLES
            WHERE 
                TABLE_SCHEMA = '{$dbName}'
            AND
                TABLE_NAME = '{$tableName}';
        ");
    }
    
    /**
     * Get all tables names in the database. 
     *
     * @param string $dbName
     * @return array
     */
    public function getAllTables($dbName)
    {
        return $this->fetchColumn("
            SELECT
                TABLE_NAME
            FROM
                INFORMATION_SCHEMA.TABLES
            WHERE 
                TABLE_SCHEMA = '{$dbName}';
        ");
    }
}
