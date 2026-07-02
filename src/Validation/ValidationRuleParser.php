<?php
/**
* Validation rule parser class.
*
* @package    Framework
* @subpackage Validation
* @since      1.0.0
*/

namespace Framework\Validation;

use Framework\Collections\Collection;
use Framework\Supports\Arr;

use function Framework\Polyfill\str_contains;

defined('ABSPATH') || exit;

class ValidationRuleParser
{
    /**
     * The data to parse.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $data;

    /**
     * The rules to parse.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $rules;

    /**
     * Create a new validation rule parser instance.
     *
     * @param array $data The data to parse.
     *
     * @return void
     * 
     * @since 1.0.0
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Get the rules.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function parse()
    {
        return $this->explode_wildcards()
            ->proces_rules();
    }

    /**
     * Get the parsed and exploded rules.
     *
     * @return array
     * 
     * @since 1.0.0
     */
    public function all()
    {
        return $this->rules();
    }

    /**
     * Explode the rules into a new array.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function explode_wildcards()
    {
        $exploded = [];

        foreach ($this->rules as $key => $rule) {
            if (str_contains($key, '*')) {
                $array_rules = $this->explode_array_rule($key, $rule);
                $exploded = array_merge($exploded, $array_rules);
            } else {
                $exploded[$key] = $rule;
            }
        }

        $this->rules = $exploded;

        return $this;
    }

    /**
     * Parse the rules.
     *
     * @return $this
     * 
     * @since 1.0.0
     */
    public function proces_rules()
    {
        $parsed = [];

        foreach ($this->rules as $key => $rules) {
            $parsed[$key] = Factory::make($this->wrap($rules));
        }

        $this->rules = $parsed;

        return $this;
    }

    /**
     * Prepare the rules.
     *
     * @param array $rules The rules to prepare.
     *
     * @return array
     * 
     * @since 1.0.0
     */
    protected function wrap(array $rules)
    {
        if (is_array($rules)) {
            return $rules;
        }

        if (is_string($rules)) {
            return explode('|', $rules);
        }

        return Arr::wrap($rules);
    }

    /**
     * Get the rules.
     *
     * @return array
     * 
     * @since 1.0.0
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Expand a wildcard rule key into concrete dot-notated keys using the parser data.
     *
     * @param string $key The wildcard rule key.
     * @param array<Rule> $rule The rule to apply to each expanded key.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function explode_array_rule(string $key, $rule)
    {
        $segments = explode('.', $key);

        return $this->explode_array_rule_segments($this->data, $segments, [], $rule);
    }

    /**
     * Recursively expand wildcard segments against a data branch.
     *
     * @param mixed $current_data The current data branch being traversed.
     * @param array $segments The remaining key segments to resolve.
     * @param array $path The resolved path segments collected so far.
     * @param array<Rule> $rule The rule to apply when the path is fully resolved.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function explode_array_rule_segments($current_data, array $segments, array $path, $rule)
    {
        if ($segments === []) {
            return [implode('.', $path) => $rule];
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            if (!is_array($current_data) || $current_data === []) {
                return [];
            }

            $exploded = [];

            foreach ($current_data as $index => $item) {
                $exploded = array_merge(
                    $exploded,
                    $this->explode_array_rule_segments(
                        $item,
                        $segments,
                        array_merge($path, [$index]),
                        $rule
                    )
                );
            }

            return $exploded;
        }

        if (!is_array($current_data) || !array_key_exists($segment, $current_data)) {
            return [];
        }

        return $this->explode_array_rule_segments(
            $current_data[$segment],
            $segments,
            array_merge($path, [$segment]),
            $rule
        );
    }
}
