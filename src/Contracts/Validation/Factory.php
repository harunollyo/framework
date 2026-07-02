<?php
/**
 * The validation factory contract.
 *
 * @package    Framework
 * @subpackage Validation
 *
 * @since 1.0.0
 */

namespace Framework\Contracts\Validation;

use Framework\Validation\Rule;

defined('ABSPATH') || exit;

interface Factory
{
    /**
     * Make a rule.
     *
     * @param string|Rule|callable $rule The rule to make.
     * @param array $rules The rules to make the rule from.
     *
     * @return Rule
     * 
     * @since 1.0.0
     */
    public function make($rule, array $rules);
}
