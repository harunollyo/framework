<?php
/**
 * Session storage driver that keeps payloads in memory for the current request only.
 * Nothing is persisted, so no value survives a redirect or a subsequent request.
 * Intended for tests and for contexts where persistence is unwanted.
 *
 * @package    Framework
 * @subpackage Session\Handlers
 * @since      1.0.0
 */
namespace Framework\Session\Handlers;

defined('ABSPATH') || exit;

use Framework\Contracts\SessionHandler;

class ArraySessionHandler implements SessionHandler
{
    /**
     * The payloads held for the current request, keyed by session identifier.
     *
     * @var array<string,array>
     *
     * @since 1.0.0
     */
    protected array $payloads = [];

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
        $payload = $this->payloads[$id] ?? [];

        return is_array($payload) ? $payload : [];
    }

    /**
     * Write the payload for the given session identifier.
     *
     * @param string $id The session identifier.
     * @param array $payload The session payload.
     * @param int $lifetime The lifetime in seconds, unused by this driver.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function write(string $id, array $payload, int $lifetime)
    {
        $this->payloads[$id] = $payload;

        return true;
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
        unset($this->payloads[$id]);

        return true;
    }
}
