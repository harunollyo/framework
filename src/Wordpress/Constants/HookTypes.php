<?php
/**
 * Defines the two WordPress hook kinds: action and filter.
 * Used by BaseHook subclasses to declare their registration type.
 * Minimal enum-like constant holder for the hook system.
 *
 * @package    Framework
 * @subpackage Wordpress\Constants
 * @since      1.0.0
 */
namespace Framework\Wordpress\Constants;

defined('ABSPATH') || exit;

class HookTypes
{
    /**
     * Action hook type.
     *
     * @var string
     */
    const ACTION = 'action';

    /**
     * Filter hook type.
     *
     * @var string
     */
    const FILTER = 'filter';
}
