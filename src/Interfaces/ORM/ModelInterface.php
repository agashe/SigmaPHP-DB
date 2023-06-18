<?php

namespace SigmaPHP\DB\Interfaces\ORM;

/**
 * Model Interface
 */
interface ModelInterface
{
    /**
     * Get table name.
     *
     * @return string
     */
    public function getTableName();
    
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
     * Fetch first model.
     *
     * @return Model|null
     */
    public function first();

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
     * Where query on models.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    public function where($field, $operator, $value);

    /**
     * And where query on models.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    public function andWhere($field, $operator, $value);

    /**
     * Or where query on models.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    public function orWhere($field, $operator, $value);

    /**
     * Where condition on model's relations.
     *
     * @param string $relation
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    public function whereHas($relation, $field, $operator, $value);
    
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
     * @return void
     */
    public function delete();

    /**
     * Get one/many models in another table 
     * related to this model.
     *
     * @param Model $model
     * @param string $foreignKey
     * @param string $localKey
     * @return array
     */
    public function hasRelation($model, $foreignKey, $localKey);
}