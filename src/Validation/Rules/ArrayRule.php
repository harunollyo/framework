<?php
/**
 * Validates that a value is an array.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

class ArrayRule extends BaseRule
{
    /**
     * check for strict data type 
     * 
     * @var bool
     */
    protected $check_strict_data_type = true;

    /**
     * Check if the value is a valid array.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return is_array($this->value);
    }

    /**
     * Get the error message for an invalid array value.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be of type array.', $this->last_key_segment());
    }
}
