<?php
/**
 * WordPress option keys used across the framework.
 *
 * @package    Framework
 * @subpackage Constants
 * @since      1.0.0
 */
namespace Framework\Constants;

defined('ABSPATH') || exit;

class OptionKeys
{
    /**
     * Option key for storing applied migration class names.
     *
     * @var string
     *
     * @since 1.0.0
     */
    public const MIGRATIONS = 'framework_migrations';
}
