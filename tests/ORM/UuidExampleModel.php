<?php

namespace SigmaPHP\DB\Tests\ORM;

use SigmaPHP\DB\ORM\Model;

/**
 * UUID Example Model Class
 */
class UuidExampleModel extends Model
{
    /**
     * @var bool $uuid
     * to control using UUID as primary keys in models
     */
    protected $uuid = true;    
}