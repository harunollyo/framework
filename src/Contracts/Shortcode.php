<?php
/**
 * Contract for WordPress shortcode handlers.
 * Requires a name accessor and a callback matching the add_shortcode signature.
 * Standardizes shortcode registration across the plugin.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface Shortcode
{
    /**
     * Get the shortcode name.
     *
     * @return string
     */
    public function get_name();

    /**
     * The shortcode callback function.
     *
     * @param array $attributes
     * @param string $content
     * @param string $shortcode_tag
     * @return string
     */
    public function callback($attributes, string $content = '', string $shortcode_tag = '');
}
