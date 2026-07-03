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

class ArrayRule extends Rule
{
    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The field {name} must be an array.',
    ];

    /**
     * Validate the rule.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validate(): bool
    {
        if (!is_array($this->value)) {
            return $this->fails($this->default_messages['default']);
        }

        return true;
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
        return $this->process_messages($this->messages);
    }
}
