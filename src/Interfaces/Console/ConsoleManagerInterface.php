<?php

namespace SigmaPHP\Core\Interfaces\Console;

/**
 * Console Manager Interface
 */
interface ConsoleManagerInterface
{
    /**
     * Execute console commands.
     * 
     * @param string $input
     * @return void
     */
    public function execute($input);
}