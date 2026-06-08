<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static bool set(string $name, $value)
 * @method static mixed get(string $name, $default = null)
 * @method static bool delete(string $name)
 * 
 * @see \Framework\Managers\OptionManager
 */
class Option extends Facade
{
    public static function get_accessor()
    {
        return 'option';
    }
}
