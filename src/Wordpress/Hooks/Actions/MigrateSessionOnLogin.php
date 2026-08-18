<?php
/**
 * Gives the session a new identifier on login while preserving its data.
 * Does nothing when the visitor has no session, so sessionless requests stay untouched.
 * Registered by default alongside the framework's other lifecycle hooks.
 *
 * @package    Framework
 * @subpackage Wordpress\Hooks\Actions
 * @since      1.0.0
 */
namespace Framework\Wordpress\Hooks\Actions;

defined('ABSPATH') || exit;

use Framework\Managers\SessionManager;
use Framework\Wordpress\BaseHook;
use Framework\Wordpress\Constants\HookNames;
use Framework\Wordpress\Constants\HookTypes;

use function Framework\app;

class MigrateSessionOnLogin extends BaseHook
{
    /**
     * Get the name.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_name()
    {
        return HookNames::WP_LOGIN;
    }

    /**
     * Get the type.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_type()
    {
        return HookTypes::ACTION;
    }

    /**
     * Handle.
     *
     * @param mixed $args The positional arguments.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function handle(...$args)
    {
        $session = app(SessionManager::class);

        if (!$session->has_session()) {
            return;
        }

        $session->migrate(false);
    }
}
