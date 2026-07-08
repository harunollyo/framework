<?php
/**
 * Prohibited rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;

defined('ABSPATH') || exit;

/**
 * Validates that the given field is absent or empty.
 */
class ProhibitedRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'prohibited';

    /**
     * Whether the rule is an implicit rule.
     *
     * @var bool
     *
     * @since 1.0.0
     */
    public bool $is_implicit = true;

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The {name} field is prohibited.',
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
        if ($this->is_nullish($this->value)) {
            return true;
        }

        return $this->fails($this->default_messages['default']);
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
