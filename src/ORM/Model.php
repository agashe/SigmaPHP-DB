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
            (-1 * (strlen($className) - strrpos($className, '\\') - 1))
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
        $tableFields = $this->fetchAll("
        SELECT
            GROUP_CONCAT(COLUMN_NAME) AS FIELDS
        FROM 
            INFORMATION_SCHEMA.COLUMNS
        WHERE 
            TABLE_SCHEMA = '{$this->dbName}'
        AND
            TABLE_NAME = '{$this->table}'
        ")['FIELDS'];

        return array_values($tableFields);
    }

    /**
     * Use the query builder on the model.
     * 
     * @return object
     */
    final public static function query()
    {

    }
}