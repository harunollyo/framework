<?php

namespace Framework\Supports;

use ArrayAccess;
use Closure;
use Framework\Contracts\Support\Arrayable;
use Framework\Contracts\Support\Jsonable;
use Framework\Collections\Collection;
use InvalidArgumentException;

use function Framework\deep_get;
use function Framework\value;

/**
 * The array helper class.
 *
 * @since 1.0.0
 */
class Arr
{
    /**
     * Create an array from the given items.
     *
     * @param mixed $items The items to create an array from
     *
     * @return array
     *
     * @throws InvalidArgumentException If the items cannot be represented by a scalar value
     * @since 1.0.0
     */
    public static function from($items)
    {
        switch (true) {
            case is_array($items):
                return $items;
            case $items instanceof Arrayable:
                return $items->to_array();
            case $items instanceof Jsonable:
                return json_decode($items->to_json(), true);
            case $items instanceof Collection:
                return $items->all();
            case is_object($items):
                return (array) $items;
            default:
                throw new InvalidArgumentException('Items cannot be represented by a scalar value.');
        }
    }

    /**
     * Flatten the array for the sequential array
     *
     * @param array $array The array to flatten
     * @param int|float $depth The maximum depth to flatten
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Filter the array by a callable function.
     * The function will return a true/false value and the return value is true then the value will be kept,
     * otherwise removed.
     *
     * @param array $array The array to filter
     * @param callable $callback The callable function for filtering
     *
     * @return array The filtered array
     *
     * @since 1.0.0
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Wrap the value in an array
     *
     * @param mixed $value The value to wrap
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Determine whether an array is associative.
     *
     * @param array $array The array to inspect
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function is_associative(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Get a value from an array using a dot notation key.
     *
     * @param array $array The array to get the value from
     * @param string $key The key to get the value from
     * @param mixed $default The default value if the key does not exist
     *
     * @return mixed
     * 
     * @since 1.0.0
     */
    public static function get($array, $key, $default = null)
    {
        if (!static::accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * Pluck the array by a key
     *
     * @param iterable $array The array to pluck from
     * @param string|array|int|Closure|null $value The value to pluck
     * @param string|array|Closure|null $key The key to pluck by
     *
     * @return array The plucked array
     *
     * @since 1.0.0
     */
    public static function pluck($array, $value, $key = null)
    {
        $results = [];

        foreach ($array as $item) {
            $item_value = $value instanceof Closure
                ? $value($item)
                : deep_get($item, $value);

            if (is_null($key)) {
                $results[] = $item_value;
            } else {
                $item_key = $key instanceof Closure
                    ? $key($item)
                    : deep_get($item, $key);

                if (is_object($item_key) && method_exists($item_key, '__toString')) {
                    $item_key = (string) $item_key;
                }

                $results[$item_key] = $item_value;
            }
        }

        return $results;
    }

    /**
     * Return a new instance containing only the specified keys
     *
     * @param array $items The array to filter
     * @param array $keys The keys to keep
     *
     * @return array A new array containing only the specified keys
     *
     * @since 1.0.0
     */
    public static function only(array $items, array $keys)
    {
        return array_intersect_key($items, array_flip($keys));
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param array $values The array of arrays to collapse
     *
     * @return array The collapsed array
     *
     * @since 1.0.0
     */
    public static function collapse($values)
    {
        $results = [];

        foreach ($values as $item) {
            if ($item instanceof Collection) {
                $results[] = $item->all();
            } elseif (is_array($item)) {
                $results[] = $item;
            }
        }

        return array_merge([], ...$results);
    }

    /**
     * Check if the value is accessible.
     *
     * @param mixed $value The value to check
     *
     * @return boolean True if the value is accessible, false otherwise
     *
     * @since 1.0.0
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof Arrayable;
    }

    /**
     * Check if the array has a key.
     *
     * @param array $array The array to check
     * @param mixed $key The key to check for
     *
     * @return boolean True if the array has the key, false otherwise
     *
     * @since 1.0.0
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if (is_float($key) || is_null($key)) {
            $key = (string) $key;
        }

        return array_key_exists($key, $array);
    }

    /**
     * Convert the array to a JSON string
     *
     * @param array $array The array to convert
     * @param int $flags The flags to use for JSON encoding
     * @param int $depth The maximum depth to encode
     *
     * @return string The JSON string
     *
     * @since 1.0.0
     */
    public static function json_encode($array, $flags = 0, $depth = 512)
    {
        return wp_json_encode($array, $flags, $depth);
    }
}
