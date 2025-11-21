<?php

namespace SigmaPHP\DB\Console;

use SigmaPHP\DB\Exceptions\InvalidArgumentException;
use SigmaPHP\DB\Migrations\Logger;
use SigmaPHP\DB\Connectors\Connector;
use Doctrine\Inflector\InflectorFactory;
use SigmaPHP\DB\Exceptions\InvalidConfigurationException;
use SigmaPHP\DB\Interfaces\Console\ConsoleManagerInterface;

/**
 * Console Manager Class
 */
class ConsoleManager implements ConsoleManagerInterface
{
    /**
     * @var string Default config file name
     */
    private const DEFAULT_CONFIG_FILE_NAME = 'database';

    /**
     * @var string Default config file extension
     */
    private const DEFAULT_CONFIG_FILE_EXTENSION = 'php';

    /**
     * @var array $configs
     */
    private $configs;
 
    /**
     * @var string $basePath
     */
    private $basePath;

    /**
     * @var Connector $dbConnector
     */
    private $dbConnector;

    /**
     * @var Doctrine\Inflector\InflectorFactory $inflector
     */
    private $inflector;

    /**
     * ConsoleManager Constructor
     */
    public function __construct()
    {
        $this->basePath = dirname(
            (new \ReflectionClass(
                \Composer\Autoload\ClassLoader::class
            ))->getFileName()
        , 3);

        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * Execute console commands.
     * 
     * @param string $input
     * @return void
     */
    final public function execute($input)
    {
        $command = $input[1] ?? 'help';
        $argument = $input[2] ?? null;
        $option = $input[3] ?? null;

        // check that one of the parameters is the config
        $customConfigPath = '';
        
        if (!empty($argument) && (strpos($argument, '--config=') !== false)) {
            $customConfigPath = $argument;
            $argument = null;
        } else {
            $customConfigPath = $option;
        }

        if (!in_array($command, ['version', 'help', 'create:config'])) {
            $this->loadConfigs($customConfigPath);
        }
        
        switch ($command) {
            case 'version':
                $this->version();
                break;

            case 'help':
                $this->help();
                break;
            
            case 'create:config':
                $this->createConfigFile($argument);
                break;

            case 'create:migration':
                $this->createMigrationFile($argument);
                break;

            case 'create:model':
                $this->createModelFile($argument);
                break;

            case 'create:seeder':
                $this->createSeeder($argument);
                break;

            case 'migrate':
                $this->migrate($argument);
                break;

            case 'rollback':
                $this->rollback($argument);
                break;

            case 'seed':
                $this->seed($argument);
                break;

            case 'truncate':
                $this->truncate();
                break;

            case 'drop':
                $this->drop();
                break;

            case 'fresh':
                $this->drop();
                $this->migrate();
                $this->seed();
                break;
                
            default:
                $this->commandNotFound();
                break;
        }
    }

    /**
     * Get database connection instance (PDO) 
     * or create new one if it's not found.
     *
     * @return \PDO
     */
    private function getDbConnection()
    {
        if (!isset($this->configs['database_connection']) ||
            empty($this->configs['database_connection'])
        ) {
            throw new InvalidConfigurationException(
                'Couldn\'t connect to the DB , no configs were provided!'
            );
        }

        if (!($this->dbConnector instanceof Connector)) {
            $this->dbConnector = new Connector(
                $this->configs['database_connection']
            );
        }

        return $this->dbConnector->connect();
    }

    /**
     * Print lines with custom font color.
     *
     * @param string $text
     * @param string $color
     * @return void
     */
    private function printMessage($text, $color = '')
    {
        // check if the stream (terminal) supports colorization.
        if (!stream_isatty(STDOUT) || isset($_SERVER['NO_COLOR'])) {
            $color = '';
        }

        switch ($color) {
            case 'success':
                $color = "\033[32m";
                break;
            case 'error':
                $color = "\033[31m";
                break;
            default:
                $color = '';
                break;
        }

        echo "{$color}{$text}" . PHP_EOL;
    }

    /**
     * Execute the command and print its output.
     *
     * @param string $command
     * @return void
     */
    private function executeCommand($command)
    {
        $output = [];

        exec($command, $output);

        foreach ($output as $line) {
            print($line . PHP_EOL);
        }
    }
    
    /**
     * Default error message.
     * 
     * @return void
     */
    private function commandNotFound()
    {
        $message = <<< NotFound
        Invalid command.
        Type 'php sigma-db help' command for help.
        NotFound;

        $this->printMessage($message, "error");
    }

    /**
     * Load config.
     * 
     * @param string $path
     * @return void
     */
    private function loadConfigs($path = '')
    {
        $configFilePath = $this->basePath . '/' . 
            self::DEFAULT_CONFIG_FILE_NAME . '.' .
            self::DEFAULT_CONFIG_FILE_EXTENSION;

        if (!empty($path)) {
            if ((strpos($path, '--config=') !== false)) {
                $configFilePath = str_replace('--config=', '', $path);
            } else {
                throw new InvalidArgumentException("Unknown option $path");
            }    
        }

        if (!file_exists($configFilePath)) {
            $message = <<<ERROR
            No config file was found , please create new config
            file or run 'php sigma-db help' for help.
            ERROR;

            $this->printMessage($message, "error");
            exit;
        }

        $this->configs = require $configFilePath;

        // replace ./ with empty string if exists in the path
        $this->configs['path_to_migrations'] = 
            str_replace('./', '', $this->configs['path_to_migrations']);
        $this->configs['path_to_seeders'] = 
            str_replace('./', '', $this->configs['path_to_seeders']);
        $this->configs['path_to_models'] = 
            str_replace('./', '', $this->configs['path_to_models']);
    }

    /**
     * Extract files names from dir.
     *
     * @param string $path
     * @return array
     */
    private function getFilesNames($path)
    {
        if (!file_exists($path)) {
            $this->printMessage(
                "{$path} doesn't exists", "error"
            );

            return;
        }

        $filesNames = [];

        if ($handle = opendir($path)) {
            while (($file = readdir($handle))) {
                if (in_array($file, ['.', '..'])) continue;
                $filesNames[] = str_replace('.php', '', $file);   
            }
            
            closedir($handle);
        }

        return $filesNames;
    }

    /**
     * Print framework version.
     * 
     * @return void
     */
    private function version()
    {
        $this->printMessage("SigmaPHP-DB version 0.1.0");
    }
    
    /**
     * Print help menu.
     * 
     * @return void
     */
    private function help()
    {
        $helpContent = <<< HELP
        These are all available commands with SigmaPHP-DB CLI Tool:

            create:config {path}
                Create new config file, if no path was provided , a
                default config file (database.php) will be created 
                in the root of the project's folder. 
            create:migration {migration name}
                Create migration file.
            create:model {model name}
                Create model file. This command will generate
                in addition a new migration file automatically.
            create:seeder {seeder name}
                Create seeder file. 
            drop
                Drop all tables in the database.
            fresh
                Drop all tables in the database. then will run
                all migrations and seed the database.
            help
                Print all available commands (this menu).
            migrate {migration name}
                Run migration/s files.
            rollback {date}
                Rollback latest migration. or choose specific date
                to rollback to.
            seed {seeder name}
                Run seeder/s.
            version
                Print the current version of SigmaPHP-DB Package.
            truncate
                Delete the data in all tables.

        Examples:
            - php sigma-db version
            - php sigma-db create:migration MyMigration
            - php sigma-db seed
        HELP;

        $this->printMessage($helpContent);
    }
    
    /**
     * Create new file.
     * 
     * @param string $path
     * @param string $name
     * @param string $content
     * @param string $type
     * @param string $extension
     * @return void
     */
    private function createFile(
        $path, 
        $name, 
        $content,
        $type = '', 
        $extension = 'php' 
    ) {
        $file = $path . '/' . $name . '.' . $extension;

        try {
            if (file_exists($file)) {
                $this->printMessage(
                    "{$name} {$type} is already exists", "success"
                );

                return;
            }

            file_put_contents($file, $content);
            $this->printMessage(
                "{$name} {$type} was created successfully", "success"
            );
        } catch (\Exception $e) {
            $this->printMessage("{$e}", "error");
        }
    }

    /**
     * Create config file.
     * 
     * @param string $path
     * @return void
     */
    private function createConfigFile($path = '')
    {
        $this->createFile(
            $path ?: $this->basePath,
            self::DEFAULT_CONFIG_FILE_NAME,
            file_get_contents(__DIR__ . '/templates/database.php.dist'),
            'Config'
        );
    }

    /**
     * Create new migration file.
     * 
     * @param string $fileName
     * @return void
     */
    private function createMigrationFile($fileName)
    {
        $migrationFilesPath = $this->basePath . 
            $this->configs['path_to_migrations'];

        if (!is_dir($migrationFilesPath)) {
            mkdir($migrationFilesPath, 0755, true);
        }

        $template = '';
        $tableName = '';
        $fileType = 'Migration';
        $className = ucfirst($fileName) . $fileType;

        switch ($fileName) {
            case (bool) preg_match('/Create[a-zA-Z]*Table/', $fileName):                
                $tableName = $this->inflector->pluralize(
                    $this->inflector->tableize(
                        preg_replace(
                            ['/Create/', '/Table/'], '', $fileName
                        )
                    )
                );

                $template = str_replace(
                    ['$className', '$tableName'],
                    [$className, $tableName],
                    file_get_contents(
                        __DIR__ . '/templates/create_table_migration.php.dist'
                    )
                );

                break;
            case (bool) preg_match(
                    '/AddColumn[a-zA-Z]*To[a-zA-Z]*Table/', 
                    $fileName
                ):

                // we use this small hack to get the column and table names :)
                $migrationFileNameParts = explode('To', $fileName);
                
                $tableName = $this->inflector->pluralize(
                    $this->inflector->tableize(
                        preg_replace(
                            ['/Table/'], '', $migrationFileNameParts[1]
                        )
                    )
                );

                $fieldName = lcfirst(preg_replace(
                    ['/AddColumn/'], '', $migrationFileNameParts[0]
                ));

                $template = str_replace(
                    ['$className', '$tableName', '$fieldName'],
                    [$className, $tableName, $fieldName],
                    file_get_contents(
                        __DIR__ . '/templates/add_column_migration.php.dist'
                    )
                );

                break;
            default:
                $template = str_replace(
                    '$className',
                    $className,
                    file_get_contents(__DIR__ . '/templates/migration.php.dist')
                );
        }
        
        $this->createFile(
            $migrationFilesPath,
            $className,
            $template,
            $fileType
        );
    }

    /**
     * Create new model file with migration file.
     * 
     * @param string $fileName
     * @return void
     */
    private function createModelFile($fileName)
    {
        $modelsFilesPath = $this->basePath . 
            $this->configs['path_to_models'];

        if (!is_dir($modelsFilesPath)) {
            mkdir($modelsFilesPath, 0755, true);
        }

        $fileType = 'Model';
        $className = ucfirst($fileName);

        $this->createFile(
            $modelsFilesPath,
            $className,
            str_replace(
                '$className',
                $className,
                file_get_contents(__DIR__ . '/templates/model.php.dist')
            ),
            $fileType
        );

        // create new migration file for the model
        $inflector = InflectorFactory::create()->build();
        $migrationFileName = $inflector->pluralize($fileName);
        $this->createMigrationFile("Create{$migrationFileName}Table");
    }

    /**
     * Create new seeder.
     * 
     * @param string $fileName
     * @return void
     */
    private function createSeeder($fileName)
    {
        $seedersFilesPath = $this->basePath . 
            $this->configs['path_to_seeders'];

        if (!is_dir($seedersFilesPath)) {
            mkdir($seedersFilesPath, 0755, true);
        }

        $fileType = 'Seeder';
        $className = ucfirst($fileName) . $fileType;

        $this->createFile(
            $seedersFilesPath,
            $className,
            str_replace(
                '$className',
                $className,
                file_get_contents(__DIR__ . '/templates/seeder.php.dist')
            ),
            $fileType
        );
    }

    /**
     * Migrate the database.
     * 
     * @param string $migrationName
     * @return void
     */
    private function migrate($migrationName = '')
    {
        $migrations = [];

        // remove the file extension ".php" if exists
        $migrationName = str_replace('.php', '', $migrationName);

        if (!empty($migrationName)) {
            $migrations[] = $migrationName;
        } else {
            $migrations = $this->getFilesNames(
                $this->basePath . $this->configs['path_to_migrations']
            );
        }

        $logger = new Logger(
            $this->getDbConnection(),
            $this->configs['logs_table_name']
        );

        $migrations = $logger->canBeMigrated($migrations);

        if (empty($migrations)) {
            $this->printMessage(
                "All migrations were already run , " .
                "Nothing to be migrated.",
                "error"
            );

            return;
        }

        foreach ($migrations as $migration) {
            require_once $this->basePath .
                $this->configs['path_to_migrations'] .
                "/{$migration}.php";
            
            $migrationClass = new $migration(
                $this->getDbConnection(),
                $this->dbConnector->getDatabaseName()
            );
            
            $migrationClass->up();
            $logger->log($migration);

            $this->printMessage(
                "{$migration} was migrated successfully.",
                "success"
            );
        }

        $this->printMessage(
            "All migrations ran successfully.",
            "success"
        );
    }

    /**
     * Rollback the database.
     * 
     * @param string $date
     * @return void
     */
    private function rollback($date = '')
    {
        $logger = new Logger(
            $this->getDbConnection(),
            $this->configs['logs_table_name']
        );

        foreach ($logger->canBeRolledBack($date) as $migration) {
            require_once $this->basePath .
                $this->configs['path_to_migrations'] .
                "/{$migration}.php";
            
            $migrationClass = new $migration(
                $this->getDbConnection(),
                $this->configs['database_connection']['name']
            );
            
            $migrationClass->down();
            $logger->removeLog($migration);

            $this->printMessage(
                "{$migration} was rolledback successfully.",
                "success"
            );
        }

        $this->printMessage("Database rolled back successfully.", "success");
    }

    /**
     * Seed the database.
     * 
     * @param string $seederName
     * @return void
     */
    private function seed($seederName = '')
    {
        $seeders = [];

        // remove the file extension ".php" if exists
        $seederName = str_replace('.php', '', $seederName);

        if (!empty($seederName)) {
            $seeders[] = $seederName;
        } else {            
            $seeders = $this->getFilesNames(
                $this->basePath . $this->configs['path_to_seeders']
            );
        }

        foreach ($seeders as $seeder) {
            require_once $this->basePath .
                $this->configs['path_to_seeders'] .
                "/{$seeder}.php";

            $seed = new $seeder(
                $this->getDbConnection()
            );

            $seed->run();

            $this->printMessage(
                "{$seeder} was run successfully.",
                "success"
            );
        }

        $this->printMessage("All seeders ran successfully.", "success");
    }

    /**
     * Truncate the database.
     * 
     * @return void
     */
    private function truncate()
    {
        $message = "This command will truncate all tables, ";
        $message .= "Are You Sure? (Enter 'YES' to proceed)";
        $this->printMessage($message);

        $answer = stream_get_line(STDIN, 16, PHP_EOL);

        if ($answer == 'YES') {
            $logger = new Logger(
                $this->getDbConnection(),
                $this->configs['logs_table_name']
            );

            $tables = $logger->getAllTables(
                $this->configs['database_connection']['name']
            );
                        
            foreach ($tables as $table) {
                if ($table == $this->configs['logs_table_name']) {
                    continue;
                }

                $logger->execute("
                    TRUNCATE TABLE {$table};
                ");
            }

            $this->printMessage(
                "All tables were truncated successfully.",
                "success"
            );
        }
    }

    /**
     * Drop all tables in the database.
     * 
     * @return void
     */
    private function drop()
    {
        $message = "This command will drop all tables, ";
        $message .= "Are You Sure? (Enter 'YES' to proceed)";
        $this->printMessage($message);

        $answer = stream_get_line(STDIN, 16, PHP_EOL);

        if ($answer == 'YES') {
            $logger = new Logger(
                $this->getDbConnection(),
                $this->configs['logs_table_name']
            );

            $tables = $logger->getAllTables(
                $this->configs['database_connection']['name']
            );
                        
            foreach ($tables as $table) {
                $logger->execute("
                    DROP TABLE {$table};
                ");
            }

            $this->printMessage(
                "All tables were dropped successfully.",
                "success"
            );
        }
    }
}
