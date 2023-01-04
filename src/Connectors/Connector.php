<?php

namespace SigmaPHP\DB\Connectors;

use SigmaPHP\DB\Interfaces\Connectors\ConnectorInterface;

/**
 * Connector Class
 */
class Connector implements ConnectorInterface
{
    /**
     * Create new PDO connection.
     * 
     * @param array $configs 
     * @return \PDO
     */
    public function connect($configs)
    {
        return new \PDO(
            "mysql:host={$configs['host']};
            dbname={$configs['name']};
            port={$configs['port']}",
            $configs['user'],
            $configs['pass']
        );
    }
}