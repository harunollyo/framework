<?php
/**
 * Validates that the given value is an email.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

class EmailRule extends BaseRule
{
    /**
     * Determine if the value is an email.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Get the error message for invalid email.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be of type email.', $this->last_key_segment());
    }
}
