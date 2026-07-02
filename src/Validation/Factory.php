<?php
/**
 * The validation factory.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */

namespace Framework\Validation;

defined('ABSPATH') || exit;

use function Framework\Polyfill\array_first;
use function Framework\Polyfill\array_last;
use function Framework\Polyfill\str_contains;

class Factory
{
    /**
     * The base rules.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $base_rules = [
        'string',
        'required',
        'required_if',
        'contains',
        'does_not_contain',
        'date',
        'email',
        'exists',
        'in',
        'not_in',
        'numeric',
        'integer',
        'prohibited',
        'unique',
        'file',
        'image',
        'array',
    ];

    /**
     * The modifier rules.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $modifier_rules = [
        'min',
        'max',
        'between',
        'after',
        'before',
        'size',
        'length',
        'ip',
        'url',
        'image',
        'mimes',
        'mimetypes',
        'dimensions',
    ];

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
     * @inheritdoc
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
        $length = count($this->rules);
        $index = 0;
        $rules = [];

        while ($index < $length) {
            $rule = $this->rules[$index++];

            if ($rule instanceof Rule) {
                $rules[] = $rule;
            } elseif (is_string($rule) && $this->is_base_rule($rule)) {
                $modifiers = $this->chain[$rule] ?? [];
                $index += count($modifiers);
                $rules[] = $this->build_rule($rule, $modifiers);
            }
        }

        return $rules;
    }

    /**
     * Build the rule.
     *
     * @param string $rule The rule to build.
     * @param array $from_chain The from chain.
     *
     * @return Rule
     *
     * @since 1.0.0
     */
    protected function build_rule(string $rule, array $from_chain)
    {
        $rule_name = $this->rule_name($rule);
        $rule_arguments = $this->rule_arguments($rule);

        $instance = $rule_arguments ? Rule::$rule_name($rule_arguments) : Rule::$rule_name();

        foreach ($from_chain as $chain_rule) {
            $name = $this->rule_name($chain_rule);
            $arguments = $this->rule_arguments($chain_rule);

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
            if (!is_string($rule)) {
                continue;
            }

            if ($this->is_base_rule($rule)) {
                $this->chain[$rule] ??= [];
                $last_base_rule = $rule;
                continue;
            }

            if ($this->is_modifier_rule($rule)) {
                $this->distribute($rule, $last_base_rule);
            }
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
        $this->chain[$last_base_rule][] = $rule;
    }

    /**
     * Check if the rule is a base rule.
     *
     * @param string $rule The rule to check.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function is_base_rule($rule)
    {
        return in_array($this->rule_name($rule), $this->base_rules, true);
    }

    /**
     * Check if the rule is a modifier rule.
     *
     * @param string $rule The rule to check.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function is_modifier_rule($rule)
    {
        return in_array($this->rule_name($rule), $this->modifier_rules, true);
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
