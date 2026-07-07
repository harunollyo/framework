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

/**
 * Numeric rule class.
 *
 * @method $this integer()
 * @method $this min(int $min)
 * @method $this max(int $max)
 */
class NumericRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'numeric';

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
        'integer',
        'int',
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
        'integer' => 'The field {name} must be an integer.',
        'int' => 'The field {name} must be an integer.',
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
        $passed = true;

        if (!is_numeric($this->value)) {
            $this->fails($this->default_messages['default']);

            if ($this->should_stop_on_first_failure) {
                return false;
            }
        }

        $passed = $this->with_constraints(function ($passed, $constraint) {
            if (!$passed) {
                $this->fails($this->default_messages[$constraint], [$constraint => $this->get($constraint)]);
            }
        });

        return $passed;
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
        return floatval($value) >= floatval($this->get('min'));
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
        $x = floatval($value) <= floatval($this->get('max'));
        return $x;
    }

    /**
     * Compile the integer constraint.
     *
     * @param string $value The value to compile.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_integer($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Compile the int constraint.
     *
     * @param string $value The value to compile.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_int($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
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
