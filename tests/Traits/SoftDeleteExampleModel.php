<?php

use SigmaPHP\DB\ORM\Model;
use SigmaPHP\DB\Traits\SoftDelete;

/**
 * SoftDelete Example Model Class
 */
class SoftDeleteExampleModel extends Model
{
    use SoftDelete;

    /**
     * @var string $table
     */
    protected $table = 'test_soft_delete';
}
