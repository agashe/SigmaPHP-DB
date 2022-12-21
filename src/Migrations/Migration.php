<?php

namespace SigmaPHP\DB\Migrations;

use SigmaPHP\DB\Interfaces\Migrations\MigrationInterface;

/**
 * Migration Class
 */
class Migration implements MigrationInterface
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;

    /**
     * Migration Constructor
     */
    public function __construct($dbConfigs)
    {
        $this->dbConfigs = $dbConfigs;
    }

    /**
     * Connect to database.
     * 
     * @return \PDO
     */
    private function connectToDatabase()
    {
        return new \PDO(
            "mysql:host={$this->dbConfigs['host']};
            dbname={$this->dbConfigs['name']}",
            $this->dbConfigs['user'],
            $this->dbConfigs['pass']
        );
    }

    /**
     * Convert field's properties array to SQL statement.
     *
     * @param array $properties
     * @return string
     */
    private function convertFieldToSql($properties)
    {
        $fieldString = "{$properties['name']}";

        $type = strtoupper($properties['type']);
        $fieldString .= " {$type} ";

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
            !empty($properties['auto_update'])) {
            $fieldString .=
                " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ";
        }

        // enum and set data types options
        if (isset($properties['values']) && !empty($properties['values'])) {
            $values = implode(',', $properties['values']);
            $fieldString .= "({$values}) ";
        }
        
        // general field options (for all data types)
        if (isset($properties['size']) && !empty($properties['size'])) {
            $fieldString .= "({$properties['size']}) ";
        }
        
        if (isset($properties['primary']) && 
            ($properties['primary'] === true)) {
            $fieldString .= " AUTO_INCREMENT ";
        }

        if (isset($properties['not_null']) && 
            ($properties['not_null'] === true)) {
            $fieldString .= " NOT NULL ";
        }

        if (isset($properties['default']) && !empty($properties['default'])) {
            $fieldString .= " DEFAULT '{$properties['default']}' ";
        }

        if (isset($properties['after']) && !empty($properties['after'])) {
            $fieldString .= " AFTER '{$properties['after']}' ";
        }

        if (isset($properties['comment']) && !empty($properties['comment'])) {
            $fieldString .= " COMMENT '{$properties['comment']}' ";
        }

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
     * Execute SQL statements.
     *
     * @param string $statement
     * @return void
     */
    final public function execute($statement)
    {
        try {
            $this->connectToDatabase()
                ->prepare($statement)
                ->execute();
        } catch (\Exception $e) {
            echo $e;
        }
    }

    /**
     * Create new table schema.
     * 
     * @param string $name
     * @param array $fields
     * @param array $options
     * @return void
     */
    final public function createTable($name, $fields, $options)
    {
        // start create table statement
        $createTableStatement = "CREATE TABLE $name";

        // add fields        
        $tableFields = '';
        $primaryKey = '';
        
        foreach ($fields as $field) {
            $tableFields .= $this->convertFieldToSql($field) . ',';

            if (isset($field['primary']) && 
                ($field['primary'] === true)) {
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
    public function updateTable($name, $options)
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
    public function renameTable($currentName, $newName)
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
        $tableExists = $this->connectToDatabase()->prepare("
            SELECT
                TABLE_NAME
            FROM 
                INFORMATION_SCHEMA.TABLES
            WHERE 
                TABLE_SCHEMA = '{$this->dbConfigs['name']}'
            AND
                TABLE_NAME = '{$name}';
        ");

        $tableExists->execute();
        return ($tableExists->fetch() != false);
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
    public function dropTable($name)
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
    public function addColumn($table, $name, $properties)
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
    public function updateColumn($table, $name, $properties)
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
    public function renameColumn($table, $currentName, $newName)
    {
        $this->execute(
            "ALTER TABLE $table RENAME COLUMN $currentName TO $newName;"
        );
    }

    /**
     * Drop column.
     * 
     * @param string $table
     * @param string $name
     * @return void
     */
    public function dropColumn($table, $name)
    {
        $this->execute("ALTER TABLE $table DROP COLUMN $name;");
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
    public function addForeignKey(
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
     * Drop foreign key.
     * 
     * @param string $constraint
     * @return void
     */
    public function dropForeignKey($constraint)
    {

    }
}