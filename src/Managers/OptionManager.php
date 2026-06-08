<?php

namespace Framework\Managers;

use Framework\Supports\Arr;
use Framework\Wordpress\Models\Option;

use function Framework\collection;
use function Framework\with_prefix;
use function Framework\without_prefix;

class OptionManager
{
    /**
     * Set the value of an option.
     *
     * Stores the given value in the WordPress options table using a namespaced option name.
     *
     * @param string $name The option key to set.
     * @param mixed $value The value to store for the option.
     * @param bool|null $autoload Whether to autoload the option.
     * 
     * @return bool True if the value was updated, false otherwise.
     */
    public function set(string $name, $value, $autoload = null)
    {
        return \update_option($this->get_option_name($name), $value, $autoload);
    }

    /**
     * Retrieve the value of an option.
     *
     * Gets the value from the WordPress options table using a namespaced option name.
     * Returns the default value if the option does not exist.
     *
     * @param string|array $name The option key to retrieve.
     * @param mixed|null $default The default value to return if the option does not exist.
     * @return mixed The value of the option or the default value.
     */
    public function get($name, $default = null)
    {
        $names = $this->get_option_name($name);

        $options = Option::query()->where_in('option_name', $names)->get();

        if ($options->is_empty()) {
            return $this->refill_missing_keys_with_defaults([], $names, $default);
        }

        if ($options->count() === 1) {
            return $options->first()->option_value ?? $default;
        }

        $results =  $options->pluck('option_value', 'option_name')->map(function($value, $key) use ($default) {
            if (is_array($default)) {
                return !is_null($value) ? $value : ($default[$key] ?? null);
            }

            return is_null($value) ? $default : $value;
        })->all();

        $results = $this->refill_missing_keys_with_defaults($results, $names, $default);

        return $this->rebase_keys($results);
    }

    /**
     * Refill the missing keys with the defaults.
     *
     * @param array $results The results to refill.
     * @param array $names The names to refill.
     * @param mixed $default The default value to refill.
     *
     * @return array The refilled results.
     */
    protected function refill_missing_keys_with_defaults(array $results, array $names, $default)
    {
        foreach ($names as $key) {
            if (!isset($results[$key])) {
                $key_without_prefix = without_prefix($key);
                $results[$key] = is_array($default) ? ($default[$key_without_prefix] ?? null) : $default;
            }
        }

        return $results;
    }

    /**
     * Rebase the keys of the results.
     *
     * @param array $results The results to rebase.
     * @return array The rebased results.
     */
    protected function rebase_keys(array $results)
    {
        return collection($results)->map(function($value, $key) {
            $key_without_prefix = without_prefix($key);
            return [$key_without_prefix => $value];
        })->collapse()->all();
    }

    /**
     * Delete an option.
     *
     * Removes the option from the WordPress options table using a namespaced option name.
     *
     * @param string $name The option key to delete.
     * @return bool True if the option was deleted, false otherwise.
     */
    public function delete(string $name)
    {
        return \delete_option($this->get_option_name($name));
    }

    /**
     * Generate the full option name with namespace prefix.
     *
     * Prepends the app prefix to the given option key.
     *
     * @param string|array $name The base option key.
     * @return array The namespaced option key.
     */
    protected function get_option_name($name)
    {
        $name = Arr::wrap($name);

        return collection($name)->map(fn($name) => with_prefix($name))->all();
    }
}
