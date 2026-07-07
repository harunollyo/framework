<?php
/**
 * String rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;

defined('ABSPATH') || exit;

class ArrayRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'array';

    /**
     * The constraints.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $constraints = [
        'min',
        'max',
        'size',
        'exactly',
        'contains'
    ];

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The field {name} must be an array.',
        'min' => 'The field {name} must have at least {min} items.',
        'max' => 'The field {name} must have at most {max} items.',
        'size' => 'The field {name} must have exactly {size} items.',
        'exactly' => 'The field {name} must have exactly {exactly} items.',
        'contains' => 'The field {name} must contain {contains}.',
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

        return $this->with_constraints(function ($passed, $constraint) {
            if (!$passed) {
                $this->fails($this->default_messages[$constraint], [$constraint => $this->get($constraint)]);
            }
        });
    }

    /**
     * Validate the min constraint.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_min(): bool
    {
        return count($this->value) >= (int) $this->get('min');
    }

    /**
     * Validate the max constraint.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_max(): bool
    {
        return count($this->value) <= (int) $this->get('max');
    }

    /**
     * Validate the size constraint.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_size(): bool
    {
        return count($this->value) === (int) $this->get('size');
    }

    /**
     * Validate the exactly constraint.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_exactly(): bool
    {
        return count($this->value) === (int) $this->get('exactly');
    }

    /**
     * Validate the contains constraint.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_contains(): bool
    {
        return in_array($this->get('contains'), $this->value);
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
