<?php
/**
 * String rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Supports\Arr;
use Framework\Validation\Rule;

defined('ABSPATH') || exit;

/**
 * Validates that the given value is a string.
 *
 * @method StringRule min(int $min)
 * @method StringRule max(int $max)
 * @method StringRule length(int $length)
 * @method StringRule size(int $size)
 * @method StringRule regex(string $regex)
 * @method StringRule email()
 * @method StringRule url()
 * @method StringRule ip()
 */
class StringRule extends Rule
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
        'length',
        'size',
        'regex',
        'email',
        'url',
        'ip',
    ];

    /**
     * Whether to check the strict data type.
     *
     * @var bool
     *
     * @since 1.0.0
     */
    protected bool $strict = true;

    /**
     * The messages for the validation errors.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'min' => 'The field `{name}` must be at least {min} characters long.',
        'max' => 'The field `{name}` must be at most {max} characters long.',
        'length' => 'The field `{name}` must be {length} characters long.',
        'size' => 'The field `{name}` must be {size} characters long.',
        'regex' => 'The field `{name}` must match the regex {regex}.',
        'email' => 'The field `{name}` must be a valid email address.',
        'url' => 'The field `{name}` must be a valid url.',
        'ip' => 'The field `{name}` must be a valid ip address.',
        'default' => 'The field `{name}` must be a string.',
    ];

    /**
     * @inheritDoc
     */
    public function validate(): bool
    {
        if (!is_string($this->value)) {
            return $this->fails($this->default_messages['default']);
        }

        return $this->with_constraints(function ($passed, $constraint) {
            if (!$passed) {
                $this->fails($this->default_messages[$constraint], [$constraint => $this->get($constraint)]);
            }
        });
    }

    /**
     * Compile the min constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_min($value)
    {
        return mb_strlen($value) >= (int) $this->get('min');
    }

    /**
     * Compile the max constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_max($value)
    {
        return mb_strlen($value) <= (int) $this->get('max');
    }

    /**
     * Compile the length constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_length($value)
    {
        return mb_strlen($value) === (int) $this->get('length');
    }

    /**
     * Compile the size constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_size($value)
    {
        return mb_strlen($value) === (int) $this->get('size');
    }

    /**
     * Compile the regex constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_regex($value)
    {
        return preg_match($this->get('regex'), $value) !== false; // @todo: handle this in a proper way
    }

    /**
     * Compile the email constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Compile the url constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Compile the ip constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function compile_ip($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @inheritDoc
     */
    public function messages()
    {
        return $this->process_messages($this->messages);
    }
}
