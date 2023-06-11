<?php

namespace SigmaPHP\DB\ORM;

use SigmaPHP\DB\Traits\SoftDelete;
use SigmaPHP\DB\Traits\DbOperations;
use Doctrine\Inflector\InflectorFactory;
use SigmaPHP\DB\QueryBuilders\QueryBuilder;
use SigmaPHP\DB\Interfaces\ORM\ModelInterface;

/**
 * Model Class
 */
class Model implements ModelInterface
{
    use DbOperations {
        DbOperations::delete as remove;
    }

    /**
     * @var \PDO $dbConnection
     */
    private $dbConnection;

    /**
     * @var string $dbName
     */
    private $dbName;

    /**
     * @var QueryBuilder $queryBuilder
     */
    private $queryBuilder;

    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var string $primary
     */
    protected $primary;

    /**
     * @var array $fields
     */
    protected $fields;

    /**
     * @var array $values
     */
    protected $values;

    /**
     * @var bool $isNew
     */
    protected $isNew;

    /**
     * @var string $softDeleteFieldName
     */
    protected $softDeleteFieldName;

    /**
     * @var bool $fetchTrashed
     * this flag will allow trashed models to 
     * be returned with queries results 
     */
    protected $fetchTrashed;

    /**
     * @var bool $fetchTrashedWithQuery
     * this flag is temporary and will be reset
     * after each query
     */
    protected $fetchTrashedWithQuery;

    /**
     * Model Constructor
     */
    public function __construct(
        $dbConnection = null,
        $dbName = '',
        $values = [],
        $isNew = true
    ) {
        $this->dbConnection = $dbConnection;
        $this->dbName = $dbName;
        $this->isNew = $isNew;

        $this->queryBuilder = new QueryBuilder($this->dbConnection);

        // set table name if it wasn't provided
        if (empty($this->table)) {
            $this->table = $this->createTableName(get_called_class());
        }

        // check if table exists
        if (!$this->tableExists($this->dbName, $this->table)) {
            throw new \Exception(
                "Error : table {$this->table} doesn't exist"
            );
        }

        // set primary key
        if (empty($this->primary)) {
            $this->primary = 'id';
        }

        // set soft delete field name
        if (empty($this->softDeleteFieldName)) {
            $this->softDeleteFieldName = 'deleted_at';
        }
        
        // set fetch trashed models flag
        if (empty($this->fetchTrashed) && $this->fetchTrashed !== true) {
            $this->fetchTrashed = false;
        }

        // set fetch trashed models with query flag
        if (empty($this->fetchTrashedWithQuery) && 
            $this->fetchTrashedWithQuery !== true) {
            $this->fetchTrashedWithQuery = false;
        }

        // fetch fields
        if (empty($this->fields)) {
            $this->fields = $this->fetchTableFields($this->dbName);
        }

        // set fields values
        if (empty($this->values)) {
            foreach ($this->fields as $field) {
                if ($field == $this->primary) {
                    if (!isset($values[$field]) || empty($values[$field])) {
                        $this->isNew = true;
                        $this->values[$field] = null;
                        continue;
                    } else {
                        $this->isNew = false;
                    }
                }

                $this->values[$field] = (isset($values[$field])) ?
                $values[$field] : null;
            }
        }
    }

    /**
     * Create table name.
     *
     * @param string $className
     * @return string
     */
    protected function createTableName($className)
    {
        $tableName = substr(
            $className,
            (-1 * (strlen($className) - strrpos($className, '\\')))
        );

        $inflector = InflectorFactory::create()->build();
        return $inflector->pluralize($inflector->tableize(
            str_replace('\\', '', $tableName)
        ));
    }

    /**
     * Fetch table fields.
     *
     * @return array
     */
    protected function fetchTableFields()
    {
        $tableFields = $this->fetch("
            SELECT
                GROUP_CONCAT(
                    COLUMN_NAME ORDER BY ORDINAL_POSITION ASC
                ) AS FIELDS
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = '{$this->dbName}'
            AND
                TABLE_NAME = '{$this->table}'
        ")['FIELDS'];

        return array_values(explode(',', $tableFields));
    }

    /**
     * Use the query builder on the model.
     *
     * @return object
     */
    protected function query()
    {
        return $this->queryBuilder->table($this->table);
    }
    
    /**
     * Convert array to model instance.
     *
     * @param array $modelData
     * @return object
     */
    protected function createModelInstance($modelData)
    {
        return new (get_called_class())(
            $this->dbConnection,
            $this->dbName,
            $modelData
        );
    }
    
    /**
     * Check if model is using soft delete.
     *
     * @return bool
     */
    protected function isUsingSoftDelete()
    {
        return in_array(SoftDelete::class, array_values(
            class_uses(get_called_class())
        ));
    }

    /**
     * Set field value.
     *
     * @param string $field
     * @param string $value
     * @return void
     */
    final public function __set($field, $value)
    {
        if (!in_array($field, $this->fields)) {
            throw new \Exception("Unknown field $field");
        }

        $this->values[$field] = $value;
    }

    /**
     * Set field value.
     *
     * @param string $field
     * @param string $value
     * @return void
     */
    final public function __get($field)
    {
        if (!in_array($field, $this->fields)) {
            throw new \Exception("Unknown field $field");
        }

        return $this->values[$field];
    }

    /**
     * Get table name.
     *
     * @return string
     */
    final public function getTableName()
    {
        return $this->table;
    }

    /**
     * Create model from an array of data. 
     * This method doesn't only create new instance of the model 
     * but save it also.
     *
     * @param array $modelData
     * @return object
     */
    final public function create($modelData)
    {
        $model = $this->createModelInstance($modelData);
        $model->save();

        return $model;
    }

    /**
     * Fetch all models.
     *
     * @return array
     */
    final public function all()
    {
        $models = [];
        $query = $this->query();

        if ($this->isUsingSoftDelete() && 
            $this->fetchTrashed == false &&
            $this->fetchTrashedWithQuery == false
        ) {
            $query->where($this->softDeleteFieldName, 'IS', 'NULL');
        }

        foreach ($query->getAll() as $modelData) {
            $models[] = $this->createModelInstance($modelData);
        }

        // disable query return trash flag
        $this->fetchTrashedWithQuery = false;

        return $models;
    }

    /**
     * Count all models.
     *
     * @return int
     */
    final public function count()
    {
        $query = $this->query()
            ->select(['count(*) as rows_count']);

        if ($this->isUsingSoftDelete() && 
            $this->fetchTrashed == false &&
            $this->fetchTrashedWithQuery == false
        ) {
            $query->where($this->softDeleteFieldName, 'IS', 'NULL');
        }

        // disable query return trash flag
        $this->fetchTrashedWithQuery = false;

        return $query->get()['rows_count'];
    }

    /**
     * Find model by primary key.
     *
     * @param mixed $primaryValue
     * @return Model
     */
    final public function find($primaryValue)
    {
        $query = $this->query()
            ->where($this->primary, '=', $primaryValue);

        if ($this->isUsingSoftDelete() && 
            $this->fetchTrashed == false &&
            $this->fetchTrashedWithQuery == false
        ) {
            $query->andWhere($this->softDeleteFieldName, 'IS', 'NULL');
        }

        // disable query return trash flag
        $this->fetchTrashedWithQuery = false;

        return $this->createModelInstance(
            $query->get()
        );
    }

    /**
     * Find model by field's value.
     *
     * @param string $field
     * @param int $value
     * @return array
     */
    final public function findBy($field, $value)
    {
        $query = $this->query()
            ->where($field, '=', $value);

        if ($this->isUsingSoftDelete() && 
            $this->fetchTrashed == false &&
            $this->fetchTrashedWithQuery == false
        ) {
            $query->andWhere($this->softDeleteFieldName, 'IS', 'NULL');
        }

        // disable query return trash flag
        $this->fetchTrashedWithQuery = false;

        return $this->createModelInstance(
            $query->get()
        );
    }

    /**
     * Save model , by updating current model
     * or creating new one.
     *
     * @return mixed
     */
    final public function save()
    {
        $values = [];

        foreach ($this->values as $field => $value) {
            if (($field == $this->primary) || empty($value)) {
                continue;
            }

            $values[$field] = $value;
        }

        if ($this->isNew) {
            $this->insert($this->table, [$values]);
            $this->values[$this->primary] = 
                $this->getLatestInsertedRowPrimaryKeyValue();
            $this->isNew = false;
        } else {
            $this->update(
                $this->table,
                $values,
                // this might condition if we have condition !!
                [$this->primary => $this->values[$this->primary]]
            );
        }
    }

    /**
     * Delete model.
     *
     * @return void
     */
    final public function delete()
    {
        if ($this->isUsingSoftDelete()) {
            $this->trash();
        } else {
            $this->remove(
                $this->table,
                [$this->primary => $this->values[$this->primary]]
            );
            
            // remove the PK value from the model
            $this->values[$this->primary] = null;
        }

        // mark the model as new
        $this->isNew = true;
    }

    /**
     * Get one/many models in another table
     * related to this model.
     *
     * @param Model $model
     * @param string $foreignKey
     * @param string $localKey
     * @return array
     */
    final public function hasRelation($model, $foreignKey, $localKey)
    {
        $relationModel = new ($model)(
            $this->dbConnection,
            $this->dbName
        );

        $relatedModelsData = $this->query()
            ->select([$relationModel->getTableName() . '.*'])
            ->join(
                $relationModel->getTableName(),
                $relationModel->getTableName() . '.' . $foreignKey,
                '=',
                $this->table . '.' . $localKey,
            )
            ->where(
                $this->table . '.' . $this->primary,
                '=',
                $this->values[$this->primary] ?? null
            )
            ->getAll();

        $models = [];

        foreach ($relatedModelsData as $relatedModelData) {
            $models[] = $relationModel->create($relatedModelData, false);
        }

        return $models;
    }
}
