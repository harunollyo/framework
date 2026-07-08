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

use function Framework\Polyfill\str_ends_with;
use function Framework\Polyfill\str_starts_with;

defined('ABSPATH') || exit;

/**
 * Validates that the given value is a string.
 *
 * @method StringRule min(int $min)
 * @method StringRule max(int $max)
 * @method StringRule length(int $length)
 * @method StringRule size(int $size)
 * @method StringRule email()
 * @method StringRule url()
 * @method StringRule ip()
 * @method StringRule alpha()
 * @method StringRule alpha_num()
 * @method StringRule alpha_dash()
 * @method StringRule between(int $min, int $max)
 * @method StringRule starts_with(string $value)
 * @method StringRule ends_with(string $value)
 * @method StringRule doesnt_start_with(string $value)
 * @method StringRule doesnt_end_with(string $value)
 * @method StringRule exactly(int $value)
 */
class StringRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'string';

    /**
     * The supported constraints.
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
        'email',
        'url',
        'ip',
        'alpha',
        'alpha_num',
        'alpha_dash',
        'between',
        'starts_with',
        'ends_with',
        'doesnt_start_with',
        'doesnt_end_with',
        'exactly',
    ];

    /**
     * The messages for the validation errors.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'min' => 'The {name} field must be at least {min} characters long.',
        'max' => 'The {name} field must be at most {max} characters long.',
        'length' => 'The {name} field must be {length} characters long.',
        'size' => 'The {name} field must be {size} characters long.',
        'email' => 'The {name} field must be a valid email address.',
        'url' => 'The {name} field must be a valid url.',
        'ip' => 'The {name} field must be a valid ip address.',
        'alpha' => 'The {name} field must only contain alphabetic characters.',
        'alpha_num' => 'The {name} field must only contain alphabetic characters and numbers.',
        'alpha_dash' => 'The {name} field must only contain alphabetic characters, dashes and underscores.',
        'between' => 'The {name} field must be between {min} and {max} characters long.',
        'starts_with' => 'The {name} field must start with {starts_with}.',
        'ends_with' => 'The {name} field must end with {ends_with}.',
        'doesnt_start_with' => 'The {name} field must not start with {doesnt_start_with}.',
        'doesnt_end_with' => 'The {name} field must not end with {doesnt_end_with}.',
        'exactly' => 'The {name} field must be exactly {exactly} characters long.',
        'default' => 'The {name} field must be a string.',
    ];

    /**
     * @inheritDoc
     */
    public function validate(): bool
    {
        if (!is_string($this->value)) {
            return $this->fails($this->default_messages['default']);
        }

        return $this->validate_constraints(function ($passed, $constraint) {
            if (!$passed) {
                $this->fails($this->default_messages[$constraint], $this->constraint_placeholders($constraint));
            }
        });
    }

    /**
     * Build the message placeholders for a failed constraint.
     *
     * @param string $constraint The failed constraint name.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function constraint_placeholders(string $constraint)
    {
        if ($constraint === 'between') {
            [$min, $max] = $this->get('between');

            return ['min' => (string) $min, 'max' => (string) $max];
        }

        return [$constraint => (string) $this->get($constraint)];
    }

    /**
     * Validate the min constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_min($value)
    {
        return mb_strlen($value) >= (int) $this->get('min');
    }

    /**
     * Validate the max constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_max($value)
    {
        return mb_strlen($value) <= (int) $this->get('max');
    }

    /**
     * Validate the length constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_length($value)
    {
        return $this->has_exact_length($value, 'length');
    }

    /**
     * Validate the size constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_size($value)
    {
        return $this->has_exact_length($value, 'size');
    }

    /**
     * Validate the exactly constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_exactly($value)
    {
        return $this->has_exact_length($value, 'exactly');
    }

    /**
     * Check whether the value length matches the given constraint value.
     *
     * @param string $value The value to check.
     * @param string $constraint The constraint holding the expected length.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function has_exact_length($value, string $constraint)
    {
        return mb_strlen($value) === (int) $this->get($constraint);
    }

    /**
     * Validate the starts_with constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_starts_with($value)
    {
        return str_starts_with($value, $this->get('starts_with'));
    }

    /**
     * Validate the ends_with constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function validate_ends_with($value)
    {
        return str_ends_with($value, $this->get('ends_with'));
    }

    /**
     * Validate the doesnt_start_with constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function validate_doesnt_start_with($value)
    {
        return !str_starts_with($value, $this->get('doesnt_start_with'));
    }

    /**
     * Validate the doesnt_end_with constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function validate_doesnt_end_with($value)
    {
        return !str_ends_with($value, $this->get('doesnt_end_with'));
    }

    /**
     * Validate the between constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    protected function validate_between($value)
    {
        [$min, $max] = $this->get('between');

        return mb_strlen($value) >= (int) $min && mb_strlen($value) <= (int) $max;
    }

    /**
     * Validate the alpha constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_alpha($value)
    {
        return preg_match('/^[a-zA-Z]+$/', $value) === 1;
    }

    /**
     * Validate the alpha_num constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_alpha_num($value)
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }

    /**
     * Validate the alpha_dash constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_alpha_dash($value)
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
    }

    /**
     * Validate the email constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate the url constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate the ip constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_ip($value)
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
