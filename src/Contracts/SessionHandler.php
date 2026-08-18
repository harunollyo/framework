<?php
/**
 * Contract for session storage drivers.
 * Describes reading a payload by identifier, writing one with a lifetime, and destroying one.
 * Implementations must never throw for a missing payload; they return an empty array instead.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface SessionHandler
{
    /**
     * Read the payload stored for the given session identifier.
     *
     * A missing, expired, or corrupted payload must resolve to an empty array so
     * the session degrades to a fresh one rather than raising an error.
     *
     * @param string $id The session identifier.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function read(string $id);

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
    public function write(string $id, array $payload, int $lifetime);

    /**
     * Destroy the payload stored for the given session identifier.
     *
     * @param string $id The session identifier.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function destroy(string $id);
}
