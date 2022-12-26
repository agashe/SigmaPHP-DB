<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Console\ConsoleManager;

/**
 * Console Manager Test
 */
class ConsoleManagerTest extends TestCase
{
    /**
     * @var ConsoleManager $consoleManager
     */
    private $consoleManager;

    /**
     * ConsoleManagerTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->consoleManager = new ConsoleManager();
    }

    /**
     * Test execute command method.
     *
     * @return void
     */
    public function testExecute()
    {
        $input = [
            'php',
            'version'
        ];

        $this->consoleManager->execute($input);
        $this->expectOutputString("SigmaPHP-DB version 0.1.0\n");
    }
    
    /**
     * Test sorry message will be printed for unknown commands.
     *
     * @return void
     */
    public function testUnknownCommand()
    {
        $input = [
            'php',
            'my-command'
        ];

        $this->consoleManager->execute($input);

        $expectedMessage = "Invalid command.\n";
        $expectedMessage .= "Type 'php sigma-db help' command for help.\n";

        $this->expectOutputString($expectedMessage);
    }
}