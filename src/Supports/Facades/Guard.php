<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static mixed authorize(string $ability, $model = null)
 * @method static bool allows(string $ability, $model = null)
 * @method static bool denies(string $ability, $model = null)
 * 
 * @see \Framework\Core\Managers\PolicyManager
 */
class Guard extends Facade
{
    public static function get_accessor()
    {
        return 'policy';
    }
}
