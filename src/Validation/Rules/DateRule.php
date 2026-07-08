<?php
/**
 * Date rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Supports\Facades\Date;
use Framework\Validation\ValidationRule;

use function Framework\deep_get;

defined('ABSPATH') || exit;

/**
 * Validates that the given value is a valid date.
 *
 * @method $this after(string $reference)
 * @method $this format(string $format)
 */
class DateRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'date';

    /**
     * The supported constraints.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $constraints = [
        'after',
        'format',
        'datetime',
    ];

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The {name} field must be a valid date.',
        'after' => 'The {name} field must be a date after {after}.',
        'format' => 'The {name} field must match the format {format}.',
        'datetime' => 'The {name} field must be a valid datetime.',
    ];

    /**
     * The date format.
     *
     * @var string
     *
     * @since 1.0.0
     */ 
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * The datetime format.
     *
     * @var string
     *
     * @since 1.0.0
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Validate the rule.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validate(): bool
    {
        if (!Date::is_valid_date($this->value)) {
            return $this->fails($this->default_messages['default']);
        }

        return $this->validate_constraints(function ($passed, $constraint) {
            if (!$passed) {
                $this->fails($this->default_messages[$constraint], [$constraint => (string) $this->get($constraint)]);
            }
        });
    }

    /**
     * Validate the after constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_after($value)
    {
        $reference = $this->resolve_reference_date($this->get('after'));

        if (!Date::is_valid_date($reference)) {
            return false;
        }

        return Date::is_after(Date::parse($value), Date::parse($reference));
    }

    /**
     * Validate the format constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_format($value)
    {
        $format = (string) $this->get('format');

        if (!is_string($value)) {
            return false;
        }

        $date = Date::create_from_format($format, $value);

        return $date !== null && $date->format($format) === $value;
    }

    /**
     * Validate the datetime constraint.
     *
     * @param string $value The value to validate.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function validate_datetime($value)
    {
        $format = $this->get('datetime');

        if ($format === true) {
            $format = static::DATETIME_FORMAT;
        }

        $date = Date::create_from_format($format, $value);

        return $date !== null && $date->format($format) === $value;
    }

    /**
     * Resolve the after argument as another data field first, falling back to a literal date.
     *
     * @param string $reference The reference field name or literal date.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    protected function resolve_reference_date($reference)
    {
        $field_value = deep_get($this->data, (string) $reference);

        if ($field_value !== null) {
            return $field_value;
        }

        return $reference;
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
