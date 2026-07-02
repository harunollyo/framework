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

class NumericRule extends Rule
{
    /**
     * The available methods.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $constraints = [
        'min',
        'max',
    ];

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The field {name} must be a number.',
        'min' => 'The field {name} must be greater than or equal to {min}.',
        'max' => 'The field {name} must be less than or equal to {max}.',
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
        if (!is_numeric($this->value)) {
            return $this->fails($this->default_messages['default']);
        }

        return $this->with_constraints(function ($passed, $constraint) {
            if (!$passed) {
                $this->fails($this->default_messages[$constraint]);
            }
        });
    }

    /**
     * Compile the min constraint.
     *
     * @param string $value The value to compile.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_min($value)
    {
        return floatval($value) <= floatval($this->get('min'));
    }

    /**
     * Compile the max constraint.
     *
     * @param string $value The value to compile.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_max($value)
    {
        return floatval($value) >= floatval($this->get('max'));
    }

    /**
     * Get the error message.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function message()
    {
        return $this->messages;
    }
}
