<?php

namespace Framework\Supports\Facades;

use Framework\Facade;
use Kirki\App\Decisions\Contexts\DecisionContext;

/**
 * DecisionEngine facade
 *
 * @method DecisionContext get_context()
 * @method void apply_rules(DecisionContext $context, array $rules)
 * @method bool passes(array $condition_data)
 * @method void apply_action(array $action_data)
 *
 * @see \Kirki\App\Decisions\DecisionEngine
 */
class DecisionEngine extends Facade
{
    public static function get_accessor()
    {
        return 'decision_engine';
    }
}
