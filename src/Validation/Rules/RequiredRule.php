<?php
/**
 * Validates that a value is present and not null.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

class RequiredRule extends BaseRule
{
    /**
     * Determine if the value is present.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validate_rule()
    {
        if (is_array($this->value)) {
            return count($this->value) > 0;
        }

        return !is_null($this->value) && $this->value !== '';
    }

    /**
     * Get the error message for a missing required field.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function get_error_message()
    {
        return sprintf('The %s field is required.', $this->last_key_segment());
    }

    /**
     * Ignore rule check.
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function ignore_rule_check()
    {
        return false;
    }
}
