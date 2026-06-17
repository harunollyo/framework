<?php
/**
 * Contract for WordPress capability management actions.
 * Requires handle logic plus a method to retrieve capability slugs, optionally filtered by role.
 * Used by hooks that register or sync custom capabilities.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface Capability
{
    /**
     * Execute the action logic.
     *
     * @return void
     */
    public function handle();

    /**
     * Get the capabilities .
     *
     * @param string $role
     * @return array
     */
    public function get_capabilities($role = null);
}
