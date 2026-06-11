<?php

namespace Framework\Polyfill;

if (!function_exists('Framework\Polyfill\array_first')) {
    /**
     * Get the first element of an array.
     *
     * @param array $array The array to get the first element from
     *
     * @return mixed|null The first element of the array or the default value
     * 
     * @since 1.0.0
     */
    function array_first(array $array) {
        if (function_exists('\array_first')) {
            return \array_first($array);
        }

        if (empty($array)) {
            return null;
        }

        foreach ($array as $value) {
            return $value;
        }
    }
}

if (!function_exists('Framework\Polyfill\is_iterable')) {
    /**
     * Determine if the given value is iterable.
     *
     * @param mixed $value The value to check
     *
     * @return bool True if the value is an array or Traversable, false otherwise
     *
     * @since 1.0.0
     */
    function is_iterable($value) {
        if (function_exists('\is_iterable')) {
            return \is_iterable($value);
        }

        return is_array($value) || $value instanceof \Traversable;
    }
}

if (!function_exists('Framework\Polyfill\array_key_first')) {
    /**
     * Get the first key of an array.
     *
     * @param array $array The array to get the first key from
     *
     * @return int|string|null The first key of the array or null if the array is empty
     *
     * @since 1.0.0
     */
    function array_key_first(array $array) {
        if (function_exists('\array_key_first')) {
            return \array_key_first($array);
        }

        foreach ($array as $key => $value) {
            return $key;
        }

        return null;
    }
}

if (!function_exists('Framework\Polyfill\array_key_last')) {
    /**
     * Get the last key of an array.
     *
     * @param array $array The array to get the last key from
     *
     * @return int|string|null The last key of the array or null if the array is empty
     *
     * @since 1.0.0
     */
    function array_key_last(array $array) {
        if (function_exists('\array_key_last')) {
            return \array_key_last($array);
        }

        if (empty($array)) {
            return null;
        }

        return key(array_slice($array, -1, 1, true));
    }
}

if (!function_exists('Framework\Polyfill\str_contains')) {
    /**
     * Determine if a string contains a given substring.
     *
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     *
     * @return bool True if the haystack contains the needle, false otherwise
     *
     * @since 1.0.0
     */
    function str_contains($haystack, $needle) {
        if (function_exists('\str_contains')) {
            return \str_contains($haystack, $needle);
        }

        return (string) $needle === '' || strpos((string) $haystack, (string) $needle) !== false;
    }
}

if (!function_exists('Framework\Polyfill\str_starts_with')) {
    /**
     * Determine if a string starts with a given substring.
     *
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     *
     * @return bool True if the haystack starts with the needle, false otherwise
     *
     * @since 1.0.0
     */
    function str_starts_with($haystack, $needle) {
        if (function_exists('\str_starts_with')) {
            return \str_starts_with($haystack, $needle);
        }

        return strncmp((string) $haystack, (string) $needle, strlen((string) $needle)) === 0;
    }
}

if (!function_exists('Framework\Polyfill\str_ends_with')) {
    /**
     * Determine if a string ends with a given substring.
     *
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     *
     * @return bool True if the haystack ends with the needle, false otherwise
     *
     * @since 1.0.0
     */
    function str_ends_with($haystack, $needle) {
        if (function_exists('\str_ends_with')) {
            return \str_ends_with($haystack, $needle);
        }

        $haystack = (string) $haystack;
        $needle = (string) $needle;

        if ($needle === '' || $haystack === $needle) {
            return true;
        }

        if ($haystack === '') {
            return false;
        }

        $needle_length = strlen($needle);

        return $needle_length <= strlen($haystack) && substr_compare($haystack, $needle, -$needle_length) === 0;
    }
}

if (!class_exists('ArgumentCountError', false) && !class_exists('Framework\Polyfill\ArgumentCountError', false)) {
    /**
     * Polyfill for the ArgumentCountError class introduced in PHP 7.1.
     *
     * @since 1.0.0
     */
    class ArgumentCountError extends \Error
    {
    }
}

if (!class_exists('Framework\Polyfill\ArgumentCountError', false) && class_exists('ArgumentCountError', false)) {
    class_alias('ArgumentCountError', 'Framework\Polyfill\ArgumentCountError');
}
