<?php
/**
 * Not in rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;

defined('ABSPATH') || exit;

/**
 * Validates that the given value is not in the list of disallowed values.
 */
class NotInRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'not_in';

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The {name} field must not be in the list of "{args}".',
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
        if (in_array($this->value, (array) $this->args)) {
            return $this->fails($this->default_messages['default']);
        }

        return true;
    }

    /**
     * Get the error messages.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function messages()
    {
        return $this->process_messages($this->messages);
    }
}
