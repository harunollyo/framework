<?php
/**
 * String rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Validation\Rule;

defined('ABSPATH') || exit;

class RequiredRule extends Rule
{
    /**
     * Validate the rule.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validate(): bool
    {
        return is_string($this->value);
    }

    /**
     * Get the error message.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function messages()
    {
        return $this->process_messages($this->default_messages);
    }
}
