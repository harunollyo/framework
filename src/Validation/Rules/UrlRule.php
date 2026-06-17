<?php
/**
 * Validates that the given value is a valid url.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

class UrlRule extends BaseRule
{
    /**
     * Determine if the value is a url.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return filter_var($this->value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get the error message for invalid url.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be of type url.', $this->last_key_segment());
    }
}
