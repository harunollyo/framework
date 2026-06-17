<?php
/**
 * Rule to ensure the value is after the given date.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

use Framework\Supports\Facades\Date;

class AfterRule extends BaseRule
{
    /**
     * Check if the rule is valid.
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (array_key_exists($this->rule_value, $this->data)) {
            if (Date::is_valid_date($this->value)) {
                return Date::parse($this->value)->isAfter(
                    Date::parse($this->data[$this->rule_value])
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Get the error message if the rule is not valid.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be after %s.', $this->last_key_segment(), $this->rule_value);
    }
}
