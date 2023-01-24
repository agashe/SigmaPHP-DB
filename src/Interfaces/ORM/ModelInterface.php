<?php

namespace SigmaPHP\DB\Interfaces\ORM;

/**
 * Model Interface
 */
interface ModelInterface
{
    /**
     * Create model from an array of data.
     * 
     * @param array $modelData
     * @param bool $isNew
     * @return object
     */
    public function create($modelData, $isNew);
    
    /**
     * Fetch all models.
     *
     * @return array
     */
    public function all();

    /**
     * Count all models.
     *
     * @return int
     */
    public function count();

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
     * @return mixed
     */
    public function save();
    
    /**
     * Delete model.
     *
     * @return bool
     */
    public function delete();
}