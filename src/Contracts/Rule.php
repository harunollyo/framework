<?php
/**
 * Contract for validation rule.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface Rule
{
    /**
     * Check the validity.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_valid();

    /**
     * Get the error message.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_error_message();

    /**
     * Get the value.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_value();

    /**
     * Check if the rule is for a specific data type. 
     */
    public function is_check_strict_data_type();
}
