<?php
/**
 * The validation factory.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */

namespace Framework\Validation;

use Framework\Supports\Arr;
use ReflectionMethod;

defined('ABSPATH') || exit;

use function Framework\Polyfill\array_first;
use function Framework\Polyfill\array_key_first;
use function Framework\Polyfill\array_last;
use function Framework\Polyfill\str_contains;

class Factory
{
    /**
     * The rules.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $rules = [];

    /**
     * The chain of rules.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $chain = [];

    /**
     * The cache of rule classes.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected static array $rule_class_cache = [];

    /**
     * Construct the factory.
     *
     * @param array $rules The rules to construct the factory with.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
        $this->build_rules_chain($rules);
    }

    /**
     * Make the rule array.
     *
     * @param array $rules The rules to make the rule array with.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function make(array $rules)
    {
        return (new static($rules))->make_rule_array();
    }

    /**
     * Make the rule array.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function make_rule_array()
    {
        $rules = [];

        foreach ($this->chain as $rule_name => $constraints) {
            $instance = $this->build_rule($rule_name, $constraints);
            $rules[] = $instance;
        }

        return $this->finalyze($rules);
    }

    /**
     * Finalyze the rules.
     * If the rules has implicit rules then remove the nullable rule.
     *
     * @param array $rules The rules to filter.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function finalyze($rules)
    {
        $has_impliclit = !empty(Arr::where($rules, fn ($rule) => $rule->is_implicit));

        $rules = Arr::reject($rules, fn ($rule) => $has_impliclit && $rule->get_rule_name() === 'nullable');

        usort($rules, fn ($first, $second) => $first->is_implicit || $first->get_rule_name() === 'nullable' ? -1 : 1);

        return $rules;
    }

    /**
     * Build the rule.
     *
     * @param string $rule The rule to build.
     * @param array $modifiers The from chain.
     *
     * @return Rule
     *
     * @since 1.0.0
     */
    protected function build_rule(string $rule, array $modifiers)
    {
        if (is_subclass_of($rule, ValidationRule::class)) {
            return array_first($modifiers);
        }

        $rule_name = $this->rule_name($rule);
        $rule_arguments = $this->rule_arguments($rule);

        $instance = $rule_arguments ? Rule::$rule_name($rule_arguments) : Rule::$rule_name();

        foreach ($modifiers as $modifier) {
            $name = $this->rule_name($modifier);
            $arguments = $this->rule_arguments($modifier);

            if (!$instance->has_constraint($name)) {
                continue;
            }

            if (!empty($arguments)) {
                $instance->$name($arguments);
            } else {
                $instance->$name();
            }
        }

        return $instance;
    }

    /**
     * Build the chain.
     *
     * @param array $rules The rules to build the chain with.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function build_rules_chain(array $rules)
    {
        $last_base_rule = null;

        foreach ($rules as $rule) {
            if ($rule instanceof ValidationRule) {
                $this->chain[get_class($rule)] = [$rule];
                continue;
            }

            $rule_name = $this->rule_name($rule);

            if (($rule_class = $this->get_rule_class($rule_name)) !== null) {
                $last_base_rule = $rule_class->get_rule_name();
            }

            $this->distribute($rule, $last_base_rule);
        }
    }

    /**
     * Distribute the rule.
     *
     * @param string $rule The rule to distribute.
     * @param string $last_base_rule The last base rule.
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function distribute($rule, $last_base_rule)
    {
        if ($last_base_rule === null) {
            return;
        }

        $rule_name = $this->rule_name($rule);
        $this->chain[$last_base_rule] ??= [];

        if ($last_base_rule !== null && Rule::$last_base_rule()->has_constraint($rule_name)) {
            $this->chain[$last_base_rule][] = $rule;
        }
    }

    /**
     * Check if the rule class exists.
     *
     * @param string $rule The rule to check.
     *
     * @return ValidationRule|null
     *
     * @since 1.0.0
     */
    protected function get_rule_class(string $rule)
    {
        if (isset(static::$rule_class_cache[$rule])) {
            return static::$rule_class_cache[$rule];
        }

        if (!method_exists(Rule::class, $rule)) {
            return null;
        }

        $method = new ReflectionMethod(Rule::class, $rule);

        return static::$rule_class_cache[$rule] = $method->invoke(null);
    }

    /**
     * Check if the rule class exists.
     *
     * @param string $rule The rule to check.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function rule_class_exists(string $rule)
    {
        $rule_class = $this->get_rule_class($rule);

        return $rule_class !== null && $rule_class->get_rule_name() === $rule;
    }

    /**
     * Get the name of the rule.
     *
     * @param string $rule The rule to get the name of.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function rule_name(string $rule)
    {
        return array_first(explode(':', $rule));
    }

    /**
     * Get the arguments of the rule.
     *
     * @param string $rule The rule to get the arguments of.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    protected function rule_arguments(string $rule)
    {
        if (!str_contains($rule, ':')) {
            return null;
        }

        $arguments = array_last(explode(':', $rule));

        if (empty($arguments)) {
            return null;
        }

        if (str_contains($arguments, ',')) {
            return explode(',', $arguments);
        }

        return $arguments;
    }
}
