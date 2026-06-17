<?php
/**
 * Validates that the given value matches db date time format.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

use Framework\Constants\DateTimeFormats;
use DateTime;
use Framework\Supports\Facades\Date;

class DateTimeRule extends BaseRule
{
    /**
     * Determine if the value is a valid date in the given format.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $date = Date::createFromFormat(DateTimeFormats::DB_DATETIME, $this->value);

        return $date && $date->isValid();
    }

    /**
     * Get the error message for invalid date format.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be a valid date time in the format %s.', $this->last_key_segment(), DateTimeFormats::DB_DATETIME);
    }
}
