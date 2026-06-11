<?php

namespace Framework\Concerns;

use function Framework\deep_get;

trait EnumeratesValues
{
    /**
     * Determine if the value is callable.
     *
     * @param mixed $value The value to check
     * @return bool True when callable; false otherwise
     * @since 1.0.0
     */
    protected function is_callable($value)
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get the value of the item.
     *
     * @param callable|string|null $value The value to get
     * @return callable The value of the item
     * @since 1.0.0
     */
    protected function value_retriever($value)
    {
        if ($this->is_callable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return deep_get($item, $value);
        };
    }
}