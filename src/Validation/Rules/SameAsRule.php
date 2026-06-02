<?php

namespace Framework\Validation\Rules;

/**
 * Validates that a value is same as another specified field.
 *
 * @since 1.0.0
 */
class SameAsRule extends BaseRule
{
    /**
     * Determine if the value is matched.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $required_value = $this->data[$this->rule_value] ?? null;

        return !is_null($this->value) && $this->value === $required_value;
    }

    /**
     * Get the error message for a missing field.
     *
     * @return string
     */
    public function get_error_message()
    {
        return sprintf('The %s field must be same as %s.', $this->last_key_segment(), $this->rule_value);
    }
}
