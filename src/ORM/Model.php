<?php

namespace SigmaPHP\DB\ORM;

use SigmaPHP\DB\Traits\SoftDelete;
use SigmaPHP\DB\Traits\DbOperations;
use Doctrine\Inflector\InflectorFactory;
use SigmaPHP\DB\QueryBuilders\QueryBuilder;
use SigmaPHP\DB\Exceptions\NotFoundException;
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
     * be always returned with queries results
     */
    protected $fetchTrashed;

    /**
     * @var bool $fetchOnlyTrashed
     * this flag is temporary to return only soft
     * deleted models in the query results and
     * will be reset after each query
     */
    protected $fetchOnlyTrashed;

    /**
     * @var bool $fetchTrashedWithQuery
     * this flag is temporary to return include soft
     * deleted models in one query results and will
     * be reset after each query
     */
    protected $fetchTrashedWithQuery;

    /**
     * @var array $conditions
     * an array by all where conditions implemented
     * on the models
     */
    protected $conditions;

    /**
     * @var array $relations
     * an array by all relations details of the model
     * like table name, foreign key and local key
     */
    protected $relations;

    /**
     * @var bool $uuid
     * to control using UUID as primary keys in models
     */
    protected $uuid;

    /**
     * Model Constructor
     *
     * @param \PDO $dbConnection
     * @param string $dbName
     * @param array $values
     * @param bool $isNew
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

        // set table name if it wasn't provided
        if (empty($this->table)) {
            $this->table = $this->createTableName(get_called_class());
        }

        // check if table exists
        if (!$this->tableExists($this->dbName, $this->table)) {
            throw new NotFoundException(
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

        // set conditions
        if (empty($this->conditions)) {
            $this->conditions = [];
        }

        // set fetch trashed models flag
        if (empty($this->fetchTrashed) && $this->fetchTrashed !== true) {
            $this->fetchTrashed = false;
        }

        // set fetch only trashed models with query flag
        if (empty($this->fetchOnlyTrashed) &&
            $this->fetchOnlyTrashed !== true) {
            $this->fetchOnlyTrashed = false;
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
     * Create new instance of query builder to use with the model.
     *
     * @return QueryBuilder
     */
    protected function query()
    {
        $queryBuilder = new QueryBuilder($this->dbConnection);
        return $queryBuilder->table($this->table);
    }

    /**
     * Convert array to model instance.
     *
     * @param array $modelData
     * @return Model
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
     * Generate a new UUID to be used as PK for model.
     *
     * @return string
     */
    protected function generateUUID()
    {
        return $this->fetchColumn("SELECT UUID()")[0];
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
     * Set a new condition on model's query.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @param string $type
     * @return void
     */
    protected function setCondition(
        $field,
        $operator,
        $value,
        $type = '',
        $relation = '',
    ) {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'type' => (!empty($type)) ? $type : (
                (empty($this->conditions)) ? 'default' : 'and'
            ),
            'relation' => $relation,
        ];
    }

    /**
     * Process model's query conditions.
     *
     * @param QueryBuilder $query
     * @return void
     */
    protected function processQueryConditions(&$query)
    {
        // check soft delete condition
        if ($this->isUsingSoftDelete()) {
            if ($this->fetchTrashed == false &&
                $this->fetchTrashedWithQuery == false &&
                $this->fetchOnlyTrashed == false
            ) {
                    $this->setCondition(
                    $this->softDeleteFieldName, 'IS', 'NULL'
                );
            }
            else if ($this->fetchOnlyTrashed == true) {
                $this->setCondition(
                    $this->softDeleteFieldName, 'IS NOT', 'NULL'
                );
            }
        }

        // apply the conditions
        foreach ($this->conditions as $condition) {
            switch ($condition['type']) {
                case 'default':
                    $query->where(
                        $condition['field'],
                        $condition['operator'],
                        $condition['value']
                    );
                    break;
                case 'and':
                    $query = $query->andWhere(
                        $condition['field'],
                        $condition['operator'],
                        $condition['value']
                    );
                    break;
                case 'or':
                    $query = $query->orWhere(
                        $condition['field'],
                        $condition['operator'],
                        $condition['value']
                    );
                    break;
                case 'has':
                    if (
                        method_exists(
                            get_called_class(),
                            $condition['relation']
                        )
                    ) {
                        // boot the relation model , to fetch its details
                        $loadRelationModel = $this->{$condition['relation']}();

                        $relation = $this->relations[
                            $condition['relation']
                        ];

                        $query->join(
                            $relation['table'],
                            $relation['table'] . '.' .
                                $relation['foreign_key'],
                            '=',
                            $this->getTableName() . '.' .
                                $relation['local_key'],
                        );

                        if (
                            !empty($condition['field']) &&
                            !empty($condition['operator']) &&
                            !empty($condition['value'])
                        ) {
                            $query->where(
                                $relation['table'] . '.' . $condition['field'],
                                $condition['operator'],
                                $condition['value']
                            );
                        }

                        $query->distinct();
                    } else {
                        throw new NotFoundException(
                            "Relation {$condition['relation']} not found in
                             model " . get_called_class()
                        );
                    }
                    break;
            }
        }

        // clear all conditions
        $this->conditions = [];

        // disable query return trash flag
        $this->fetchTrashedWithQuery = false;
        $this->fetchOnlyTrashed = false;
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
            throw new NotFoundException("Unknown field $field");
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
            throw new NotFoundException("Unknown field $field");
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
     * @return array<static>
     */
    final public function all()
    {
        $models = [];
        $query = $this->query()
            ->select([$this->getTableName() . '.*']);

        $this->processQueryConditions($query);

        $result = $query->getAll();

        if (!empty($result)) {
            foreach ($result as $modelData) {
                $models[] = $this->createModelInstance($modelData);
            }
        }

        return $models;
    }

    /**
     * Fetch first model.
     *
     * @return static|null
     */
    final public function first()
    {
        $model = null;
        $query = $this->query()
            ->select([$this->getTableName() . '.*']);

        $this->processQueryConditions($query);

        $result = $query->get();

        if (!empty($result)) {
            $model = $this->createModelInstance(
                $result
            );
        }

        return $model;
    }

    /**
     * Count all models.
     *
     * @return int
     */
    final public function count()
    {
        $query = $this->query()->select([
            'count(distinct ' .
                $this->getTableName() . '.' . $this->primary .
            ') as rows_count'
        ]);

        $this->processQueryConditions($query);

        return $query->get()['rows_count'] ?: 0;
    }

    /**
     * Find model by primary key.
     *
     * @param mixed $primaryValue
     * @return static|null
     */
    final public function find($primaryValue)
    {
        $model = null;
        $query = $this->query()
            ->select([$this->getTableName() . '.*']);

        $this->setCondition(
            $this->primary, '=', $primaryValue
        );

        $this->processQueryConditions($query);

        $result = $query->get();

        if (!empty($result)) {
            $model = $this->createModelInstance(
                $result
            );
        }

        return $model;
    }

    /**
     * Find model by field's value.
     *
     * @param string $field
     * @param string $value
     * @return static|null
     */
    final public function findBy($field, $value)
    {
        $model = null;
        $query = $this->query()
            ->select([$this->getTableName() . '.*']);

        $this->setCondition(
            $field, '=', $value
        );

        $this->processQueryConditions($query);

        $result = $query->get();

        if (!empty($result)) {
            $model = $this->createModelInstance(
                $result
            );
        }

        return $model;
    }

    /**
     * Where query on models.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function where($field, $operator, $value)
    {
        $this->setCondition(
            $field, $operator, $value
        );

        return $this;
    }

    /**
     * And where query on models.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function andWhere($field, $operator, $value)
    {
        $this->setCondition(
            $field, $operator, $value, 'and'
        );

        return $this;
    }

    /**
     * Or where query on models.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function orWhere($field, $operator, $value)
    {
        $this->setCondition(
            $field, $operator, $value, 'or'
        );

        return $this;
    }

    /**
     * Where condition on model's relations.
     *
     * @param string $relation
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function whereHas(
        $relation,
        $field = '',
        $operator = '',
        $value = ''
    ) {
        $this->setCondition(
            $field, $operator, $value, 'has', $relation
        );

        return $this;
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
            if (($field == $this->primary) || is_null($value)) {
                continue;
            }

            $values[$field] = $value;
        }

        // in case of PK of type UUID , we generate a new UUID , and save it
        if ($this->uuid) {
            $this->values[$this->primary] = $this->generateUUID();
        }

        if ($this->isNew) {
            $this->insert($this->table, [$values]);

            // here we skip this part for UUID PKs , since LAST_INSERT_ID does
            // not work with non-numerical PKs
            if (!$this->uuid) {
                $this->values[$this->primary] =
                    $this->getLatestInsertedRowPrimaryKeyValue();
            }

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
     * @param bool $forceHardDelete
     * @return void
     */
    final public function delete($forceHardDelete = false)
    {
        if ($this->isUsingSoftDelete() && !$forceHardDelete) {
            // we disable the "Undefined method ..." warning , since this method
            // will be defined in the SoftDelete Trait

            /** @disregard P1013 */
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
     * @return array<static>
     */
    final public function hasRelation($model, $foreignKey, $localKey)
    {
        $relationModel = new ($model)(
            $this->dbConnection,
            $this->dbName
        );

        // save the relation details
        $relationName = debug_backtrace()[1]['function'];

        if (!isset($this->relations[$relationName])) {
            $this->relations[$relationName] = [
                'table' => $relationModel->getTableName(),
                'foreign_key' => $foreignKey,
                'local_key' => $localKey,
            ];
        }

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
