<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static bool set(string $name, $value, $autoload = null, $with_prefix = true)
 * @method static mixed get(string|array $name, $default = null, $with_prefix = true)
 * @method static bool delete(string $name, $with_prefix = true)
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
