<?php

namespace SigmaPHP\DB\Interfaces\Migrations;

/**
 * Migration Interface
 */
interface MigrationInterface
{
    /**
     * The migration's instructions , this method will be called by the 
     * 'migrate' command in the CLI tool. Also This method will be overridden
     * by children classes (migrations).
     * 
     * @return void
     */
    public function up();

    /**
     * The opposite of up() method and it reverses the specified migration 
     * changes , and rollback the database to an older status. 
     * 
     * This method will be overridden by children classes (migrations). And
     * will be called by the 'rollback' command in the CLI tool.
     * 
     * @return void
     */
    public function down();

    /**
     * Execute SQL statements.
     *
     * @param string $statement
     * @return void
     */
    public function execute($statement);

    /**
     * Create new table schema.
     * 
     * @param string $name
     * @param array $fields
     * @param array $options
     * @return void
     */
    public function createTable($name, $fields, $options);
    
    /**
     * Update table schema.
     * 
     * @param string $name
     * @param array $options
     * @return void
     */
    public function updateTable($name, $options);

    /**
     * Rename table schema.
     * 
     * @param string $currentName
     * @param string $newName
     * @return void
     */
    public function renameTable($currentName, $newName);

    /**
     * Check if table exists.
     *
     * @param string $name
     * @return bool
     */
    public function checkTable($name);

    /**
     * Change table primary key.
     *
     * @param string $table
     * @param string $oldPrimaryKey
     * @param string $newPrimaryKey
     * @return void
     */
    public function changeTablePrimaryKey(
        $table,
        $oldPrimaryKey,
        $newPrimaryKey
    );

    /**
     * Drop table schema.
     * 
     * @param string $name
     * @return void
     */
    public function dropTable($name);

    /**
     * Add column.
     * 
     * @param string $table
     * @param string $name
     * @param array $properties
     * @return void
     */
    public function addColumn($table, $name, $properties);

    /**
     * Update column.
     * 
     * @param string $table
     * @param string $name
     * @param array $properties
     * @return void
     */
    public function updateColumn($table, $name, $properties);

    /**
     * Rename column.
     * 
     * @param string $table
     * @param string $currentName
     * @param string $newName
     * @return void
     */
    public function renameColumn($table, $currentName, $newName);

    /**
     * Check if column exists.
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    public function checkColumn($table, $name);

    /**
     * Drop column.
     * 
     * @param string $table
     * @param string $name
     * @return void
     */
    public function dropColumn($table, $name);

    /**
     * Add index.
     * 
     * @param string $table
     * @param string $name
     * @param array $columns
     * @param string $type normal|unique|fulltext
     * @param array $order
     * @return void
     */
    public function addIndex(
        $table,
        $name,
        $columns,
        $type,
        $order,
    );

    /**
     * Check if index exists.
     * 
     * @param string $table
     * @param string $name
     * @return void
     */
    public function checkIndex($table, $name);

    /**
     * Drop index.
     * 
     * @param string $table
     * @param string $name
     * @return void
     */
    public function dropIndex($table, $name);

    /**
     * Add foreign key.
     * 
     * @param string $constraint
     * @param string $parentTable
     * @param array $localIds
     * @param string $referenceTable
     * @param string $foreignIds
     * @param array $options
     * @return void
     */
    public function addForeignKey(
        $constraint,
        $parentTable,
        $localIds,
        $referenceTable,
        $foreignIds,    
        $options
    );

    /**
     * Check if foreign key exists.
     * 
     * @param string $table
     * @param string $constraint
     * @return void
     */
    public function checkForeignKey($table, $constraint);

    /**
     * Drop foreign key.
     * 
     * @param string $table
     * @param string $constraint
     * @return void
     */
    public function dropForeignKey($table, $constraint);
}