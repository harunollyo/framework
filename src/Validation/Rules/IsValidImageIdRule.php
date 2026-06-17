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

class IsValidImageIdRule extends BaseRule
{
    /**
     * Determine if the value is a valid date in the given format.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return wp_attachment_is_image($this->value);
    }

    /**
     * Get the error message for invalid date format.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be a valid media image', $this->last_key_segment());
    }
}
