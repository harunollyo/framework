<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static void register(string $command_name, $callback)
 * 
 * @see \Framework\Core\Console\CommandManager
 */
class Command extends Facade
{
    public static function get_accessor()
    {
        return 'command';
    }
}
