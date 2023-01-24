<?php

namespace SigmaPHP\DB\ORM;

use SigmaPHP\DB\Traits\DbMethods;
use Doctrine\Inflector\InflectorFactory;
use SigmaPHP\DB\QueryBuilders\QueryBuilder;
use SigmaPHP\DB\Interfaces\ORM\ModelInterface;

/**
 * Model Class
 */
class Model implements ModelInterface
{
    use DbMethods;

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
     * Model Constructor
     */
    public function __construct($dbConnection, $dbName)
    {
        $this->dbConnection = $dbConnection;
        $this->queryBuilder = new QueryBuilder($this->dbConnection);
        $this->dbName = $dbName;

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
        
        // fetch fields
        if (empty($this->fields)) {
            $this->fields = $this->fetchTableFields($this->dbName);
        }

        // set fields values
        if (empty($this->values)) {
            foreach ($this->fields as $field) {
                $this->values[$field] = null;
            }
        }

        // set isNew true
        $this->isNew = true;
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
        return $inflector->pluralize($inflector->tableize($tableName));
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
     * Use the query builder on the model.
     * 
     * @return object
     */
    final public function query()
    {
        return $this->queryBuilder->table($this->table);
    }

    /**
     * Create model from an array of data.
     * 
     * @param array $modelData
     * @return object
     */
    final public function create($modelData)
    {
        $modelClass = get_called_class();
        $newModel = new $modelClass($this->dbConnection, $this->dbName);

        foreach ($modelData as $key => $val) {
            $newModel->$key = $val;
        }

        return $newModel;
    }

    /**
     * Fetch all models.
     *
     * @return array
     */
    final public function all()
    {
        $models = [];
        
        foreach ($this->query()->getAll() as $modelData) {
            $models[] = $this->create($modelData);
        }

        return $models;
    }

    /**
     * Find model by primary key.
     *
     * @param mixed $primaryValue
     * @return Model
     */
    final public function find($primaryValue)
    {
        return $this->create(
            $this->query()
                ->where($this->primary, '=', $primaryValue)
                ->get()
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
        return $this->create(
            $this->query()
                ->where($field, '=', $value)
                ->get()
        );
    }
    
    /**
     * Save model , by updating current model 
     * or creating new one.
     *
     * @return array
     */
    final public function save()
    {
        
    }
    
    /**
     * Delete model.
     *
     * @return array
     */
    final public function delete()
    {
        
    }
}