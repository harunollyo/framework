<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static mixed authorize(string $ability, $model = null, ...$arguments)
 * @method static bool allows(string $ability, $model = null, ...$arguments)
 * @method static bool denies(string $ability, $model = null, ...$arguments)
 * 
 * @see \Framework\Managers\PolicyManager
 */
class Guard extends Facade
{
    public static function get_accessor()
    {
        return 'policy';
    }
}
