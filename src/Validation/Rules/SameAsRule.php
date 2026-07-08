<?php
/**
 * Same as rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;

use function Framework\deep_get;

defined('ABSPATH') || exit;

/**
 * Validates that the given value strictly matches the value of another field.
 */
class SameAsRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'same_as';

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The {name} field must match the {args} field.',
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
        $other_value = deep_get($this->data, (string) $this->args);

        if ($this->value !== $other_value) {
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
