<?php
/**
 * Session storage driver backed by WordPress transients.
 * Expiry is delegated to WordPress, so no dedicated table or cleanup schedule is introduced.
 * Storage keys are namespaced with the application prefix.
 *
 * @package    Framework
 * @subpackage Session\Handlers
 * @since      1.0.0
 */
namespace Framework\Session\Handlers;

defined('ABSPATH') || exit;

use Framework\Contracts\SessionHandler;

use function Framework\with_prefix;

class TransientSessionHandler implements SessionHandler
{
    /**
     * Read the payload stored for the given session identifier.
     *
     * @param string $id The session identifier.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function read(string $id)
    {
        $payload = get_transient($this->storage_key($id));

        return is_array($payload) ? $payload : [];
    }

    /**
     * Write the payload for the given session identifier.
     *
     * @param string $id The session identifier.
     * @param array $payload The session payload.
     * @param int $lifetime The lifetime in seconds.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function write(string $id, array $payload, int $lifetime)
    {
        return (bool) set_transient($this->storage_key($id), $payload, $lifetime);
    }

    /**
     * Destroy the payload stored for the given session identifier.
     *
     * @param string $id The session identifier.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function destroy(string $id)
    {
        return (bool) delete_transient($this->storage_key($id));
    }

    /**
     * Determine whether an external object cache is serving transients.
     *
     * When one is, payloads live in that cache rather than the options table,
     * so a cache flush discards live sessions.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function is_external_object_cache()
    {
        return function_exists('wp_using_ext_object_cache') ? (bool) wp_using_ext_object_cache() : false;
    }

    /**
     * Build the namespaced transient key for a session identifier.
     *
     * @param string $id The session identifier.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function storage_key(string $id)
    {
        return with_prefix('session_' . $id);
    }
}
