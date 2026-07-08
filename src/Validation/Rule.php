<?php
/**
 * Rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation;

use Framework\Contracts\Support\Arrayable;
use Framework\Supports\Arr;
use Framework\Supports\Fluent;
use Framework\Validation\Rules\ArrayRule;
use Framework\Validation\Rules\InRule;
use Framework\Validation\Rules\NullableRule;
use Framework\Validation\Rules\NumericRule;
use Framework\Validation\Rules\RequiredRule;
use Framework\Validation\Rules\StringRule;
use Override;

defined('ABSPATH') || exit;

class Rule
{
    /**
     * Create a new string rule.
     *
     * @return StringRule
     *
     * @since 1.0.0
     */
    public static function string()
    {
        return new StringRule();
    }

    /**
     * Create a new email rule.
     *
     * @return StringRule
     *
     * @since 1.0.0
     */
    public static function email()
    {
        return (new StringRule())->email();
    }

    /**
     * Create a new url rule.
     *
     * @return StringRule
     *
     * @since 1.0.0
     */
    public static function url()
    {
        return (new StringRule())->url();
    }

    /**
     * Create a new required rule.
     *
     * @return RequiredRule
     *
     * @since 1.0.0
     */
    public static function required()
    {
        return new RequiredRule();
    }

    /**
     * Create a new array rule.
     *
     * @return ArrayRule
     *
     * @since 1.0.0
     */
    public static function array()
    {
        return new ArrayRule();
    }

    /**
     * Create a new numeric rule.
     *
     * @return NumericRule
     *
     * @since 1.0.0
     */
    public static function numeric()
    {
        return new NumericRule();
    }

    /**
     * Create a new integer rule.
     *
     * @return NumericRule
     *
     * @since 1.0.0
     */
    public static function integer()
    {
        return (new NumericRule())->integer();
    }

    /**
     * Create a new nullable rule.
     *
     * @return NullableRule
     *
     * @since 1.0.0
     */
    public static function nullable()
    {
        return new NullableRule();
    }

    /**
     * Create a new in rule.
     *
     * @param array|Arrayable $values The values to check.
     *
     * @return InRule
     *
     * @since 1.0.0
     */
    public static function in($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->to_array();
        }

        return new InRule(is_array($values) ? $values : func_get_args());
    }
}
