<?php

namespace SigmaPHP\DB\Console;

use Exception;
use SigmaPHP\DB\Interfaces\Console\ConsoleManagerInterface;

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
                $this->createConfigFile();
                break;

            case 'create:migration':
                $this->loadConfigs();
                $this->createMigrationFile($argument);
                break;

            case 'create:seeder':
                $this->loadConfigs();
                $this->createSeeder($argument);
                break;

            case 'migrate':
                $this->loadConfigs();
                $this->migrate($argument);
                break;

            case 'rollback':
                $this->loadConfigs();
                $this->rollback();
                break;

            case 'seed':
                $this->loadConfigs();
                $this->seed();
                break;

            default:
                $this->commandNotFound();
                break;
        }
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

        print($message . PHP_EOL);
    }

    /**
     * Load config.
     * 
     * @param string $path
     * @return void
     */
    private function loadConfigs($path = '')
    {
        $path = dirname(__DIR__, 2);

        if (!empty($option)) {
            if ((strpos($option, '--config=') !== false)) {
                $path = str_replace('--config=', '', $option);
            } else {
                throw new Exception("Unknown option $option");
            }    
        }

        if (!file_exists($path . '/' . self::DEFAULT_CONFIG_FILE_NAME)) {
            echo <<<ERROR
            \033[31mNo config file was found , please create new config
            file or run 'php sigma-db help' for help.
            ERROR . PHP_EOL;
        }

        $this->configs = require $path . '/' . self::DEFAULT_CONFIG_FILE_NAME;
    }

    /**
     * Print framework version.
     * 
     * @return void
     */
    private function version()
    {
        print("SigmaPHP-DB version 0.1.0" . PHP_EOL);
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

        print($helpContent . PHP_EOL);
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
            echo "\033[32m{$name} was created successfully" . PHP_EOL;
        } catch (\Exception $e) {
            echo "\033[31m $e" . PHP_EOL;
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
            $path ?: dirname(__DIR__, 5),
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

        $fileName = 'Create' . ucfirst($fileName) . 'Table';

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
     * @param string $seederName
     * @return void
     */
    private function createSeeder($seederName)
    {

    }

    /**
     * Migrate the database.
     * 
     * @return void
     */
    private function migrate($migrationName)
    {

    }

    /**
     * Rollback the database.
     * 
     * @return void
     */
    private function rollback()
    {

    }

    /**
     * Seed the database.
     * 
     * @return void
     */
    private function seed()
    {

    }
}
