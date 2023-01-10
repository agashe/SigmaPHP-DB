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
    public function fetchColumn($query, $columnId)
    {
        $handler = $this->dbConnection->prepare($query);
        $handler->execute();
        return $handler->fetchAll(\PDO::FETCH_COLUMN, $columnId);
    }
}
