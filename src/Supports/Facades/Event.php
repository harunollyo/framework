<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static void dispatch($event)
 * @method static void dispatch_if(Closure $boolean, $event)
 * @method static void dispatch_unless(Closure $boolean, $event)
 *
 * @see \Framework\Core\Managers\EventManager
 */
class Event extends Facade
{
    public static function get_accessor()
    {
        return 'event';
    }
}
