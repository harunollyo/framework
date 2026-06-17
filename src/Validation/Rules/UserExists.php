<?php
/**
 * Rule to ensure a a user exist with id.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

class UserExists extends BaseRule
{
    /**
     * Check if the user exist
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (empty($this->value)) {
            return true;
        }

        return get_userdata($this->value) !== false;
    }

    /**
     * Get the error message if the post does not exist.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('User with id %s does not exist.', $this->value);
    }
}
