<?php
/**
 * Validates that the given value matches db date format.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

use Framework\Constants\DateTimeFormats;
use DateTime;

class DateRule extends BaseRule
{
    /**
     * Determine if the value is a valid date in the given format.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $date = DateTime::createFromFormat(DateTimeFormats::DB_DATE, $this->value);
        return $date && $date->format(DateTimeFormats::DB_DATE) === $this->value;
    }

    /**
     * Get the error message for invalid date format.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be a valid date time in the format %s.', $this->last_key_segment(), DateTimeFormats::DB_DATE);
    }
}
