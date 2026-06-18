<?php
/**
 * Facade proxy for the CommandManager WP-CLI registration service.
 * Exposes register for binding artisan-style commands under the kirki prefix.
 * Resolves from the command container binding.
 *
 * @package    Framework
 * @subpackage Supports\Facades
 * @since      1.0.0
 */
namespace Framework\Supports\Facades;

defined('ABSPATH') || exit;

use Framework\Facade;

/**
 * Facade proxy for the CommandManager WP-CLI registration service.
 *
 * @method static void register(string $command_name, $callback)
 * @see    \Framework\Core\Console\CommandManager
 */
class Command extends Facade
{
    /**
     * Get the accessor.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function get_accessor()
    {
        return 'command';
    }
}
