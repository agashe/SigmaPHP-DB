<?php

namespace SigmaPHP\DB\Migrations;

use SigmaPHP\DB\Exceptions\InvalidArgumentException;
use SigmaPHP\DB\Interfaces\Migrations\MigrationInterface;
use SigmaPHP\DB\Traits\DbMethods;
use SigmaPHP\DB\Traits\HelperMethods;

/**
 * Migration Class
 */
class Migration implements MigrationInterface
{
    use DbMethods, HelperMethods;

    /**
     * @var string default field name for soft delete
     */
    protected const DEFAULT_SOFT_DELETE_FIELD_NAME = 'deleted_at';

    /**
     * @var string default field name for created at timestamps
     */
    protected const DEFAULT_TIMESTAMPS_CREATED_AT_FIELD_NAME = 'created_at';

    /**
     * @var string default field name for updated at timestamps
     */
    protected const DEFAULT_TIMESTAMPS_UPDATED_AT_FIELD_NAME = 'updated_at';

    /**
     * @var \PDO $dbConnection
     */
    private $dbConnection;

    /**
     * @var string $dbName
     */
    private $dbName;

    /**
     * Migration Constructor
     *
     * @param \PDO $dbConnection
     * @param string $dbName
     */
    public function __construct($dbConnection, $dbName)
    {
        $this->dbConnection = $dbConnection;
        $this->dbName = $dbName;
    }

    /**
     * Convert field's properties array to SQL statement.
     *
     * @param array $properties
     * @return string
     */
    private function convertFieldToSql($properties)
    {
        // validation
        if (!isset($properties['name']) || empty($properties['name'])) {
            throw new InvalidArgumentException(
                "Error : Field name can't be empty"
            );
        }

        if (!isset($properties['type']) || empty($properties['type'])) {
            throw new InvalidArgumentException(
                "Error : Field type can't be empty"
            );
        }

        if (in_array(strtolower($properties['type']), [
            'enum',
            'set'
            ]) &&
            (
                !isset($properties['values']) ||
                empty($properties['values']) ||
                !is_array($properties['values'])
            )
        ) {
            throw new InvalidArgumentException(
                "Error : Values property is mandatory with ENUM data type"
            );
        }

        // set default size for string data types
        if (in_array(strtolower($properties['type']), [
                'char',
                'varchar',
                'binary',
                'varbinary',
                'blob'
            ]) &&
            (
                !isset($properties['size']) ||
                empty($properties['size']) ||
                !is_numeric($properties['size'])
            )
        ) {
            $properties['size'] = 255;
        }

        // start the field SQL statement by set the name
        $fieldString = "{$properties['name']}";

        // set the field type
        $type = strtoupper($properties['type']);

        // handle the UUID primary key
        if ($type == 'UUID') {
            $type = ' CHAR(36) ';
        }

        $fieldString .= " {$type} ";

        // size option for string/numeric data types
        if (isset($properties['size']) && !empty($properties['size'])) {
            $fieldString .= "({$properties['size']}) ";
        }

        // numeric data types options
        if (isset($properties['precision']) &&
            !empty($properties['precision']) &&
            isset($properties['scale']) &&
            !empty($properties['scale'])
        ) {
            $fieldString .= "({$properties['precision']},
                {$properties['scale']}) ";
        }

        if (isset($properties['unsigned']) &&
            ($properties['unsigned'] === true)) {
            $fieldString .= " UNSIGNED ";
        }

        // date data types options
        if (isset($properties['auto_update']) &&
            ($properties['auto_update'] === true)) {
            $fieldString .=
                " DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP() ";
        }

        // enum and set data types options
        if (isset($properties['values']) && !empty($properties['values'])) {
            $values = $this->concatenateTokens($properties['values'], true);
            $fieldString .= "({$values}) ";
        }

        // general field options (for all data types)
        if (isset($properties['primary']) &&
            ($properties['primary'] === true) &&
            (strtoupper($properties['type']) != 'UUID')
        ) {
            $fieldString .= " AUTO_INCREMENT ";
        }

        if (isset($properties['not_null']) &&
            ($properties['not_null'] === true)) {
            $fieldString .= " NOT NULL ";
        }

        if (isset($properties['default']) && !empty($properties['default'])) {
            $defaultValue = $this->addQuotes($properties['default']);
            $fieldString .= " DEFAULT {$defaultValue} ";
        }

        if (isset($properties['after']) && !empty($properties['after'])) {
            $fieldString .= " AFTER '{$properties['after']}' ";
        }

        if (isset($properties['comment']) && !empty($properties['comment'])) {
            $fieldString .= " COMMENT '{$properties['comment']}' ";
        }

        // handle uuid type default value
        if (strtoupper($properties['type']) == 'UUID') {
            $fieldString .= " DEFAULT (UUID()) ";
        }

        // end the field SQL statement
        return $fieldString;
    }

    /**
     * The migration's instructions , this method will be called by the
     * 'migrate' command in the CLI tool. Also This method will be overridden
     * by children classes (migrations).
     *
     * @return void
     */
    public function up(){}

    /**
     * The opposite of up() method and it reverses the specified migration
     * changes , and rollback the database to an older status.
     *
     * This method will be overridden by children classes (migrations). And
     * will be called by the 'rollback' command in the CLI tool.
     *
     * @return void
     */
    public function down(){}

    /**
     * Create new table schema.
     *
     * @param string $name
     * @param array $fields
     * @param array $options
     * @return void
     */
    final public function createTable($name, $fields, $options = [])
    {
        // start create table statement
        $createTableStatement = "CREATE TABLE $name";

        // add fields
        $tableFields = '';
        $primaryKey  = '';

        foreach ($fields as $field) {
            // set the structure for custom field data types
            // or create regular types
            if ((isset($field['name'])) && ($field['name'] == 'soft_delete')) {
                $tableFields .= $this->convertFieldToSql([
                    'name' => self::DEFAULT_SOFT_DELETE_FIELD_NAME,
                    'type' => 'timestamp'
                ]) . ',';
            }
            else if ((isset($field['name'])) &&
                ($field['name'] == 'timestamps')) {
                $tableFields .= $this->convertFieldToSql([
                    'name' => self::DEFAULT_TIMESTAMPS_CREATED_AT_FIELD_NAME,
                    'type' => 'timestamp',
                    'default' => 'CURRENT_TIMESTAMP()'
                ]) . ',';

                $tableFields .= $this->convertFieldToSql([
                    'name' => self::DEFAULT_TIMESTAMPS_UPDATED_AT_FIELD_NAME,
                    'type' => 'timestamp',
                    'auto_update' => true
                ]) . ',';
            } else {
                $tableFields .= $this->convertFieldToSql($field) . ',';
            }

            // if the primary property was set true for the field
            // or it's of the type UUID , set the field as primary key
            if ((isset($field['primary']) && ($field['primary'] === true)) ||
                (isset($field['type']) &&
                (strtolower($field['type']) == 'uuid'))
            ) {
                $primaryKey = $field['name'];
            }
        }

        // set primary key
        if (!empty($primaryKey)) {
            $tableFields .= " PRIMARY KEY ({$primaryKey}),";
        }

        // remove trailing comma
        $tableFields = rtrim($tableFields, ',');

        $createTableStatement .= " ($tableFields) ";

        // add options
        if (isset($options['engine']) && !empty($options['engine'])) {
            $createTableStatement .= " ENGINE = {$options['engine']} ";
        }

        if (isset($options['collation']) && !empty($options['collation'])) {
            $createTableStatement .=
                " COLLATE = ({$options['collation']}) ";
        }

        if (isset($options['row_format']) && !empty($options['row_format'])) {
            $createTableStatement .=
                " ROW_FORMAT = ({$options['row_format']}) ";
        }

        if (isset($options['comment']) && !empty($options['comment'])) {
            $createTableStatement .=
                " COMMENT '{$options['comment']}' ";
        }

        $createTableStatement .= ";";

        // run create table statement
        $this->execute($createTableStatement);
    }

    /**
     * Update table schema.
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    final public function updateTable($name, $options)
    {
        // start update table statement
        $updateTableStatement = "ALTER TABLE $name";

        // add options
        if (isset($options['engine']) && !empty($options['engine'])) {
            $updateTableStatement .= " ENGINE = {$options['engine']} ";
        }

        if (isset($options['collation']) && !empty($options['collation'])) {
            $updateTableStatement .=
                " COLLATE = ({$options['collation']}) ";
        }

        if (isset($options['row_format']) && !empty($options['row_format'])) {
            $updateTableStatement .=
                " ROW_FORMAT = ({$options['row_format']}) ";
        }

        if (isset($options['comment']) && !empty($options['comment'])) {
            $updateTableStatement .=
                " COMMENT '{$options['comment']}' ";
        }

        $updateTableStatement .= ";";

        // run update table statement
        $this->execute($updateTableStatement);
    }

    /**
     * Rename table schema.
     *
     * @param string $currentName
     * @param string $newName
     * @return void
     */
    final public function renameTable($currentName, $newName)
    {
        $this->execute("
            ALTER TABLE {$currentName} RENAME {$newName};
        ");
    }

    /**
     * Check if table exists.
     *
     * @param string $name
     * @return bool
     */
    final public function checkTable($name)
    {
        return $this->tableExists($this->dbName, $name);
    }

    /**
     * Change table primary key.
     *
     * @param string $table
     * @param string $oldPrimaryKey
     * @param string $newPrimaryKey
     * @return void
     */
    final public function changeTablePrimaryKey(
        $table,
        $oldPrimaryKey,
        $newPrimaryKey
    ) {
        $this->execute("
            ALTER TABLE $table DROP PRIMARY KEY,
            CHANGE $oldPrimaryKey $oldPrimaryKey VARCHAR (255),
            ADD PRIMARY KEY ($newPrimaryKey);
        ");
    }

    /**
     * Drop table schema.
     *
     * @param string $name
     * @return void
     */
    final public function dropTable($name)
    {
        $this->execute("DROP TABLE {$name};");
    }

    /**
     * Add column.
     *
     * @param string $table
     * @param string $name
     * @param array $properties
     * @return void
     */
    final public function addColumn($table, $name, $properties)
    {
        $field = $this->convertFieldToSql([
            'name' => $name,
        ] + $properties);

        $this->execute("ALTER TABLE $table ADD $field;");
    }

    /**
     * Update column.
     *
     * @param string $table
     * @param string $name
     * @param array $properties
     * @return void
     */
    final public function updateColumn($table, $name, $properties)
    {
        $field = $this->convertFieldToSql([
            'name' => $name,
        ] + $properties);

        $this->execute("ALTER TABLE $table MODIFY $field;");
    }

    /**
     * Rename column.
     *
     * @param string $table
     * @param string $currentName
     * @param string $newName
     * @return void
     */
    final public function renameColumn($table, $currentName, $newName)
    {
        $this->execute("
            ALTER TABLE $table RENAME COLUMN $currentName TO $newName;
        ");
    }

    /**
     * Check if column exists.
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    final public function checkColumn($table, $name)
    {
        return (bool) $this->fetch("
            SELECT
                COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = '{$this->dbName}'
            AND
                TABLE_NAME = '{$table}'
            AND
                COLUMN_NAME = '{$name}';
        ");
    }

    /**
     * Drop column.
     *
     * @param string $table
     * @param string $name
     * @return void
     */
    final public function dropColumn($table, $name)
    {
        $this->execute("ALTER TABLE $table DROP COLUMN $name;");
    }

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
    final public function addIndex(
        $table,
        $name,
        $columns,
        $type = 'normal',
        $order = [],
    ) {
        $type = ($type == 'normal') ? '' : strtoupper($type);

        // prepare columns and set order if exists
        $columnsFormatted = '';

        if (isset($order) && !empty($order)) {
            foreach ($columns as $column) {
                if (array_key_exists($column, $order)) {
                    $columnsFormatted .= $column . ' ' .
                        strtoupper($order[$column]) . ',';
                } else {
                    $columnsFormatted .= $column . ',';
                }
            }

            $columnsFormatted = rtrim($columnsFormatted, ',');
        } else {
            $columnsFormatted = implode(',', $columns);
        }

        $this->execute("
            CREATE $type INDEX $name ON $table ($columnsFormatted);
        ");
    }

    /**
     * Check if index exists.
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    final public function checkIndex($table, $name)
    {
        return (bool) $this->fetch("
            SHOW INDEX FROM $table WHERE Key_name='$name';
        ");
    }

    /**
     * Drop index.
     *
     * @param string $table
     * @param string $name
     * @return void
     */
    final public function dropIndex($table, $name)
    {
        $this->execute("
            ALTER TABLE $table DROP INDEX $name;
        ");
    }

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
    final public function addForeignKey(
        $constraint,
        $parentTable,
        $localIds,
        $referenceTable,
        $foreignIds,
        $options
    ) {
        // prepare options
        $foreignKeyOptions = "";

        if (isset($options['on_delete']) && !empty($options['on_delete'])) {
            $foreignKeyOptions .= " ON DELETE {$options['on_delete']} ";
        }

        if (isset($options['on_update']) && !empty($options['on_update'])) {
            $foreignKeyOptions .= " ON UPDATE {$options['on_update']} ";
        }

        $this->execute("
            ALTER TABLE $parentTable
            ADD CONSTRAINT $constraint
            FOREIGN KEY ($localIds)
            REFERENCES {$referenceTable}($foreignIds)
            {$foreignKeyOptions}
        ");
    }

    /**
     * Check if foreign key exists.
     *
     * @param string $table
     * @param string $constraint
     * @return bool
     */
    final public function checkForeignKey($table, $constraint)
    {
        return (bool) $this->fetch("
            SELECT
                CONSTRAINT_NAME
            FROM
                INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE
                TABLE_NAME = '{$table}'
            AND
                CONSTRAINT_NAME = '{$constraint}';
        ");
    }

    /**
     * Drop foreign key.
     *
     * @param string $table
     * @param string $constraint
     * @return void
     */
    final public function dropForeignKey($table, $constraint)
    {
        $this->execute("
            ALTER TABLE $table DROP FOREIGN KEY $constraint;
        ");
    }
}
