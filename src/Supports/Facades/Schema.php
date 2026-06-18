<?php
/**
 * Facade proxy for SchemaManager database schema operations.
 * Exposes create, drop, and foreign key constraint toggles.
 * Used by migrations to define and modify tables.
 *
 * @package    Framework
 * @subpackage Supports\Facades
 * @since      1.0.0
 */
namespace Framework\Supports\Facades;

defined('ABSPATH') || exit;

use Framework\Facade;

/**
 * @method static void create(string $table, \Closure $callback)
 * @method static void drop_if_exists(string $table)
 * @method static void drop(string $table)
 * @method static void enabled_checking_foreign_key_constraints()
 * @method static void disabled_checking_foreign_key_constraints()
 * @see    \Framework\Database\Schema\SchemaManager
 */

class Schema extends Facade
{
    public static function get_accessor()
    {
        return 'schema';
    }
}
