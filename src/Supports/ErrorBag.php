<?php
/**
 * Read-only view over a plain validation error array.
 * Built at read time from flashed session data, so no instance is ever stored.
 * Keeps templates free of manual array indexing when rendering errors.
 *
 * @package    Framework
 * @subpackage Supports
 * @since      1.0.0
 */
namespace Framework\Supports;

defined('ABSPATH') || exit;

use Countable;

class ErrorBag implements Countable
{
    /**
     * The errors, keyed by field name.
     *
     * @var array<string,array<int,string>>
     *
     * @since 1.0.0
     */
    protected array $errors = [];

    /**
     * Create a new error bag instance.
     *
     * @param array $errors The errors keyed by field name.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct(array $errors = [])
    {
        foreach ($errors as $field => $messages) {
            $this->errors[(string) $field] = array_values(array_map('strval', (array) $messages));
        }
    }

    /**
     * Determine whether the bag holds any error at all.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function any()
    {
        return !empty($this->errors);
    }

    /**
     * Determine whether a field has an error.
     *
     * @param string $field The field name.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function has(string $field)
    {
        return !empty($this->errors[$field]);
    }

    /**
     * Get the first message for a field.
     *
     * @param string|null $field The field name, or null for the first message in the bag.
     * @param mixed $default The default when there is no message.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function first(?string $field = null, $default = null)
    {
        if (is_null($field)) {
            foreach ($this->errors as $messages) {
                if (!empty($messages)) {
                    return $messages[0];
                }
            }

            return $default;
        }

        return $this->errors[$field][0] ?? $default;
    }

    /**
     * Get every message for a field.
     *
     * @param string $field The field name.
     *
     * @return array<int,string>
     *
     * @since 1.0.0
     */
    public function get(string $field)
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get every message in the bag.
     *
     * @param bool $flatten Whether to return a flat list rather than a keyed array.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function all(bool $flatten = false)
    {
        if (!$flatten) {
            return $this->errors;
        }

        $messages = [];

        foreach ($this->errors as $field_messages) {
            foreach ($field_messages as $message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Get the names of the fields that have errors.
     *
     * @return array<int,string>
     *
     * @since 1.0.0
     */
    public function keys()
    {
        return array_keys($this->errors);
    }

    /**
     * Count the total number of messages in the bag.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function count(): int
    {
        return count($this->all(true));
    }
}
