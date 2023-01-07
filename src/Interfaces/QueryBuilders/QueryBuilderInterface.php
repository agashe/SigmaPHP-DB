<?php

namespace SigmaPHP\DB\Interfaces\QueryBuilders;

/**
 * QueryBuilder Interface
 */
interface QueryBuilderInterface
{
    /**
     * Select the table which will be used to perform the query.
     * 
     * @param string $table
     * @return void
     */
    public function table($table);
   
    /**
     * Choose fields that will be returned by the query if not
     * set , all fields in the table will be returned (using '*').
     * 
     * @param array $fields
     * @return void
     */
    public function select($fields);

    /**
     * Default where statement. Please note we can't use 
     * multiple where statement on the same field. we 
     * should use andWhere / orWhere instead.
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return void
     */
    public function where($column, $operator, $value);

    /**
     * And where statement. 
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return void
     */
    public function andWhere($column, $operator, $value);

    /**
     * Or where statement.
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return void
     */
    public function orWhere($column, $operator, $value);
    
    /**
     * Where the column's value within a range.
     * 
     * @param string $column
     * @param string $value1
     * @param string $value2
     * @return void
     */
    public function whereBetween($column, $value1, $value2);

    /**
     * Where the column's value will be selected 
     * from a group of values.
     * 
     * @param string $column
     * @param array $values
     * @return void
     */
    public function whereIn($column, $value1s);
    
    /**
     * Remove duplicates in result.
     * 
     * @return void
     */
    public function distinct();

    /**
     * Count rows in result.
     * 
     * @return void
     */
    public function count();

    /**
     * Get the maximum value in a column.
     * 
     * @param string $column
     * @return void
     */
    public function max($column);
    
    /**
     * Get the minimum value in a column.
     * 
     * @param string $column
     * @return void
     */
    public function min($column);
    
    /**
     * Get the average value in a column.
     * 
     * @param string $column
     * @return void
     */
    public function avg($column);
    
    /**
     * Get the total sum of column.
     * 
     * @param string $column
     * @return void
     */
    public function sum($column);
    
    /**
     * Limit the number of rows that will be returned by the query
     * and also i can add an offset to start from.
     * 
     * @param int $count
     * @param int $offset
     * @return void
     */
    public function limit($count, $offset);
    
    /**
     * Order the result rows based on some columns.
     * 
     * @param array $columns
     * @return void
     */
    public function orderBy($columns);

    /**
     * Group the result rows based on some columns.
     * 
     * @param array $columns
     * @return void
     */
    public function groupBy($columns);
    
    /**
     * Combine two group of results together optionally
     * we can allow duplicate values. Please note that
     * the selected columns should be the same in both
     * queries in order to get valid result.
     * 
     * @param void $query the second query to combine with 
     * @param bool $all a flag to activate distinct values (by default false)
     * @return void
     */
    public function union($query, $all);

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
     * @return void
     */
    public function join(
        $table,
        $column1,
        $operator,
        $column2,
        $type
    );

    /**
     * Fetch single row.
     * 
     * @return array
     */
    public function get();
    
    /**
     * Fetch all rows.
     * 
     * @return array
     */
    public function getAll();
    
    /**
     * Print the query without execution, we can use 
     * this method for debugging / logging.
     * 
     * @return string
     */
    public function print();
}