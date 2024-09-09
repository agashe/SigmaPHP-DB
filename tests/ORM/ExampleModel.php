<?php

namespace SigmaPHP\DB\Tests\ORM;

use SigmaPHP\DB\ORM\Model;

/**
 * Example Model Class
 */
class ExampleModel extends Model
{
    /**
     * @return array
     */
    public function relationExamples()
    {
        return $this->hasRelation(
            RelationExampleModel::class,
            'example_id',
            'id'
        );
    }
}