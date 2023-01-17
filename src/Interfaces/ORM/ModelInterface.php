<?php

namespace SigmaPHP\DB\Interfaces\ORM;

/**
 * Model Interface
 */
interface ModelInterface
{
    /**
     * Use the query builder on the model.
     * 
     * @return object
     */
    public static function query();
}