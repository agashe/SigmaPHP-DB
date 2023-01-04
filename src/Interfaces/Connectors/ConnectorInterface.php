<?php

namespace SigmaPHP\DB\Interfaces\Connectors;

/**
 * Connector Interface
 */
interface ConnectorInterface
{
    /**
     * Create new PDO connection.
     * 
     * @param array $configs 
     * @return \PDO
     */
    public function connect($configs);
}