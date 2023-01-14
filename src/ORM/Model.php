<?php

namespace SigmaPHP\DB\ORM;

use SigmaPHP\DB\Interfaces\ORM\ModelInterface;
use SigmaPHP\DB\Connectors\Connector;
use SigmaPHP\DB\Traits\DbMethods;

/**
 * Model Class
 */
class Model implements ModelInterface
{
    use DbMethods;

    /**
     * @var Connector $dbConnection
     */
    private $dbConnection;

    /**
     * Model Constructor
     */
    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }
}