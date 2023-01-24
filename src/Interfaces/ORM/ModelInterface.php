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
    public function query();

    /**
     * Create model from an array of data.
     * 
     * @param array $modelData
     * @return object
     */
    public function create($modelData);
    
    /**
     * Fetch all models.
     *
     * @return array
     */
    public function all();

    /**
     * Find model by primary key.
     *
     * @param mixed $primaryValue
     * @return Model
     */
    public function find($primaryValue);

    /**
     * Find model by field's value.
     *
     * @param string $field
     * @param int $value
     * @return array
     */
    public function findBy($field, $value);
    
    /**
     * Save model , by updating current model 
     * or creating new one.
     *
     * @return array
     */
    public function save();
    
    /**
     * Delete model.
     *
     * @return array
     */
    public function delete();
}