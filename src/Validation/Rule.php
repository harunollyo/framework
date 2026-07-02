<?php
/**
 * Rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation;

use Framework\Supports\Arr;
use Framework\Supports\Fluent;
use Framework\Validation\Rules\ArrayRule;
use Framework\Validation\Rules\NumericRule;
use Framework\Validation\Rules\RequiredRule;
use Framework\Validation\Rules\StringRule;
use Override;

defined('ABSPATH') || exit;

abstract class Rule extends Fluent
{
    /**
     * The data to validate.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $data = [];

    /**
     * The value to validate.
     *
     * @var mixed
     *
     * @since 1.0.0
     */
    protected $value;

    /**
     * The name of the field where the validation rule is applied.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $name = '';

    /**
     * The name of the validation rule.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = '';

    /**
     * Whether to check the strict data type.
     *
     * @var bool
     *
     * @since 1.0.0
     */
    protected bool $strict = false;

    /**
     * The messages to return.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $messages;

    /**
     * Whether to stop on first failure.
     *
     * @var bool
     *
     * @since 1.0.0
     */
    protected $should_stop_on_first_failure = false;

    /**
     * The constraints to validate.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $constraints = [];

    /**
     * Create a new rule instance.
     *
     * @param array $data The data to validate.
     * @param mixed $value The value to validate.
     * @param string $name The name of the field where the validation rule is applied.
     *
     * @return void
     * 
     * @since 1.0.0
     */
    public function __construct(array $data = [], $value = null, string $name = '')
    {
        $this->data = $data;
        $this->value = $value;
        $this->name = $name;
    }

    /**
     * Set whether to stop on first failure.
     *
     * @param bool $should_stop_on_first_failure Whether to stop on first failure.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function should_stop_on_first_failure(bool $should_stop_on_first_failure)
    {
        $this->should_stop_on_first_failure = $should_stop_on_first_failure;

        return $this;
    }

    /**
     * Set the value to validate.
     *
     * @param mixed $value The value to validate.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function with_value($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set the data to validate.
     *
     * @param array $data The data to validate.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function with_data(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the name of the field where the validation rule is applied.
     *
     * @param string $name The name of the field where the validation rule is applied.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function with_name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Add constraints to the validation rule.
     *
     * @param callable $callback The callback to call when a constraint fails.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function with_constraints(callable $callback)
    {
        $constraints = array_intersect($this->keys(), $this->constraints);
        $passed = true;

        foreach ($constraints as $constraint) {
            $method = 'compile_' . strtolower($constraint);

            if (!method_exists($this, $method)) {
                continue;
            }

            if (!$this->$method($this->value)) {
                $passed = false;
                $callback($passed, $constraint);

                if ($this->should_stop_on_first_failure) {
                    return false;
                }

                $passed = false;
            }
        }

        return $passed;
    }

    /**
     * Check if the rule has a constraint.
     *
     * @param string $constraint The constraint to check.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    public function has_constraint(string $constraint)
    {
        return in_array($constraint, $this->constraints, true);
    }

    /**
     * Validate the rule.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    abstract public function validate(): bool;

    /**
     * Get the error message.
     *
     * @return string
     *
     * @since 1.0.0
     */
    abstract public function message();

    /**
     * Set the messages to return.
     *
     * @param array $messages The messages to return.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function with_messages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Reset the messages to an empty array.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function without_messages()
    {
        $this->messages = [];

        return $this;
    }

    /**
     * Add a message to the validation errors.
     *
     * @param string $message The message to add.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function fails($message)
    {
        $this->messages = array_merge($this->messages, Arr::wrap($message));

        return false;
    }

    /**
     * Create a new string rule.
     *
     * @return StringRule
     *
     * @since 1.0.0
     */
    public static function string()
    {
        return new StringRule();
    }

    /**
     * Create a new required rule.
     *
     * @return RequiredRule
     *
     * @since 1.0.0
     */
    public static function required()
    {
        return new RequiredRule();
    }

    /**
     * Create a new array rule.
     *
     * @return ArrayRule
     *
     * @since 1.0.0
     */
    public static function array()
    {
        return new ArrayRule();
    }

    /**
     * Create a new numeric rule.
     *
     * @return NumericRule
     *
     * @since 1.0.0
     */
    public static function numeric()
    {
        return new NumericRule();
    }
}
