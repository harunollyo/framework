<?php
/**
 * Validation rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation;

use Framework\Filesystem\File;
use Framework\Supports\Arr;
use Framework\Supports\Fluent;

defined('ABSPATH') || exit;

abstract class ValidationRule extends Fluent
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
     * The arguments to validate.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $args;

    /**
     * Whether the rule is an implicit rule.
     *
     * @var bool
     *
     * @since 1.0.0
     */
    public bool $is_implicit = false;

    /**
     * The messages to return.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $messages = [];

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
     * @param array $args The additional arguments to validate.
     *
     * @return void
     * 
     * @since 1.0.0
     */
    public function __construct($args = null)
    {
        $this->args = $args;
    }

    /**
     * Get the rule name.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function get_rule_name()
    {
        return $this->rule;
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
     * Run the applied constraints against the value.
     *
     * @param callable $callback The callback to call when a constraint fails.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function validate_constraints(callable $callback)
    {
        $constraints = array_intersect($this->keys(), $this->constraints);
        $passed = true;

        foreach ($constraints as $constraint) {
            $method = 'validate_' . strtolower($constraint);

            if (!method_exists($this, $method)) {
                continue;
            }

            if (!$this->$method($this->value)) {
                $passed = false;
                $callback($passed, $constraint);

                if ($this->should_stop_on_first_failure) {
                    return false;
                }
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
    abstract public function messages();

    /**
     * Check if the rule passed.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    public function passed()
    {
        return $this->validate();
    }

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
     * Check if the value is null, an empty string, or an empty countable.
     *
     * @param mixed $value The value to check.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    public static function is_nullish($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_countable($value) && count($value) < 1) {
            return true;
        }

        if ($value instanceof File) {
            return (string) $value->getPath() === '';
        }

        return false;
    }

    /**
     * Add a message to the validation errors.
     *
     * @param string $message The message to add.
     * @param array $placeholders The placeholders to replace.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function fails(string $message, array $placeholders = [])
    {
        $message = $this->replace_message_placeholders($message, $placeholders);
        $this->messages = array_merge($this->messages, Arr::wrap($message));

        return false;
    }

    /**
     * Replace the message placeholders.
     *
     * @param string $message The message to replace the placeholders in.
     * @param array $placeholders The placeholders to replace.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    protected function replace_message_placeholders(string $message, array $placeholders = [])
    {
        $placeholders = array_merge(
            [
                'name' => $this->name,
                'args' => is_array($this->args)
                    ? Arr::join(Arr::reject($this->args, fn ($arg) => !is_string($arg) && !is_numeric($arg)), ', ')
                    : (string) $this->args
            ],
            $placeholders
        );

        foreach ($placeholders as $key => $value) {
            $placeholder = '{' . $key . '}';
            $message = str_replace($placeholder, $value, $message);
        }

        return $message;
    }

    /**
     * Process the messages.
     *
     * @param array $messages The messages to process.
     *
     * @return array
     * 
     * @since 1.0.0
     */
    protected function process_messages(array $messages, array $data = [])
    {
        foreach ($messages as $key => $message) {
            $messages[$key] = $this->replace_message_placeholders($message, $data);
        }

        return $messages;
    }
}
