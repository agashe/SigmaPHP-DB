<?php

namespace SigmaPHP\DB\Seeders;

use SigmaPHP\DB\Traits\DbOperations;
use SigmaPHP\DB\QueryBuilders\QueryBuilder;
use SigmaPHP\DB\Interfaces\Seeders\SeederInterface;

/**
 * Seeder Class
 */
class Seeder implements SeederInterface
{
    use DbOperations;

    /**
     * @var \PDO $dbConnection
     */
    private $dbConnection;

    /**
     * @var QueryBuilder $queryBuilder
     */
    protected $queryBuilder;
    
    /**
     * Seeder Constructor
     * 
     * @param \PDO $dbConnection
     */
    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->queryBuilder = new QueryBuilder($this->dbConnection);
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
}