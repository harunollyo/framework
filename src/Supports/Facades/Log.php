<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static void debug($message)
 * @method static void info($message)
 * @method static void warning($message)
 * @method static void error($message)
 * @method static void emergency($message)
 * @method static void critical($message)
 * @method static void alert($message)
 *
 * @see \Framework\Managers\LogManager
 */
class Log extends Facade
{
    public static function get_accessor()
    {
        return 'log';
    }
}
