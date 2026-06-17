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
     * @since 1.0.0
     */
    const MIGRATIONS = 'migrations';

    /**
     * Option key for email and SMTP settings.
     *
     * @var string
     * @since 1.0.0
     */
    const EMAIL_SETTINGS = 'email_settings';
}
