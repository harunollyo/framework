<?php
/**
 * Validates that the given value matches a specific date format.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

use DateTime;

class DateFormatRule extends BaseRule
{

    /**
     * Determine if the value is a valid date in the given format.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $date = DateTime::createFromFormat($this->rule_value, $this->value);
        return $date && $date->format($this->rule_value) === $this->value;
    }

    /**
     * Get the error message for invalid date format.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be a valid date in the format %s.', $this->last_key_segment(), $this->rule_value);
    }
}
