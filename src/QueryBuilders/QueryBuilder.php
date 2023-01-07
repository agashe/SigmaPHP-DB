<?php

namespace SigmaPHP\DB\QueryBuilders;

use SigmaPHP\DB\Interfaces\QueryBuilders\QueryBuilderInterface;
use SigmaPHP\DB\Connectors\Connector;
use SigmaPHP\DB\Traits\DbMethods;

/**
 * QueryBuilder Class
 */
class QueryBuilder implements QueryBuilderInterface
{
    use DbMethods;

    /**
     * @var Connector $dbConnection
     */
    private $dbConnection;

    /**
     * QueryBuilder Constructor
     */
    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }
}