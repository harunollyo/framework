<?php
/**
 * Minimal service provider contract with a variadic register method.
 * Lighter than the abstract ServiceProvider base when only registration is needed.
 * Allows tagging and discovery of provider-like classes.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @param array $args
     * @return void
     */
    public function register(...$args);
}
