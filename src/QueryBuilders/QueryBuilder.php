<?php

namespace SigmaPHP\DB\QueryBuilders;

use SigmaPHP\DB\Interfaces\QueryBuilders\QueryBuilderInterface;
use SigmaPHP\DB\Traits\DbMethods;

/**
 * QueryBuilder Class
 */
class QueryBuilder implements QueryBuilderInterface
{
    use DbMethods;

    /**
     * @var \PDO $dbConnection
     */
    private $dbConnection;

    /**
     * @var string $statement
     */
    private $statement;

    /**
     * QueryBuilder Constructor
     */
    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Concatenate array of strings into one comma separated line.
     * 
     * @param array $table
     * @return string
     */
    private function concatenateStrings($strings)
    {
        return rtrim(implode(",", $strings), ",");
    }

    /**
     * Add quotes for string values. 
     * 
     * @param string $value
     * @return string
     */
    private function addQuotes($value)
    {
        // escape if the input null , contains mysql built-in functions
        // like date , now ...etc or it's a numeric value
        return ((strpos($value, '(') !== false) || is_numeric($value) ||
            $value == 'null') ? $value : "'$value'";
    }

    /**
     * Select the table which will be used to perform the query.
     * 
     * @param string $table
     * @return object
     */
    final public function table($table)
    {
        $this->statement = "SELECT * FROM $table";
        return $this;
    }
    
    /**
     * Choose fields that will be returned by the query if not
     * set , all fields in the table will be returned (using '*').
     * 
     * @param array $fields
     * @return object
     */
    final public function select($fields)
    {
        if (empty($fields)) {
            return;
        }

        if (!is_array($fields)) {
            throw new \InvalidArgumentException(
                "Fields should be of type array"
            );
        }

        $this->statement = str_replace(
            "*",
            $this->concatenateStrings($fields),
            $this->statement
        );
        
        return $this;
    }

    /**
     * Default where statement. Please note we can't use 
     * multiple where statement on the same field. we 
     * should use andWhere / orWhere instead.
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function where($column, $operator, $value)
    {
        $value = $this->addQuotes($value);
        $this->statement .= " WHERE $column $operator $value ";
        return $this;
    }

    /**
     * And where statement. 
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function andWhere($column, $operator, $value)
    {
        $value = $this->addQuotes($value);
        $this->statement .= " AND $column $operator $value ";
        return $this;
    }

    /**
     * Or where statement.
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function orWhere($column, $operator, $value)
    {
        $value = $this->addQuotes($value);
        $this->statement .= " OR $column $operator $value ";
        return $this;
    }
    
    /**
     * Where the column's value within a range.
     * 
     * @param string $column
     * @param string $value1
     * @param string $value2
     * @return object
     */
    final public function whereBetween($column, $value1, $value2)
    {
        $value1 = $this->addQuotes($value1);
        $value2 = $this->addQuotes($value2);

        $this->statement .= " WHERE $column BETWEEN $value1 AND $value2 ";
        return $this;
    }

    /**
     * Where the column's value will be selected 
     * from a group of values.
     * 
     * @param string $column
     * @param array $values
     * @return object
     */
    final public function whereIn($column, $values)
    {
        $valuesStr = '';
        foreach ($values as $value) {
            $valuesStr .= $this->addQuotes($value) . ",";
        }

        $valuesStr = rtrim($valuesStr, ",");

        $this->statement .= " WHERE $column IN ($valuesStr) ";
        return $this;
    }

    /**
     * The equivalent to where statement but with aggregates
     * and sure you can use aggregates methods in $column.
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return object
     */
    final public function having($column, $operator, $value)
    {
        $value = $this->addQuotes($value);
        $this->statement .= " HAVING $column $operator $value ";
        return $this;
    }
    
    /**
     * Remove duplicates in result.
     * 
     * @return object
     */
    final public function distinct()
    {
        $this->statement = str_replace(
            "SELECT",
            "SELECT DISTINCT",
            $this->statement
        );
        
        return $this;
    }

    /**
     * Limit the number of rows that will be returned by the query
     * and also i can add an offset to start from.
     * 
     * @param int $count
     * @param int $offset
     * @return object
     */
    final public function limit($count, $offset = '')
    {
        $offsetStatement = '';
        if (!empty($offset) && is_numeric($offset)) {
            $offsetStatement = "OFFSET $offset";
        }

        $this->statement .= " LIMIT $count $offsetStatement ";
        return $this;
    }
    
    /**
     * Order the result rows based on some columns.
     * 
     * @param array $columns
     * @return object
     */
    final public function orderBy($columns)
    {
        $columns = $this->concatenateStrings($columns);
        $this->statement .= " ORDER BY $columns ";
        return $this;
    }

    /**
     * Group the result rows based on some columns.
     * 
     * @param array $columns
     * @return object
     */
    final public function groupBy($columns)
    {
        $columns = $this->concatenateStrings($columns);
        $this->statement .= " GROUP BY $columns ";
        return $this;
    }
    
    /**
     * Combine two group of results together optionally
     * we can allow duplicate values. Please note that
     * the selected columns should be the same in both
     * queries in order to get valid result.
     * 
     * @param QueryBuilder $query the second query to combine with 
     * @param bool $all a flag to activate distinct values (by default false)
     * @return object
     */
    final public function union($query, $all)
    {
        $unionMethod = $all ? "UNION ALL" : "UNION";
        $queryAsStr = rtrim($query->print(), ';');

        $this->statement = "({$this->statement}) $unionMethod ($queryAsStr)";
        return $this;
    }

    /**
     * Join tables ; support 'inner', 'right' and 'left' joins
     * the 'inner' join is the default if no type was set. 
     * Please note we use dot notation to reference columns for
     * example : 'table.column'
     * 
     * @param string $table
     * @param string $column1
     * @param string $operator
     * @param string $column2
     * @param string $type
     * @return object
     */
    final public function join(
        $table,
        $column1,
        $operator,
        $column2,
        $type = 'inner'
    ) {
        $type = strtoupper($type);
        $this->statement .= " $type JOIN $table ON $column1 $operator $column2";
        return $this;
    }

    /**
     * Fetch single row.
     * 
     * @return array
     */
    final public function get()
    {
        return $this->fetch(rtrim($this->statement) . ";");
    }
    
    /**
     * Fetch all rows.
     * 
     * @return array
     */
    final public function getAll()
    {
        return $this->fetchAll(rtrim($this->statement) . ";");
    }
    
    /**
     * Print the query without execution, we can use 
     * this method for debugging / logging.
     * 
     * @return string
     */
    final public function print()
    {
        return rtrim($this->statement) . ";";
    }
}