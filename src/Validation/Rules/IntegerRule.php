<?php
/**
 * Validates that a value is an integer.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

class IntegerRule extends BaseRule
{
    /**
     * check for strict data type 
     * 
     * @var bool
     */
    protected $check_strict_data_type = true;

    /**
     * Check if the value is a valid integer.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return filter_var($this->value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Get the error message for an invalid integer value.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be of type integer.', $this->last_key_segment());
    }
}
