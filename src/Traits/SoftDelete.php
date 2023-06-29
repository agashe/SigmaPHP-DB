<?php

namespace SigmaPHP\DB\Traits;

/**
 * SoftDelete Trait.
 */
trait SoftDelete
{
    /**
     * Delete model by updating its deletion date field.
     *
     * @return void
     */
    public function trash()
    {
        $this->update(
            $this->table,
            [$this->softDeleteFieldName => 'NOW()'],
            [$this->primary => $this->values[$this->primary]]
        );
    }
    
    /**
     * Allow soft deleted models to be returned in the query results.
     *
     * @return object
     */
    public function withTrashed()
    {
        $this->fetchTrashedWithQuery = true;
        return $this;
    }

    /**
     * Return only soft deleted models in the query results.
     *
     * @return object
     */
    public function onlyTrashed()
    {
        $this->fetchOnlyTrashed = true;
        return $this;
    }
    
    /**
     * Restore soft deleted model.
     *
     * @return void
     */
    public function restore()
    {
        $this->update(
            $this->table,
            [$this->softDeleteFieldName => 'NULL'],
            [$this->primary => $this->values[$this->primary]]
        );

        // mark the model as exists
        $this->isNew = false;
    }
    
    /**
     * Check if model was soft deleted.
     *
     * @return bool
     */
    public function isTrashed()
    {
        return (bool) !empty($this->values[$this->softDeleteFieldName]);
    }
}
