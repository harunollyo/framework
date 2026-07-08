<?php
/**
 * User exists rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;

defined('ABSPATH') || exit;

/**
 * Validates that a WordPress user exists for the given value.
 *
 * The lookup field defaults to the user id and can be set
 * to email, login, or slug via the rule argument.
 */
class UserExistsRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'user_exists';

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The selected {name} is not a valid user.',
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
        $user = \get_user_by($this->lookup_field(), $this->value);

        if ($user === false) {
            return $this->fails($this->default_messages['default']);
        }

        return true;
    }

    /**
     * Get the user lookup field from the rule argument.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function lookup_field()
    {
        $field = is_string($this->args) && $this->args !== '' ? $this->args : 'id';

        return in_array($field, ['id', 'email', 'login', 'slug'], true) ? $field : 'id';
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
