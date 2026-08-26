<?php
/**
 * Facade proxy for the SessionManager store.
 * Exposes reading, writing, flashing, identity, and persistence methods.
 * Entry point for session access from anywhere in the application.
 *
 * @package    Framework
 * @subpackage Supports\Facades
 * @since      1.0.0
 */
namespace Framework\Supports\Facades;

defined('ABSPATH') || exit;

use Framework\Facade;

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * Facade proxy for the SessionManager store.
 *
 * @method static \Framework\Managers\SessionManager start()
 * @method static mixed get(?string $key = null, $default = null)
 * @method static array all()
 * @method static array only(array $keys)
 * @method static bool has($key)
 * @method static bool exists($key)
 * @method static bool missing($key)
 * @method static \Framework\Managers\SessionManager put($key, $value = null)
 * @method static \Framework\Managers\SessionManager push(string $key, $value)
 * @method static mixed increment(string $key, int $amount = 1)
 * @method static mixed decrement(string $key, int $amount = 1)
 * @method static mixed remember(string $key, callable $callback)
 * @method static mixed pull(string $key, $default = null)
 * @method static \Framework\Managers\SessionManager forget($keys)
 * @method static \Framework\Managers\SessionManager flush()
 * @method static \Framework\Managers\SessionManager flash(string $key, $value = true)
 * @method static \Framework\Managers\SessionManager now($key, $value = null)
 * @method static \Framework\Managers\SessionManager reflash()
 * @method static \Framework\Managers\SessionManager keep($keys)
 * @method static \Framework\Managers\SessionManager flash_input(array $input)
 * @method static mixed get_old_input(?string $key = null, $default = null)
 * @method static bool has_old_input()
 * @method static string token()
 * @method static \Framework\Managers\SessionManager regenerate_token()
 * @method static \Framework\Managers\SessionManager regenerate(bool $destroy = false)
 * @method static \Framework\Managers\SessionManager invalidate()
 * @method static \Framework\Managers\SessionManager migrate(bool $destroy = false)
 * @method static string get_id()
 * @method static string get_name()
 * @method static bool has_session()
 * @method static bool is_started()
 * @method static bool is_modified()
 * @method static mixed previous_url($default = null)
 * @method static \Framework\Managers\SessionManager set_previous_url(string $url)
 * @method static bool save()
 * @method static string generate_id()
 * @method static bool is_valid_id(string $id)
 * @method static int get_lifetime_seconds()
 * @method static array get_never_flash()
 * @method static array get_defaults()
 * @see    \Framework\Managers\SessionManager
 */
class Session extends Facade
{
    /**
     * Get the accessor.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public static function get_accessor()
    {
        return 'session';
    }
}
