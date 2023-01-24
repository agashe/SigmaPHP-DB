<?php

namespace SigmaPHP\DB\Console;

use Exception;
use SigmaPHP\DB\Interfaces\Console\ConsoleManagerInterface;
use SigmaPHP\DB\Connectors\Connector;
use SigmaPHP\DB\Migrations\Logger;

/**
 * Console Manager Class
 */
class ConsoleManager implements ConsoleManagerInterface
{
    /**
     * @var string Default config file name.
     */
    private const DEFAULT_CONFIG_FILE_NAME = 'database.php';

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
     * ConsoleManager Constructor
     */
    public function __construct()
    {
        $this->basePath = dirname(__DIR__, 2);
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
                $this->loadConfigs($option);
                $this->createMigrationFile($argument);
                break;

            case 'create:seeder':
                $this->loadConfigs($option);
                $this->createSeeder($argument);
                break;

            case 'migrate':
                $this->loadConfigs($option);
                $this->migrate($argument);
                break;

            case 'rollback':
                $this->loadConfigs($option);
                $this->rollback();
                break;

            case 'seed':
                $this->loadConfigs($option);
                $this->seed($argument);
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
            throw new Exception(
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
        $configPath = $this->basePath;

        if (!empty($path)) {
            if ((strpos($path, '--config=') !== false)) {
                $configPath = str_replace('--config=', '', $path);
            } else {
                throw new \Exception("Unknown option $path");
            }    
        }

        if (!file_exists($configPath . '/' . self::DEFAULT_CONFIG_FILE_NAME)) {
            $message = <<<ERROR
            No config file was found , please create new config
            file or run 'php sigma-db help' for help.
            ERROR;

            $this->printMessage($message, "error");
            return;
        }

        $this->configs = require $configPath . '/' . 
            self::DEFAULT_CONFIG_FILE_NAME;
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
                Create config file, if no path was provided , a
                default config file (database.php) will be created 
                in the root of the project's folder. 
            create:migration {migration name}
                Create migration file.
            create:seeder {seeder name}
                Create seeder file. 
            help
                Print all available commands (this menu).
            migrate
                Run migration files.
            rollback
                Rollback latest migration.
            seed
                Run seeders.
            version
                Print the current version of SigmaPHP-DB Library.

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
     * @return void
     */
    private function createFile($path, $name, $content)
    {
        try {
            file_put_contents($path . '/' . $name, $content);
            $this->printMessage("{$name} was created successfully", "success");
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
            file_get_contents(__DIR__ . '/templates/database.php.dist')
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
        if (!is_dir($this->configs['path_to_migrations'])) {
            mkdir($this->configs['path_to_migrations'], 0755, true);
        }

        $fileName = ucfirst($fileName) . 'Migration';

        $this->createFile(
            $this->configs['path_to_migrations'],
            $fileName . '.php',
            str_replace(
                '$fileName',
                $fileName,
                file_get_contents(__DIR__ . '/templates/migration.php.dist')
            )
        );
    }

    /**
     * Create new seeder.
     * 
     * @param string $fileName
     * @return void
     */
    private function createSeeder($fileName)
    {
        if (!is_dir($this->configs['path_to_seeders'])) {
            mkdir($this->configs['path_to_seeders'], 0755, true);
        }

        $fileName = ucfirst($fileName) . 'Seeder';

        $this->createFile(
            $this->configs['path_to_seeders'],
            $fileName . '.php',
            str_replace(
                '$fileName',
                $fileName,
                file_get_contents(__DIR__ . '/templates/seeder.php.dist')
            )
        );
    }

    /**
     * Migrate the database.
     * 
     * @param string $migrationName
     * @return void
     */
    private function migrate($migrationName)
    {
        $migrations = [];
        $logger = new Logger(
            $this->getDbConnection(),
            $this->configs['logs_table_name']
        );

        if (!empty($migrationName)) {
            if (!empty($logger->canBeMigrated([$migrationName]))) {
                $migrations[] = $migrationName;
            } else {
                $this->printMessage(
                    "{$migrationName} was already migrated.",
                    "error"
                );

                return;
            }
        } else {
            if ($handle = opendir($this->configs['path_to_migrations'])) {
                while (($file = readdir($handle))) {
                    if (in_array($file, ['.', '..'])) continue;
                    $migrations[] = str_replace('.php', '', $file);   
                }
                
                closedir($handle);
            }

            $migrations = $logger->canBeMigrated($migrations);

            if (empty($migrations)) {
                $this->printMessage(
                    "Nothing to be migrated.",
                    "error"
                );

                return;
            }
        }

        foreach ($migrations as $migration) {
            require_once $this->configs['path_to_migrations'] . 
                '/' . $migration . '.php';
            
            $migrationClass = new $migration(
                $this->getDbConnection(),
                $this->dbConnector->getDatabaseName()
            );
            
            $migrationClass->up();
            $logger->log($migration);
        }

        $this->printMessage(
            "All migrations ran successfully.",
            "success"
        );
    }

    /**
     * Rollback the database.
     * 
     * @return void
     */
    private function rollback()
    {
        $logger = new Logger(
            $this->getDbConnection(),
            $this->configs['logs_table_name']
        );

        foreach ($logger->canBeRolledBack() as $migration) {
            require_once $this->configs['path_to_migrations'] . 
                '/' . $migration . '.php';
            
            $migrationClass = new $migration(
                $this->getDbConnection(),
                $this->configs['database_connection']['name']
            );
            
            $migrationClass->down();
            $logger->removeLog($migration);
        }

        $this->printMessage("Database rolled back successfully.", "success");
    }

    /**
     * Seed the database.
     * 
     * @param string $seederName
     * @return void
     */
    private function seed($seederName)
    {
        $seeders = [];

        if (!empty($seederName)) {
            if (file_exists(
                $this->configs['path_to_seeders'] . "/" . $seederName . '.php'
            )) {
                $seeders[] = $seederName;
            } else {
                throw new \Exception("Seeder '$seederName' doesn't exist!");
            }
        } else {            
            if ($handle = opendir($this->configs['path_to_seeders'])) {
                while (($file = readdir($handle))) {
                    if (in_array($file, ['.', '..'])) continue;
                    $seeders[] = str_replace('.php', '', $file);   
                }
                
                closedir($handle);
            }
        }

        foreach ($seeders as $seeder) {
            require_once $this->configs['path_to_seeders'] . 
                '/' . $seeder . '.php';

            $seed = new $seeder(
                $this->getDbConnection()
            );

            $seed->run();
        }

        $this->printMessage("All seeders ran successfully.", "success");
    }
}
