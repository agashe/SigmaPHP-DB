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
     * Use the query builder on the model.
     * 
     * @return object
     */
    protected function query()
    {
        return $this->queryBuilder->table($this->table);
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
     * Create model from an array of data.
     * 
     * @param array $modelData
     * @param bool $isNew
     * @return object
     */
    final public function create($modelData, $isNew = true)
    {        
        return new (get_called_class())(
            $this->dbConnection,
            $this->dbName,
            $modelData
        );
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
            $models[] = $this->create($modelData, false);
        }

        return $models;
    }

    /**
     * Count all models.
     *
     * @return int
     */
    final public function count()
    {
        return $this->query()
            ->select(['count(*) as rows_count'])
            ->get()['rows_count'];
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
        , false);
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
        , false);
    }
    
    /**
     * Save model , by updating current model 
     * or creating new one.
     *
     * @return mixed
     */
    final public function save()
    {
        if ($this->isNew) {
            $fields = implode(',', array_keys($this->values));
            $values = array_values($this->values);
            
            $valuesStr = '';
            foreach ($values as $value) {
                $valuesStr .= (is_string($value) ? "'$value'" : $value ). ','; 
            }

            $valuesStr = rtrim($valuesStr, ',');
            
            $this->execute("
                INSERT INTO {$this->table} ($fields) VALUES ($valuesStr);
            ");
        } else {
            $updateStatement = "UPDATE {$this->table} SET ";

            foreach ($this->values as $col => $val) {
                $val = is_string($val) ? "'$val'" : $val; 
                $updateStatement .= "$col = $val,";
            }

            $updateStatement = rtrim($updateStatement, ',');

            if (isset($search) && !empty($search)) {
                $field = implode('', array_keys($search));
                $value = implode('', array_values($search));
                $value = is_string($value) ? "'$value'" : $value;
                $updateStatement .= " WHERE $field = $value;";
            }

            $this->execute($updateStatement);
        }
    }
    
    /**
     * Delete model.
     *
     * @return bool
     */
    final public function delete()
    {
        $condition = 1;
        
        if (isset($search) && !empty($search)) {
            $field = implode('', array_keys($search));
            $value = implode('', array_values($search));
            $value = is_string($value) ? "'$value'" : $value;
            $condition = "$field = $value";
        }

        $this->execute("
            DELETE FROM {$this->table} WHERE $condition;
        ");
    }
}