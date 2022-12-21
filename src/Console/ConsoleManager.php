<?php

namespace SigmaPHP\Core\Console;

use PassGen\PassGen;
use SigmaPHP\Core\Config\Config;

/**
 * Console Manager Class
 */
class ConsoleManager
{
    /**
     * ConsoleManager Constructor
     */
    public function __construct()
    {
        // todo
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
                $this->createMigrationFile($argument);
                break;

            case 'create:seeder':
                $this->createSeeder($argument);
                break;

            case 'migrate':
                $this->migrate();
                break;

            case 'rollback':
                $this->rollback();
                break;

            case 'seed':
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
        These are all available commands with SigmaPHP CLI Tool:

            create:config
                Create config file.
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
                Print the current version of SigmaPHP Framework.

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
            file_put_contents($path . $name, $content);
            echo "\033[32m {$name} was created successfully" . PHP_EOL;
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
    private function createConfigFile($path)
    {

    }

    /**
     * Create new migration file.
     * 
     * @param string $fileName
     * @return void
     */
    private function createMigrationFile($fileName)
    {
        $path = '';

        $content = <<< MIGRATION_CONTENT
        <?php
        
        namespace SigmaPHP\Controllers;

        use SigmaPHP\Core\Controllers\BaseController;

        class $fileName extends BaseController
        {
            /**
             * $fileName Constructor
             */
            public function __construct()
            {
                parent::__construct();
            }
        }
        MIGRATION_CONTENT;

        $this->createFile($path, $fileName . '.php', $content);
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
