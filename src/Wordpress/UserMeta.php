<?php
/**
 * Class to handle the user meta Provides static methods for getting, setting, updating, and deleting user meta.
 *
 * @package    Framework
 * @subpackage Wordpress
 * @since      1.0.0
 */
namespace Framework\Wordpress;

defined('ABSPATH') || exit;

use function Framework\with_prefix;
use function Framework\without_prefix;

class UserMeta
{
    /**
     * Retrieve user meta by ID and key.
     *
     * @param int $user_id The user id.
     * @param string $key The key.
     * @param bool $single The single.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public static function get(int $user_id, string $key = '', bool $single = true)
    {
        if (!empty($key)) {
            return get_user_meta($user_id, with_prefix($key), $single);
        }

        $metadata = get_user_meta($user_id, $key, $single);

        $updated_metadata = [];

        foreach ($metadata as $key => $value) {
            $key = without_prefix($key);
            $updated_metadata[$key] = $single ? $value[0] : $value;
        }

        return $updated_metadata;
    }

    /**
     * Get all user meta by ID.
     *
     * @param int $user_id The user id.
     * @param mixed $single The single.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function get_all(int $user_id, $single = true)
    {
        return self::get($user_id, '', $single);
    }

    /**
     * Add the value of a specific user meta.
     *
     * @param int $user_id The user id.
     * @param string $key The key.
     * @param mixed $value The value.
     * @param mixed $unique The unique.
     *
     * @return bool|int
     *
     * @since 1.0.0
     */
    public static function add(int $user_id, string $key, $value, $unique = false)
    {
        return add_user_meta($user_id, with_prefix($key), $value, $unique);
    }

    /**
     * Add multiple user meta at once.
     *
     * @param int $user_id The user id.
     * @param array $meta_input The meta input.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function add_many(int $user_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::add($user_id, $key, $value);
        }

        return true;
    }

    /**
     * Update the value of a specific user meta.
     *
     * @param int $user_id The user id.
     * @param string $key The key.
     * @param mixed $value The value.
     * @param mixed $prev_value The prev value.
     *
     * @return bool|int
     *
     * @since 1.0.0
     */
    public static function update(int $user_id, string $key, $value, $prev_value = '')
    {
        return update_user_meta($user_id, with_prefix($key), $value, $prev_value);
    }

    /**
     * Update multiple user meta at once.
     *
     * @param int $user_id The user id.
     * @param array $meta_input The meta input.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function update_many(int $user_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::update($user_id, $key, $value);
        }

        return true;
    }

    /**
     * Delete a specific user meta.
     *
     * @param int $user_id The user id.
     * @param string $key The key.
     * @param mixed $value The value.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function delete(int $user_id, string $key, $value = '')
    {
        return delete_user_meta($user_id, with_prefix($key), $value);
    }
}
