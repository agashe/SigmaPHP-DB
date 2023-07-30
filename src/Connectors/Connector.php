<?php

namespace SigmaPHP\DB\Connectors;

use SigmaPHP\DB\Interfaces\Connectors\ConnectorInterface;

/**
 * Connector Class
 */
class Connector implements ConnectorInterface
{
    /**
     * @var array $configs
     */
    private $configs;

    /**
     * Connector Constructor
     * 
     * @param array $configs
     */
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Create new PDO connection.
     * 
     * @return \PDO
     */
    final public function connect()
    {
        return new \PDO(
            "mysql:host={$this->configs['host']};
            dbname={$this->configs['name']};
            port={$this->configs['port']}",
            $this->configs['user'],
            $this->configs['pass']
        );
    }

    /**
     * Get the database name.
     * 
     * @return string
     */
    final public function getDatabaseName()
    {
        return $this->configs['name'];
    }
}