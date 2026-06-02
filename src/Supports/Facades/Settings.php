<?php

namespace Framework\Supports\Facades;

use Framework\Facade;

/**
 * @method static \Framework\AppSettings get(string $key)
 *
 * @see \Kirki\App\Settings\SettingsFactory
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function get_accessor()
    {
        return 'settings';
    }
}
